#!/usr/bin/env bash
#
# sincronizar-historico.sh
# -----------------------------------------------------------------------------
# Para quem JÁ tinha um clone do projeto ANTES da reescrita de histórico
# (30/08/2026). Nessa data a branch `main` foi reescrita (remoção do rótulo
# Co-Authored-By de 5 commits) e reenviada com `push --force`.
#
# Um `git pull` normal, nesse cenário, faz um merge das duas histórias e traz
# os commits antigos de volta. Este script alinha a sua cópia com a `main`
# reescrita SEM perder nada: tudo o que a sua cópia tinha é guardado antes
# numa branch de backup local.
#
# Rode no Git Bash (Windows), no WSL ou num terminal Linux/macOS, de dentro
# da pasta do projeto:
#
#     bash scripts/sincronizar-historico.sh
#
# Opções:
#     -y, --yes    não perguntar antes do reset (para uso automatizado)
#     -h, --help   mostra esta ajuda
# -----------------------------------------------------------------------------

set -euo pipefail

REMOTE="origin"
BRANCH="main"

ASSUME_YES=0
for arg in "$@"; do
  case "$arg" in
    -y|--yes)  ASSUME_YES=1 ;;
    -h|--help) sed -n '2,30p' "$0"; exit 0 ;;
    *) echo "Opção desconhecida: $arg" >&2; exit 2 ;;
  esac
done

say()  { printf '\n\033[1m%s\033[0m\n' "$*"; }
info() { printf '  %s\n' "$*"; }
warn() { printf '\033[33m  %s\033[0m\n' "$*"; }
err()  { printf '\033[31m%s\033[0m\n' "$*" >&2; }

# --- 1. Estamos num repositório git? ------------------------------------------
if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  err "Isto não é um repositório git. Entre na pasta do projeto e rode de novo."
  exit 1
fi
cd "$(git rev-parse --show-toplevel)"

URL="$(git remote get-url "$REMOTE" 2>/dev/null || true)"
if [ -z "$URL" ]; then
  err "Não existe o remote '$REMOTE' neste clone."
  exit 1
fi
say "Repositório"
info "pasta:  $(pwd)"
info "remote: $URL"

# --- 2. Buscar a história nova ----------------------------------------------
say "Buscando a história reescrita do servidor..."
git fetch "$REMOTE" --prune --tags

if ! git rev-parse --verify --quiet "$REMOTE/$BRANCH" >/dev/null; then
  err "Não encontrei $REMOTE/$BRANCH depois do fetch. Verifique o nome da branch."
  exit 1
fi

LOCAL_HEAD="$(git rev-parse HEAD)"
REMOTE_HEAD="$(git rev-parse "$REMOTE/$BRANCH")"
CURRENT_BRANCH="$(git symbolic-ref --quiet --short HEAD || echo '(HEAD solta)')"

# --- 3. Já está sincronizado? ---------------------------------------------------
if [ "$LOCAL_HEAD" = "$REMOTE_HEAD" ]; then
  say "Nada a fazer."
  info "Você já está em $REMOTE/$BRANCH ($(git rev-parse --short "$REMOTE_HEAD"))."
  exit 0
fi

# --- 4. Backup de tudo o que a sua cópia tem ---------------------------------
TS="$(date +%Y%m%d-%H%M%S)"
BACKUP="backup/pre-sync-$TS"
git branch "$BACKUP" HEAD
say "Backup local criado"
info "branch '$BACKUP' -> $(git rev-parse --short "$LOCAL_HEAD")"
info "Nada abaixo apaga esta branch. Ela é a sua rede de segurança."

# --- 5. Alterações não commitadas ------------------------------------------------
STASHED=0
if ! git diff --quiet || ! git diff --cached --quiet; then
  warn "Há alterações não commitadas no working tree."
  git stash push --include-untracked --message "pre-sync-$TS" >/dev/null
  STASHED=1
  info "Guardadas no stash (serão reaplicadas no fim)."
fi

# --- 6. O que será substituído ------------------------------------------------
EXTRA="$(git rev-list --count "$REMOTE/$BRANCH..HEAD" 2>/dev/null || echo 0)"
if [ "$EXTRA" -gt 0 ]; then
  say "Commits na sua cópia que NÃO estão na história nova ($EXTRA):"
  git --no-pager log --oneline --decorate "$REMOTE/$BRANCH..HEAD" | sed 's/^/  /'
  echo
  info "Se algum desses for trabalho SEU (e não uma versão antiga de um commit"
  info "reescrito), ele continua salvo em '$BACKUP'. Recupere depois com:"
  info "    git cherry-pick <hash>"
fi

# --- 7. Confirmação ---------------------------------------------------------------
say "Ação: alinhar '$BRANCH' com '$REMOTE/$BRANCH' ($(git rev-parse --short "$REMOTE_HEAD"))"
info "via 'git reset --hard' — o working tree vai passar a refletir a história nova."
if [ "$ASSUME_YES" -ne 1 ]; then
  if [ ! -t 0 ]; then
    err "Sem terminal interativo. Releia o que será feito e rode de novo com --yes."
    exit 1
  fi
  printf '\n  Continuar? [s/N] '
  read -r RESP
  case "$RESP" in
    s|S|sim|SIM|y|Y) ;;
    *) err "Cancelado. Nada foi alterado (o backup '$BACKUP' permanece)."; exit 1 ;;
  esac
fi

# --- 8. Reset ---------------------------------------------------------------------
if [ "$CURRENT_BRANCH" != "$BRANCH" ]; then
  git checkout -B "$BRANCH" "$REMOTE/$BRANCH"
fi
git reset --hard "$REMOTE/$BRANCH"

# --- 9. Reaplicar alterações locais --------------------------------------------
if [ "$STASHED" -eq 1 ]; then
  say "Reaplicando suas alterações não commitadas..."
  if git stash pop; then
    info "Reaplicadas."
  else
    warn "Conflito ao reaplicar. Suas mudanças seguem em 'git stash list'."
    warn "Resolva com 'git checkout --theirs/--ours' ou 'git stash show -p'."
  fi
fi

# --- 10. Limpeza ---------------------------------------------------------------
git remote prune "$REMOTE" >/dev/null 2>&1 || true
git gc --quiet || true

say "Pronto."
info "$BRANCH agora aponta para $(git rev-parse --short HEAD) (= $REMOTE/$BRANCH)."
info "História anterior preservada na branch: $BACKUP"
info "Quando tiver certeza de que não precisa dela:  git branch -D $BACKUP"
