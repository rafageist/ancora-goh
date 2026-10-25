#!/usr/bin/env bash
# One-off database initialization for the Docker Compose deployment (#32).
#
#   docker compose run --rm setup          (or: docker/manage.sh init-db)
#
# Installs the Áncora GOH schema and the default data seed from the
# maintained SQL sources (dev/sql, through dev/sql/install.sh), never from a
# historical dump. It is safe to run again:
#   - if the database exists and holds the Áncora schema, nothing is changed;
#   - if the database exists but is not an Áncora schema, it refuses (exit 3);
#   - otherwise it creates the application role (no superuser, no role
#     creation), installs as that role so the role owns every object, and
#     removes the temporary CREATEDB right afterwards.
# It never drops or overwrites an existing database. A failed installation
# removes only the database it created itself (install.sh).
#
# The seeded administrator "admin" has no usable password until it is set
# with bin/setpassword.php (docker/manage.sh set-admin-password).
set -euo pipefail

say()  { echo "[setup] $*"; }
fail() { echo "[setup] ERROR: $*" >&2; exit "${2:-1}"; }

export PGHOST=${PGHOST:-db} PGPORT=${PGPORT:-5432}
db=${ANCORA_DB_NAME:-ancoragoh}
role=${ANCORA_DB_USER:-ancoragoh}
su_pwfile=${POSTGRES_PASSWORD_FILE:-/run/secrets/postgres_password}
app_pwfile=${ANCORA_DB_PASSWORD_FILE:-/run/secrets/db_password}
sql=/opt/ancora/dev/sql

for v in "$db" "$role"; do
  [[ $v =~ ^[a-z_][a-z0-9_]{0,62}$ ]] || fail "database and role names must match [a-z_][a-z0-9_]*" 64
done
[ "$role" != postgres ] || fail "the application role must not be the superuser" 64
for f in "$su_pwfile" "$app_pwfile"; do
  [ -r "$f" ] || fail "secret not readable: $f (run docker/manage.sh init-secrets)" 64
done
app_pw=$(cat "$app_pwfile")
[[ $app_pw =~ ^[A-Za-z0-9]{16,}$ ]] || fail "the db_password secret must be at least 16 letters and digits" 64

# Passwords reach libpq through a private password file, not the environment.
pgpass=$(mktemp)
trap 'rm -f "$pgpass"' EXIT
chmod 600 "$pgpass"
printf '*:*:*:postgres:%s\n*:*:*:%s:%s\n' "$(cat "$su_pwfile")" "$role" "$app_pw" > "$pgpass"
export PGPASSFILE=$pgpass

as_super() { PGUSER=postgres psql -X -At -v ON_ERROR_STOP=1 -d postgres "$@"; }

for _ in $(seq 1 30); do pg_isready -q -d postgres -U postgres && break; sleep 1; done
pg_isready -q -d postgres -U postgres || fail "database server $PGHOST:$PGPORT is not ready"
say "server: $(as_super -c 'SHOW server_version')"

if [ "$(as_super -c "SELECT count(*) FROM pg_database WHERE datname = '$db'")" = 1 ]; then
  if [ "$(PGUSER=postgres psql -X -At -d "$db" -c "SELECT to_regclass('seguridad.usuario') IS NOT NULL AND to_regclass('public.planificacion') IS NOT NULL")" = t ]; then
    say "database '$db' is already initialized; nothing was changed"
    exit 0
  fi
  fail "database '$db' exists but does not hold the Áncora GOH schema; nothing was changed (see 'Reset' in docs/deployment/docker.md)" 3
fi

# The role password is passed as a psql variable on standard input, so it is
# neither in the command line nor in the server log as a literal statement.
as_super -q -v role="$role" -v pw="$app_pw" <<'SQL'
SELECT format('CREATE ROLE %I LOGIN NOSUPERUSER NOCREATEROLE NOREPLICATION', :'role')
 WHERE NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = :'role') \gexec
SELECT format('ALTER ROLE %I CREATEDB PASSWORD %L', :'role', :'pw') \gexec
SQL
say "role '$role' ready (temporary CREATEDB)"

status=0
PGUSER=$role AGOH_ADMIN_INSTALL_CONFIRM=$db TMPDIR=/tmp \
  "$sql/install.sh" --admin-install --with-data "$db" || status=$?

as_super -q -v role="$role" -v db="$db" <<'SQL'
SELECT format('ALTER ROLE %I NOCREATEDB', :'role') \gexec
SELECT format('REVOKE ALL ON DATABASE %I FROM PUBLIC', :'db')
 WHERE EXISTS (SELECT 1 FROM pg_database WHERE datname = :'db') \gexec
SQL
[ "$status" = 0 ] || fail "installation failed (exit $status); no database was kept" "$status"

say "database '$db' installed from the maintained SQL sources and owned by '$role'"
say "next: set the administrator password (docker/manage.sh set-admin-password)"
