# Banco de dados

A aplicação usa **PostgreSQL 16**. O caminho local (nesta máquina) é um PostgreSQL
**nativo instalado dentro da distro WSL**, sem Docker — decisão tomada para economizar
tempo de instalação. O `docker-compose.yml` continua no repositório e é usado pelo CI
(GitHub Actions); quem preferir Docker localmente também pode usá-lo, mas não é o
caminho padrão descrito aqui.

## Setup automático (outra máquina, outra pessoa)

Quem for clonar o projeto em outro computador não precisa seguir os passos manuais
abaixo: `scripts/configurar-banco.sh` sobe o Postgres via Docker, cria o `.env`,
instala as dependências PHP e roda as migrations (criando as tabelas), com a
opção de popular com o `DemoSeeder`.

```bash
bash scripts/configurar-banco.sh
```

O script verifica Docker/PHP/Composer e explica como instalar o que faltar.

## Instalar (uma vez, dentro da distro WSL)

```bash
sudo apt update
sudo apt install -y postgresql postgresql-contrib php8.3-pgsql
```

## Subir / parar

```bash
sudo service postgresql start    # sobe o PostgreSQL em 127.0.0.1:5432
sudo service postgresql status   # confere se está no ar
sudo service postgresql stop     # para
```

## Criar o role e os bancos (uma vez)

```bash
sudo -u postgres psql -c "CREATE ROLE aluga WITH LOGIN PASSWORD 'secret' CREATEDB;"
sudo -u postgres createdb -O aluga aluga_quadra
sudo -u postgres createdb -O aluga aluga_quadra_test
```

## Alternativa: Docker (CI e quem preferir)

```bash
docker compose up -d      # sobe o PostgreSQL em localhost:5432
docker compose ps         # confere o healthcheck
docker compose down       # para (mantém os dados no volume pgdata)
docker compose down -v    # para e APAGA todos os dados
```

## Bancos e credenciais

| Banco               | Uso                          |
|---------------------|------------------------------|
| `aluga_quadra`      | desenvolvimento (o `.env`)   |
| `aluga_quadra_test` | suíte de testes (`phpunit.xml`) |

Credenciais (dev — não são segredo, batem com o `docker-compose.yml`):

```
host=127.0.0.1  port=5432  user=aluga  password=secret
```

O banco de testes é criado uma única vez, na primeira subida do volume, pelo script
`docker/postgres/init/01-create-test-database.sh`. Se precisar recriá-lo:

```bash
docker compose exec postgres createdb -U aluga aluga_quadra_test
```

## Comandos de migration

```bash
php artisan migrate --seed --seeder=DemoSeeder   # primeira vez
php artisan migrate:fresh --seed --seeder=DemoSeeder   # zera e repovoa
php artisan db:show          # confere a conexão ativa
php artisan migrate:status
```

## Contas de demonstração (após o `DemoSeeder`)

| Papel              | E-mail             | Senha      |
|--------------------|--------------------|------------|
| Jogador            | jogador@demo.com   | `password` |
| Dono de quadra     | dono@demo.com      | `password` |
| Dono de quadra (2) | dono2@demo.com     | `password` |

## Jogador e dono de quadra na mesma tabela `users`

Jogador e dono de quadra vivem na mesma tabela `users`, diferenciados pela
coluna `role` (enum `App\Enums\UserRole`: `jogador` ou `dono_quadra`). Um guard
só (`web`), um provider só (`users`) — o Fortify decide, no login, se o papel
informado (campo `contexto` do formulário) bate com o `role` do usuário
encontrado.

- Campos só de jogador: `cpf`, `data_nascimento`, `sexo`, `nivel`,
  `saldo_creditos`.
- Campos só de dono: `nome_estabelecimento`, `cnpj`, `horario_funcionamento`,
  `metodos_pagamento_aceitos`, `pausa_*`, `chave_pix_recebimento`,
  `comissao_aceita_em`, `prazo_cancelamento_horas`.
- Compartilhados: `name`, `email`, `password`, `telefone`, `avatar_path`,
  two-factor.

FKs que são sempre de dono (`quadras.dono_id`, `produtos.dono_id`,
`pedidos.dono_id`, `excecoes_data.dono_id`) e as de jogador (`reservas.user_id`,
`salas.criador_id` etc.) todas apontam para `users.id` — é a mesma tabela dos
dois lados, então nenhuma delas precisa de relação polimórfica.

## Tabelas extraídas de `users` (`enderecos`, `noticacao_usuarios`)

Duas tabelas próprias, cada uma em relação 1:1 simples com `users`
(`user_id` único — não polimórfica, já que só existe uma tabela de usuário):

- `enderecos` — endereço de cada usuário (jogador ou dono). Campos: `cep`,
  `logradouro`, `numero`, `complemento`, `bairro`, `cidade`, `estado`.
- `noticacao_usuarios` — preferências de notificação; 5 campos de jogador
  (`notif_confirmacao_reserva` etc.) e 3 de dono (`notif_dono_*`) na mesma
  linha, uma por usuário.

O endereço da própria quadra (onde ela fica) é outra coisa, já modelada em
`quadras.endereco/cidade/bairro/cep` — não tem relação com nada disso.

## Testes

`phpunit.xml` aponta a conexão `pgsql` para `aluga_quadra_test`. Os testes usam
`RefreshDatabase`, então cada teste roda numa transação que é revertida no fim — o
banco de testes fica limpo. Basta o PostgreSQL estar no ar:

```bash
sudo service postgresql start   # se ainda não estiver rodando
php artisan test
```

## Portabilidade

As migrations não usam SQL específico de fornecedor — só o *query builder* do
Eloquent — então continuam compatíveis com MySQL/MariaDB e SQLite. A **única**
exceção planejada é a *exclusion constraint* de `reservas` (ver `PLANO.md`, Sessão 3):
ela usa `EXCLUDE USING gist` + extensão `btree_gist`, recursos do PostgreSQL, e serve
para impedir sobreposição de horários da mesma quadra no nível do banco. A migration
que a cria deixa isso documentado num comentário.

## Se a porta 5432 estiver ocupada

Já existe outro PostgreSQL local? Ajuste a porta de escuta em
`/etc/postgresql/16/main/postgresql.conf` (`port = 5433`, por exemplo) e troque
`DB_PORT` no `.env` e em `phpunit.xml` para o mesmo valor.

## Validação local — 05/10/2026

Instalado o PostgreSQL 16 nativamente na distro WSL (`apt install postgresql
postgresql-contrib php8.3-pgsql`), role `aluga` e bancos `aluga_quadra` /
`aluga_quadra_test` criados manualmente. `.env` ajustado para `pgsql`.

**Pegadinha encontrada:** um container Docker (`aluga_quadra_pg`, da Sessão 1
original) já estava rodando havia 5 semanas na porta 5432, sem que ninguém soubesse
— o diagnóstico anterior ("Docker não está instalado") estava errado. Por causa
disso, quando o pacote `postgresql` foi instalado, o Debian/Ubuntu detectou a porta
5432 ocupada e subiu o cluster nativo na **5433** automaticamente. As primeiras
rodadas de migration/seed/teste e a primeira conexão do DBeaver foram todas parar
no container Docker (confirmado comparando `select count(*) from users` via TCP
127.0.0.1:5432 vs `docker exec aluga_quadra_pg psql ...`: mesmo resultado), não no
Postgres nativo recém-instalado.

Resolvido: `docker stop aluga_quadra_pg` (parado, não removido — o volume com os
dados antigos continua intacto se precisar depois) e porta do nativo corrigida de
volta para 5432 em `/etc/postgresql/16/main/postgresql.conf` + `sudo service
postgresql restart`. O container tem `RestartPolicy: no`, então não volta sozinho
num próximo boot do WSL; `postgresql.service` está `enabled` (sobe sozinho).

Depois da correção, contra o Postgres nativo de verdade (`PostgreSQL 16.15 (Ubuntu
16.15-0ubuntu0.24.04.1) on x86_64-pc-linux-gnu` — confirmado pelo `select
version()`, sem `musl`/Alpine):

- `php artisan migrate:fresh --seed --seeder=DemoSeeder` — 47 migrations aplicadas,
  seed concluído sem erro.
- `php artisan db:show` — `PostgreSQL 16.15`, conexão `pgsql`, banco `aluga_quadra`,
  porta `5432`, 28 tabelas.
- `php artisan test` — **359/359 testes passando** contra `aluga_quadra_test`.
- Achado à parte (também já corrigido): o `.env` local ainda tinha `APP_LOCALE=en` /
  `APP_FAKER_LOCALE=en_US` (resquício, item D do diagnóstico). Isso fazia o Faker
  gerar CEPs em formato americano (`12345-6789`, 10 caracteres), que não cabem em
  `quadras.cep varchar(9)` nem existem no Brasil — o SQLite nunca aplicou esse
  limite de tamanho, então o bug ficava invisível antes da troca de banco. Corrigido
  sincronizando o `.env` com o `.env.example` (`pt_BR` nos três). Isso adianta parte
  da Sessão 2 do `PLANO.md`; a tradução de `validation.php`/`auth.php` e o
  `Carbon::setLocale` continuam pendentes.
