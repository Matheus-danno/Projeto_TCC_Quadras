# AlugaQuadra

Plataforma web para **alugar quadras esportivas e montar partidas**. Jogadores
encontram quadras, reservam horários, criam ou entram em salas para completar
um time e compram produtos na loja dos estabelecimentos. Donos de quadra
gerenciam agenda, reservas, finanças e loja num painel próprio, e um
administrador aprova cadastros e acompanha a comissão da plataforma.

Projeto de TCC. Os pagamentos são **simulados**: nenhuma cobrança real é feita
(ver [Decisões de arquitetura](#decisões-de-arquitetura)).

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.2+ (testado em 8.3, 8.4 e 8.5), Laravel 12 |
| Interface | Livewire 4 (componentes de página inteira), Blade |
| Autenticação | Laravel Fortify (login, recuperação de senha, 2FA) |
| CSS | Bootstrap 5 + `public/css/style.css` no site; Tailwind 4 + Flux nas telas de configurações |
| Build de assets | Vite (tudo local, sem CDN) |
| Banco de dados | PostgreSQL 16 |
| Testes | Pest 4, rodando contra PostgreSQL |
| Serviços externos | OpenStreetMap — Overpass API (quadras próximas) e Nominatim (coordenadas do endereço) |

## Como rodar

Pré-requisitos: PHP 8.2+ com `pdo_pgsql`, Composer, Node 22+ e um PostgreSQL 16
— nativo na WSL ou via Docker. O passo a passo do banco, com as duas opções,
está em [docs/banco-de-dados.md](docs/banco-de-dados.md).

```bash
# 1. Banco (escolha um)
docker compose up -d                 # PostgreSQL em container, já cria os dois bancos
# ou: bash scripts/configurar-banco.sh   (faz o banco, o .env e as migrations de uma vez)

# 2. Aplicação
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed           # tabelas + conta de administrador
php artisan db:seed --class=DemoSeeder        # dados de demonstração
php artisan db:seed --class=RealQuadrasSeeder # quadras reais de Bauru e região (opcional)

# 3. Front-end e servidor
npm ci
npm run build
php artisan serve                    # http://localhost:8000
```

O roteiro mínimo para avaliar o sistema está em
[docs/como-avaliar.md](docs/como-avaliar.md).

### Contas de demonstração

Todas com a senha `password`.

| Papel | E-mail |
|---|---|
| Jogador | `jogador@demo.com` |
| Dono de quadra | `dono@demo.com` |
| Dono de quadra (2) | `dono2@demo.com` |
| Administrador | `admin@demo.com` |

O `DemoSeeder` também cria donos em cada etapa da aprovação de cadastro
(`dono.pendente@demo.com`, `dono.analise@demo.com`, `dono.ajustes@demo.com`,
`dono.rejeitado@demo.com`, `dono.fluxo-completo@demo.com`).

## Funcionalidades por papel

**Visitante:** página inicial, busca de quadras (`/quadras`), quadras próximas
pelo mapa (`/quadras-proximas`), salas abertas (`/encontre-um-time`), loja
(`/loja`), cadastro de jogador (`/registro`) e de dono (`/cadastro-dono`).

**Jogador** (`/perfil`)
- Reservar horário em quadra, com pagamento simulado (Pix, cartão ou créditos).
- Criar sala para completar time, ou entrar em uma; com aprovação manual, o
  organizador aceita ou recusa os pedidos.
- Chat da sala e chat com o dono da quadra; avaliar jogadores e quadra após a partida.
- Comprar na loja, com carrinho, retirada no estabelecimento e nota de compra.
- Cancelar reserva dentro do prazo do dono (o valor vira crédito).
- Editar perfil, preferências de notificação, cartões salvos, 2FA e
  **Baixar meus dados** (LGPD).

**Dono de quadra** (`/painel`)
- Cadastro em análise até a aprovação do administrador (`/painel/cadastro`).
- Quadras com fotos, horário de funcionamento e exceções de data.
- Agenda e lista de reservas, confirmação e cancelamento, agendamento manual
  para clientes sem conta.
- Financeiro com a comissão da plataforma descontada; loja com produtos,
  estoque e pedidos; mensagens com jogadores.

**Administrador** (`/admin`)
- Fila de aprovação de donos e quadras, com checklist e histórico.
- Usuários (bloqueio), quadras, reservas, financeiro, repasses aos donos e
  percentual de comissão.

## Estrutura

```
app/
  Livewire/          componentes de tela, por contexto: Quadras, Salas, Loja,
                     Perfil, Painel (dono), Admin, Auth
  Models/            entidades Eloquent
  Enums/             valores de domínio (status, esportes, papéis...)
  Policies/          autorização por recurso (Quadra, Reserva, Sala, Pedido, Produto)
  Http/Middleware/   papel do usuário e bloqueio de conta
  Services/          Overpass e Nominatim (OpenStreetMap), aprovação de
                     cadastro, exportação de dados pessoais
  Support/Carrinho   carrinho da loja na sessão
routes/              web.php (site), painel.php (dono), admin.php, settings.php
database/            migrations, factories e seeders (DemoSeeder, RealQuadrasSeeder)
docs/                documentação de engenharia (ver docs/README.md)
tests/               Pest: Feature/ (fluxos) e Unit/
```

## Testes e lint

```bash
php artisan test               # suíte inteira (PostgreSQL, banco aluga_quadra_test)
composer test:coverage         # com cobertura (exige pcov ou xdebug)
vendor/bin/pint                # padroniza o código
```

Os testes usam o mesmo SGBD da aplicação, para que recursos específicos do
PostgreSQL (como a *exclusion constraint* das reservas) também sejam testados.
O CI (`.github/workflows/tests.yml`) roda a suíte em PHP 8.3, 8.4 e 8.5 com
cobertura e publica o resumo em cada execução. Estratégia e números em
[docs/testes.md](docs/testes.md).

## Decisões de arquitetura

**Livewire em vez de API + SPA.** Cada tela é um componente Livewire que faz o
papel de controlador e de view. Elimina a duplicação de validação e de rotas
entre front e back, e cabe no tamanho de um TCC sem abrir mão de interatividade.

**Reserva sem sobreposição garantida no banco.** A verificação de horário livre
é feita com a quadra travada (`lockForUpdate`) dentro de uma transação, em
`Reserva::agendarSemConflito()`. Por baixo, a *exclusion constraint*
`reservas_sem_sobreposicao` do PostgreSQL rejeita duas reservas ativas com
horários sobrepostos na mesma quadra, mesmo que duas requisições cheguem juntas.
O mesmo padrão de trava protege as vagas das salas (`Sala::entrarComo()`), e o
estoque da loja é baixado com um decremento condicional dentro da transação do
checkout: se faltar estoque, o pedido inteiro é desfeito.

**Pagamento simulado.** Reserva, sala e pedido são confirmados na hora, sem
gateway. O código Pix exibido é ilustrativo e não é um payload EMV válido.
Integrar um gateway real (Mercado Pago, Stripe) é trabalho futuro.

**"Quadras próximas" vem do OpenStreetMap.** A busca por localização consulta a
Overpass API e mostra quadras reais ao redor do usuário, cadastradas ou não na
plataforma. É uma prova de integração com serviço externo, separada do catálogo
próprio (`/quadras`). Só a latitude e a longitude são enviadas.

**Papéis por coluna e enum.** `users.role` (`jogador`, `dono_quadra`, `admin`)
com o enum `UserRole` e o middleware `EnsureUserHasRole`, sem pacote de ACL. A
regra fina (quem edita qual quadra, quem vê qual pedido) fica nas *policies*.

**Duas stacks de CSS.** O site público usa Bootstrap; as telas de configurações
vieram do starter kit Livewire, com Tailwind e Flux. Ambas são empacotadas pelo
Vite, então o sistema funciona sem internet. Unificar é trabalho futuro.

**PostgreSQL.** Escolhido pelas *constraints* de exclusão (regra de agenda no
banco) e por ser um SGBD relacional completo. As migrations são portáveis; só a
constraint de reservas é específica e é ignorada em outros bancos.

## Documentação

- [docs/README.md](docs/README.md) — índice
- [Modelo de dados](docs/modelo-de-dados.md) · [Arquitetura](docs/arquitetura.md) ·
  [Casos de uso](docs/casos-de-uso.md) · [Requisitos](docs/requisitos.md) ·
  [Testes](docs/testes.md) · [LGPD](docs/lgpd.md) ·
  [Banco de dados](docs/banco-de-dados.md) · [Como avaliar](docs/como-avaliar.md)
- [ROTEIRO-APRESENTACAO.md](ROTEIRO-APRESENTACAO.md) — roteiro da defesa

## Limitações conhecidas e trabalho futuro

- Gateway de pagamento real.
- Notificações por e-mail (hoje são só dentro da plataforma); a verificação de
  e-mail foi retirada, já que o cadastro loga o usuário direto.
- Excluir uma conta apaga também as reservas e pedidos dela do histórico do
  dono; anonimizar seria melhor (ver [docs/lgpd.md](docs/lgpd.md)).
- CPF e CNPJ guardados em texto plano.
- Unificar as duas stacks de CSS.
- `routes/web.php` ainda usa *closures* para várias páginas simples.
