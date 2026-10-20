# Testes

## Como rodar

```bash
php artisan test                 # suíte inteira
php artisan test --filter=Sala   # só os testes cujo nome contém "Sala"
composer test:coverage           # com cobertura (precisa de pcov ou xdebug)
```

Os testes rodam contra o PostgreSQL, no banco `aluga_quadra_test` (configurado
em `phpunit.xml`). Cada teste começa com o banco vazio (`RefreshDatabase`) e
cria os próprios dados com *factories* (`database/factories`).

## Estratégia

A suíte tem cerca de 420 testes, quase todos **de feature**: exercitam uma tela
ou rota inteira (componente Livewire ou requisição HTTP), passando por
validação, autorização, regras de negócio e banco de dados, como o usuário
faria. É onde ficam as regras de um sistema como este: quem pode reservar o
quê, quando o horário está livre, quanto é a comissão.

Testes **unitários** ficam para lógica isolada e integração externa, como o
`OverpassQuadraFinder` (com respostas simuladas da API, sem rede).

Algumas escolhas:

- **Mesmo SGBD da aplicação.** Rodar em PostgreSQL, e não em SQLite em memória,
  permite testar de verdade a *exclusion constraint* das reservas
  (`ReservaSemSobreposicaoTest` insere direto no banco e espera o erro) e os
  `lockForUpdate`.
- **Concorrência simulada.** Não dá para disparar duas requisições ao mesmo
  tempo num teste. Simula-se o estado que a corrida produziria: a tela é aberta
  com a vaga livre, outra pessoa ocupa a vaga, e só então a confirmação é
  enviada (`Salas/VagasConcorrentesTest`). Também se força a verificação da
  aplicação a "não ver" o conflito, para provar que o banco barra
  (`ReservaSemSobreposicaoTest`).
- **Serviços externos falsificados.** Overpass e Nominatim são testados com
  `Http::fake()`; a suíte não depende de internet.
- **Autorização testada pelos dois lados.** Para cada *policy*, um teste de
  quem pode e outro de quem não pode (`*PolicyTest`, `RoleAccessTest`).
- **Factory sem colisões.** A `ReservaFactory` gera dia e hora em sequência, e
  não sorteados, para que várias reservas da mesma quadra num teste nunca se
  sobreponham por acaso. Isso tornaria os testes instáveis, agora que o banco
  rejeita sobreposição.

## Organização

| Pasta / arquivo | Cobre |
|---|---|
| `Feature/Auth` | Cadastro de jogador e de dono, login, senha, 2FA |
| `Feature/Perfil` | Área do jogador: reservas, salas, pedidos, mensagens, notificações, pagamentos, segurança |
| `Feature/Painel` | Painel do dono: dashboard, quadras, reservas, agenda, agendamento manual, financeiro, loja, mensagens |
| `Feature/Admin`, `Feature/Aprovacao` | Aprovação de cadastros, comissão, repasses, bloqueio |
| `Feature/Salas`, `SalaTest`, `SalaChatTest`, `SalaPolicyTest` | Criar, entrar, aprovar, chat, avaliação, vagas concorrentes |
| `QuadraReservaTest`, `QuadraDisponibilidadeTest`, `ReservaSemSobreposicaoTest`, `ReservaCancelamentoTest` | Busca, horários livres, reserva sem sobreposição, cancelamento |
| `CarrinhoTest`, `PedidoPolicyTest` | Loja, checkout, estoque, acesso a pedidos |
| `Feature/Privacidade` | Páginas legais e "Baixar meus dados" |
| `Unit/` | `OverpassQuadraFinder` |

A ligação de cada requisito com seus testes está em
[requisitos.md](requisitos.md).

## Cobertura

O CI (`.github/workflows/tests.yml`) roda a suíte com cobertura (xdebug) em PHP
8.3, 8.4 e 8.5 e publica a tabela por arquivo no resumo de cada execução, na aba
*Actions* do GitHub. Localmente, `composer test:coverage` gera a mesma tabela e
o arquivo `coverage.xml`, desde que o PHP tenha `pcov` ou `xdebug`
(`sudo apt install php8.3-pcov`).

> Para a monografia: copie a tabela da última execução do CI (ou rode
> `composer test:coverage`) e registre aqui a cobertura total e por diretório.
