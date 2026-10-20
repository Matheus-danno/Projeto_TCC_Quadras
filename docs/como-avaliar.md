# Como avaliar o AlugaQuadra em 5 minutos

Guia para quem vai rodar o projeto pela primeira vez, como a banca ou um
avaliador.

## 1. Subir

Precisa de: Docker, PHP 8.2+ com a extensão `pdo_pgsql`, Composer e Node 22+.

```bash
git clone https://github.com/Matheus-danno/Projeto_TCC_Quadras.git
cd Projeto_TCC_Quadras

bash scripts/configurar-banco.sh   # sobe o PostgreSQL (Docker), cria o .env,
                                   # instala o Composer, roda as migrations
                                   # e popula os dados de demonstração
npm ci && npm run build            # CSS e JS (funciona sem internet depois)
php artisan serve                  # http://127.0.0.1:8000
```

Sem Docker, dá para usar o PostgreSQL instalado na própria máquina ou WSL:
veja [banco-de-dados.md](banco-de-dados.md). Para recomeçar do zero a qualquer
momento: `bash scripts/configurar-banco.sh --fresh`.

## 2. Entrar

Senha de todas as contas: `password`.

| Papel | E-mail | Onde entrar |
|---|---|---|
| Jogador | `jogador@demo.com` | `/login` |
| Dono de quadra | `dono@demo.com` | `/entrar-dono` |
| Administrador | `admin@demo.com` | `/entrar-admin` |

## 3. Roteiro mínimo

1. **Jogador — reservar.** Em **Todas as Quadras**, escolha uma quadra, uma
   data e um horário e confirme; pague com Pix (simulado). Tente reservar o
   mesmo horário de novo: o sistema recusa.
2. **Jogador — sala.** Em **Encontre um Time**, crie uma sala; o sistema
   reserva a quadra junto. Em outra aba anônima, entre com outro jogador e
   participe.
3. **Jogador — loja.** Em **Loja**, adicione um produto ao carrinho, finalize e
   abra a nota de compra.
4. **Jogador — perfil.** Em **Meu perfil**, veja a reserva e o pedido; em
   **Editar Perfil**, clique em **Baixar meus dados**.
5. **Dono.** Em `/painel`, veja a reserva feita no passo 1 na agenda, o pedido
   na loja e o faturamento já com a comissão descontada.
6. **Administrador.** Em `/admin/aprovacoes`, abra um cadastro pendente e
   aprove ou peça ajustes.

## 4. Conferir a qualidade

```bash
php artisan test        # ~420 testes, contra o PostgreSQL (banco aluga_quadra_test)
vendor/bin/pint --test  # padrão de código
```

Mais detalhes: [testes.md](testes.md) e [requisitos.md](requisitos.md)
(cada requisito ligado aos seus testes).

## Problemas comuns

| Sintoma | Solução |
|---|---|
| `could not find driver` | Instale a extensão do PostgreSQL para PHP (`php8.3-pgsql` no Ubuntu). |
| Erro de conexão com o banco | Suba o banco: `docker compose up -d`. |
| Porta 5432 ocupada | Ver [banco-de-dados.md](banco-de-dados.md#se-a-porta-5432-estiver-ocupada). |
| Site sem estilo | Rode `npm run build`. |
