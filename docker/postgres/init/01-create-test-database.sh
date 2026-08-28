#!/bin/bash
# Executado uma unica vez, quando o volume do Postgres e criado pela primeira vez.
# Cria o banco usado pela suite de testes (phpunit.xml aponta para ele).
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE DATABASE aluga_quadra_test;
    GRANT ALL PRIVILEGES ON DATABASE aluga_quadra_test TO $POSTGRES_USER;
EOSQL
