#!/usr/bin/env bash
#
# configurar-banco.sh
# -----------------------------------------------------------------------------
# Recria em outra maquina o banco de dados do AlugaQuadra: sobe o PostgreSQL
# via Docker, cria o `.env`, instala as dependencias PHP e roda as migrations
# (criando todas as tabelas). Ao final, opcionalmente popula com dados de
# demonstracao (DemoSeeder).
#
# Pre-requisitos (o script detecta o que falta e explica como instalar):
#   - Docker + Docker Compose
#   - PHP 8.2+ e Composer
#
# Uso, de dentro da pasta do projeto (Git Bash, WSL ou terminal Linux/macOS):
#
#     bash scripts/configurar-banco.sh
#
# Opcoes:
#     --fresh      apaga e recria todas as tabelas (php artisan migrate:fresh)
#     --sem-seed   nao popular com as contas de demonstracao (DemoSeeder)
#     -y, --yes    nao perguntar antes de acoes destrutivas (usa com --fresh)
#     -h, --help   mostra esta ajuda
# -----------------------------------------------------------------------------

set -euo pipefail

FRESH=0
SEED=1
ASSUME_YES=0
for arg in "$@"; do
  case "$arg" in
    --fresh)     FRESH=1 ;;
    --sem-seed)  SEED=0 ;;
    -y|--yes)    ASSUME_YES=1 ;;
    -h|--help)   sed -n '2,24p' "$0"; exit 0 ;;
    *) echo "Opcao desconhecida: $arg" >&2; exit 2 ;;
  esac
done

say()  { printf '\n\033[1m%s\033[0m\n' "$*"; }
info() { printf '  %s\n' "$*"; }
warn() { printf '\033[33m  %s\033[0m\n' "$*"; }
err()  { printf '\033[31m%s\033[0m\n' "$*" >&2; }

# --- 0. Detectar o sistema (para mostrar o comando de instalacao certo) -----
IS_WSL=0
grep -qi microsoft /proc/version 2>/dev/null && IS_WSL=1
case "$(uname -s)" in
  Linux*)              OS="linux" ;;
  Darwin*)             OS="macos" ;;
  MINGW*|MSYS*|CYGWIN*) OS="windows" ;;
  *)                   OS="desconhecido" ;;
esac

# --- 1. Estamos na pasta do projeto? -----------------------------------------
if [ ! -f "composer.json" ] || [ ! -f "docker-compose.yml" ]; then
  if git rev-parse --show-toplevel >/dev/null 2>&1; then
    cd "$(git rev-parse --show-toplevel)"
  fi
fi
if [ ! -f "composer.json" ] || [ ! -f "docker-compose.yml" ]; then
  err "Rode este script de dentro da pasta do projeto (onde ficam composer.json e docker-compose.yml)."
  exit 1
fi

# --- 2. Docker instalado e rodando? ------------------------------------------
say "Verificando Docker..."
if ! command -v docker >/dev/null 2>&1; then
  err "Docker nao encontrado."
  case "$OS" in
    linux)
      if [ "$IS_WSL" -eq 1 ]; then
        info "Dentro da distro WSL, instale com:"
      else
        info "Instale com:"
      fi
      info "    sudo apt update && sudo apt install -y docker.io docker-compose-v2"
      info "    sudo usermod -aG docker \$USER   # depois saia e entre de novo na sessao"
      info "    sudo service docker start"
      info "Se o pacote da sua distro for diferente, veja https://docs.docker.com/engine/install/"
      ;;
    macos)
      info "Instale o Docker Desktop: https://www.docker.com/products/docker-desktop/"
      info "(ou 'brew install --cask docker', se usar Homebrew)"
      ;;
    windows)
      info "Instale o Docker Desktop (com o back-end WSL2): https://www.docker.com/products/docker-desktop/"
      ;;
    *)
      info "Veja https://docs.docker.com/get-docker/"
      ;;
  esac
  exit 1
fi

DC="docker compose"
if ! $DC version >/dev/null 2>&1; then
  if command -v docker-compose >/dev/null 2>&1; then
    DC="docker-compose"
  else
    err "Docker encontrado, mas sem o Compose. Instale o plugin 'docker-compose-v2' (Linux) ou atualize o Docker Desktop."
    exit 1
  fi
fi

if ! docker info >/dev/null 2>&1; then
  err "O Docker esta instalado mas nao esta rodando."
  if [ "$IS_WSL" -eq 1 ] || [ "$OS" = "linux" ]; then
    info "Inicie com: sudo service docker start"
  else
    info "Abra o Docker Desktop e espere ele ficar pronto, depois rode este script de novo."
  fi
  exit 1
fi
info "OK."

# --- 3. Subir o PostgreSQL ----------------------------------------------------
say "Subindo o PostgreSQL (docker compose up -d)..."
if ! $DC up -d; then
  err "Falha ao subir o container."
  info "Se a porta 5432 ja estiver em uso por outro Postgres nesta maquina, veja a"
  info "secao 'Se a porta 5432 estiver ocupada' em docs/banco-de-dados.md."
  exit 1
fi

info "Aguardando o banco ficar pronto..."
TRIES=0
until $DC exec -T postgres pg_isready -U aluga -d aluga_quadra >/dev/null 2>&1; do
  TRIES=$((TRIES + 1))
  if [ "$TRIES" -ge 30 ]; then
    err "O PostgreSQL nao respondeu depois de 30s. Rode '$DC logs postgres' para investigar."
    exit 1
  fi
  sleep 1
done
info "Banco no ar (aluga_quadra e aluga_quadra_test)."

# --- 4. PHP e Composer --------------------------------------------------------
say "Verificando PHP e Composer..."
if ! command -v php >/dev/null 2>&1; then
  err "PHP nao encontrado (precisa do PHP 8.2+)."
  case "$OS" in
    linux)
      info "Instale com: sudo apt install -y php8.3-cli php8.3-pgsql php8.3-xml php8.3-mbstring php8.3-curl unzip"
      ;;
    macos)
      info "Instale com: brew install php"
      ;;
    *)
      info "Veja https://www.php.net/manual/pt_BR/install.php"
      ;;
  esac
  exit 1
fi
if ! command -v composer >/dev/null 2>&1; then
  err "Composer nao encontrado."
  info "Instale seguindo https://getcomposer.org/download/"
  exit 1
fi
info "OK."

# --- 5. .env -------------------------------------------------------------------
if [ ! -f ".env" ]; then
  say "Criando .env a partir do .env.example..."
  cp .env.example .env
else
  info ".env ja existe, mantido como esta."
fi

# --- 6. Dependencias PHP -------------------------------------------------------
if [ ! -f "vendor/autoload.php" ]; then
  say "Instalando dependencias PHP (composer install)..."
  composer install
fi

# --- 7. APP_KEY ------------------------------------------------------------
if ! grep -q '^APP_KEY=.\+' .env; then
  say "Gerando APP_KEY..."
  php artisan key:generate
fi

# --- 8. Migrations (criacao das tabelas) --------------------------------------
if [ "$FRESH" -eq 1 ] && [ "$ASSUME_YES" -ne 1 ]; then
  warn "--fresh vai APAGAR todas as tabelas existentes em aluga_quadra e recria-las."
  if [ ! -t 0 ]; then
    err "Sem terminal interativo. Rode de novo com --yes para confirmar."
    exit 1
  fi
  printf '\n  Continuar? [s/N] '
  read -r RESP
  case "$RESP" in
    s|S|sim|SIM|y|Y) ;;
    *) err "Cancelado."; exit 1 ;;
  esac
fi

MIGRATE_CMD="migrate"
[ "$FRESH" -eq 1 ] && MIGRATE_CMD="migrate:fresh"

say "Criando as tabelas (php artisan $MIGRATE_CMD)..."
if [ "$SEED" -eq 1 ]; then
  php artisan "$MIGRATE_CMD" --seed --seeder=DemoSeeder
else
  php artisan "$MIGRATE_CMD"
fi

# --- 9. Resumo -----------------------------------------------------------------
say "Pronto! Banco de dados configurado."
info "Conexao: host=127.0.0.1 port=5432 banco=aluga_quadra usuario=aluga senha=secret"
if [ "$SEED" -eq 1 ]; then
  info "Contas de demonstracao (senha 'password' para todas):"
  info "  jogador@demo.com, dono@demo.com, dono2@demo.com"
fi
info "Para rodar a aplicacao: composer run dev"
info "Para rodar os testes:   php artisan test"
