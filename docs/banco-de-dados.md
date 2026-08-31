# Banco de dados

A aplicação usa **PostgreSQL 16**. O `docker-compose.yml` na raiz sobe o banco já
com as duas bases criadas — não é preciso instalar PostgreSQL na máquina.

## Subir / parar

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

## Testes

`phpunit.xml` aponta a conexão `pgsql` para `aluga_quadra_test`. Os testes usam
`RefreshDatabase`, então cada teste roda numa transação que é revertida no fim — o
banco de testes fica limpo. Basta ter o container de pé:

```bash
docker compose up -d
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

Já existe outro PostgreSQL local? Mude a porta publicada no `docker-compose.yml`
(ex.: `"5433:5432"`) e ajuste `DB_PORT` no `.env` e em `phpunit.xml`.
