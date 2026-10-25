#!/usr/bin/env bash
# Everyday commands for the local Docker Compose deployment (#32).
# Every command is a thin wrapper around "docker compose"; the equivalent
# plain commands are listed in docs/deployment/docker.md.
#
#   docker/manage.sh init-secrets            create secrets/ (never overwrites)
#   docker/manage.sh up                      build the images and start db + web
#   docker/manage.sh init-db                 install the database (safe to repeat)
#   docker/manage.sh set-admin-password      prompt for the "admin" password
#   docker/manage.sh set-admin-password --generate FILE
#                                            random password, written to FILE (0600)
#   docker/manage.sh status | logs [service] | stop | start | restart
#   docker/manage.sh rebuild                 rebuild after code changes, keep data
#   docker/manage.sh down                    remove the containers, keep data
#   docker/manage.sh backup [DIR]            database dump into DIR (default backups/)
#   docker/manage.sh restore FILE            replace the database with a dump (asks)
#   docker/manage.sh reset                   DELETE ALL DATA (asks)
#   docker/manage.sh e2e [--deployed|--verify-persisted]   docker/e2e.sh
#
# The application is published on 127.0.0.1:8087 by default (ANCORA_BIND_ADDRESS
# and ANCORA_HTTP_PORT in .env). It is not safe for an untrusted network:
# docs/deployment/docker.md.
set -euo pipefail

repo=$(cd "$(dirname "$0")/.." && pwd)
cd "$repo"
secrets=${ANCORA_SECRETS_DIR:-$repo/secrets}
dbname=${ANCORA_DB_NAME:-ancoragoh}
dbuser=${ANCORA_DB_USER:-ancoragoh}

dc() { docker compose -f "$repo/compose.yaml" "$@"; }
die() { echo "manage: $*" >&2; exit 1; }
project() { dc config --format json | sed -n 's/^ *"name": *"\([^"]*\)".*/\1/p' | head -1; }
http_port() {
  local p=${ANCORA_HTTP_PORT:-}
  [ -n "$p" ] || p=$(sed -n 's/^ANCORA_HTTP_PORT=\([0-9]*\).*/\1/p' "$repo/.env" 2>/dev/null | tail -1)
  echo "${p:-8087}"
}
bind_address() {
  local a=${ANCORA_BIND_ADDRESS:-}
  [ -n "$a" ] || a=$(sed -n 's/^ANCORA_BIND_ADDRESS=\([^ #]*\).*/\1/p' "$repo/.env" 2>/dev/null | tail -1)
  echo "${a:-127.0.0.1}"
}
need_secrets() {
  for f in postgres_password db_password; do
    [ -s "$secrets/$f" ] || die "missing $secrets/$f: run docker/manage.sh init-secrets"
  done
}
web_running() { [ -n "$(dc ps -q --status running web 2>/dev/null)" ]; }
db_running() { [ -n "$(dc ps -q --status running db 2>/dev/null)" ]; }
confirm() { # expected answer, prompt
  local answer=${ANCORA_CONFIRM:-}
  if [ -z "$answer" ]; then
    [ -t 0 ] || die "confirmation needed: run interactively or set ANCORA_CONFIRM=$1"
    read -r -p "$2" answer
  fi
  [ "$answer" = "$1" ] || die "not confirmed; nothing was changed"
}
backup_to() { # file
  ( umask 077; dc exec -T db pg_dump -U postgres --format=custom --no-password -d "$dbname" > "$1" ) \
    || { rm -f "$1"; die "backup failed"; }
}

cmd=${1:-help}; shift || true
case "$cmd" in
  init-secrets)
    # 0700 directory, 0644 files: the files are bind-mounted into containers
    # that run as unprivileged users; the directory keeps them private on the host.
    mkdir -p "$secrets"; chmod 700 "$secrets"
    for f in postgres_password db_password; do
      if [ -e "$secrets/$f" ]; then echo "kept     $secrets/$f"; continue; fi
      od -An -N24 -tx1 /dev/urandom | tr -d ' \n' > "$secrets/$f"
      chmod 644 "$secrets/$f"
      echo "created  $secrets/$f"
    done
    ;;

  up)
    need_secrets
    port=$(http_port)
    if ! web_running && (exec 3<>"/dev/tcp/127.0.0.1/$port") 2>/dev/null; then
      die "127.0.0.1:$port is already in use; set another ANCORA_HTTP_PORT in .env"
    fi
    dc up -d --build --wait --wait-timeout 240 db web
    addr=$(bind_address)
    if [ "$addr" = 127.0.0.1 ]; then
      echo "Áncora GOH: http://127.0.0.1:$port/ (local only)"
    else
      echo "Áncora GOH: http://127.0.0.1:$port/, published on $addr:$port"
      echo "WARNING: reachable from the network. The application is not safe on an untrusted network."
    fi
    ;;

  init-db)
    need_secrets
    dc run --rm --build setup
    ;;

  set-admin-password)
    web_running || die "the web service is not running: docker/manage.sh up"
    if [ "${1:-}" = --generate ]; then
      out=${2:-}; [ -n "$out" ] || die "usage: set-admin-password --generate FILE"
      [ ! -e "$out" ] || die "$out exists; choose a new file"
      pw=$(od -An -N15 -tx1 /dev/urandom | tr -d ' \n')
      ( umask 077; printf '%s\n' "$pw" > "$out" )
      echo "generated password written to $out (mode 0600); delete it after you have stored it safely"
    else
      [ -t 0 ] || die "run interactively, or use --generate FILE"
      read -r -s -p "New password for admin (at least 12 characters): " pw; echo
      read -r -s -p "Repeat it: " pw2; echo
      [ "$pw" = "$pw2" ] || die "the passwords differ; nothing was changed"
      [ "${#pw}" -ge 12 ] || die "too short; nothing was changed"
      unset pw2
    fi
    printf '%s\n' "$pw" | dc exec -T -w /var/www/ancora/bin web php -d 'error_reporting=E_ALL & ~E_DEPRECATED' setpassword.php -nick admin >/dev/null \
      || die "bin/setpassword.php failed (is the database initialized? docker/manage.sh init-db)"
    unset pw
    echo "administrator password set; log in as 'admin' at http://127.0.0.1:$(http_port)/"
    ;;

  status) dc ps ;;
  logs) dc logs --tail=200 "$@" ;;
  stop) dc stop ;;
  start) need_secrets; dc start ;;
  restart) need_secrets; dc restart ;;

  rebuild)
    need_secrets
    dc build --pull web
    dc --profile setup build --pull setup
    dc up -d --wait --wait-timeout 240 db web
    ;;

  down)
    # never with -v: the data volume is kept
    dc down
    ;;

  backup)
    db_running || die "the db service is not running: docker/manage.sh up"
    dir=${1:-$repo/backups}
    mkdir -p "$dir"; chmod 700 "$dir"
    file=$dir/$dbname-$(date -u +%Y%m%dT%H%M%SZ).dump
    backup_to "$file"
    echo "backup written to $file ($(du -h "$file" | cut -f1)); it holds all data, including password hashes: keep it private"
    ;;

  restore)
    file=${1:-}; [ -n "$file" ] && [ -r "$file" ] || die "usage: restore FILE (a dump made by docker/manage.sh backup)"
    db_running || die "the db service is not running: docker/manage.sh up"
    echo "WARNING: this replaces the whole '$dbname' database with $file."
    echo "         Everything entered since that backup is lost (a safety backup is made first)."
    confirm "$dbname" "Type the database name ($dbname) to continue: "
    mkdir -p "$repo/backups"; chmod 700 "$repo/backups"
    safety=$repo/backups/$dbname-before-restore-$(date -u +%Y%m%dT%H%M%SZ).dump
    if [ "$(dc exec -T db psql -X -At -U postgres -d postgres -c "SELECT count(*) FROM pg_database WHERE datname = '$dbname'")" = 1 ]; then
      backup_to "$safety"; echo "safety backup: $safety"
    fi
    dc stop web
    dc exec -T db dropdb -U postgres --if-exists "$dbname"
    dc exec -T db createdb -U postgres -O "$dbuser" "$dbname"
    dc exec -T db pg_restore -U postgres --no-owner --role="$dbuser" --exit-on-error -d "$dbname" < "$file" \
      || die "restore failed; the safety backup is $safety (web stays stopped)"
    dc exec -T db psql -X -q -U postgres -d postgres -c "REVOKE ALL ON DATABASE \"$dbname\" FROM PUBLIC"
    dc start web
    echo "restored $file into $dbname"
    ;;

  reset)
    p=$(project); vol=${p}_pgdata
    echo "WARNING: reset DELETES ALL DATA of the '$p' deployment: the database volume"
    echo "         $vol is removed. This cannot be undone. Make a backup first"
    echo "         (docker/manage.sh backup). The secrets in $secrets are kept."
    confirm "delete $p" "Type 'delete $p' to continue: "
    dc --profile setup down --remove-orphans
    if docker volume inspect "$vol" >/dev/null 2>&1; then
      [ "$(docker volume inspect -f '{{index .Labels "com.docker.compose.project"}}' "$vol")" = "$p" ] \
        || die "volume $vol does not belong to project $p; not removed"
      docker volume rm "$vol" >/dev/null
      echo "removed volume $vol"
    fi
    echo "next: docker/manage.sh up && docker/manage.sh init-db && docker/manage.sh set-admin-password"
    ;;

  e2e) exec "$repo/docker/e2e.sh" "$@" ;;

  help|-h|--help) sed -n '2,22p' "$0" | sed 's/^# \{0,1\}//' ;;
  *) die "unknown command '$cmd' (docker/manage.sh help)" ;;
esac
