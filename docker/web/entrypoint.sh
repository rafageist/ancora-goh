#!/bin/sh
# Web container start (#32): write the database connection file that the
# application reads as bin/ancoragoh.conf (a link to /run/ancora), then run
# the command (Apache by default).
#
# Settings come from the environment (compose.yaml); the password comes from
# the db_password Docker secret. Values are checked against a strict
# character set before they are written, because the file is parsed as PHP
# literals (lib/basic/file/PropertiesFile.php).
set -eu

fail() { echo "ancora-entrypoint: $*" >&2; exit 64; }

host=${ANCORA_DB_HOST:-db}
port=${ANCORA_DB_PORT:-5432}
name=${ANCORA_DB_NAME:-ancoragoh}
user=${ANCORA_DB_USER:-ancoragoh}
pwfile=${ANCORA_DB_PASSWORD_FILE:-/run/secrets/db_password}

for v in "$host" "$name" "$user"; do
  case "$v" in ''|*[!A-Za-z0-9_.-]*) fail "database host, name and user may only contain letters, digits, '_', '.' and '-'" ;; esac
done
case "$port" in ''|*[!0-9]*) fail "ANCORA_DB_PORT must be a number" ;; esac
[ -r "$pwfile" ] || fail "database password secret not readable: $pwfile (run docker/manage.sh init-secrets)"
pw=$(cat "$pwfile")
case "$pw" in ''|*[!A-Za-z0-9]*) fail "the database password secret must contain letters and digits only" ;; esac
[ "${#pw}" -ge 16 ] || fail "the database password secret must have at least 16 characters"

umask 077
conf=/run/ancora/ancoragoh.conf
cat > "$conf.tmp" <<EOF
# Generated at container start by docker/web/entrypoint.sh. Do not edit.
host = "$host"
port = $port
dbname = "$name"
user = "$user"
password = "$pw"
EOF
mv "$conf.tmp" "$conf"
unset pw

exec "$@"
