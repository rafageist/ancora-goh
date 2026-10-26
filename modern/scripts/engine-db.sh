#!/usr/bin/env bash
# Provision the scheduling engine database of one site (issue #71).
#
#   engine-db.sh create [--with-data] [--admin-install] [--if-missing] [--password-file FILE] <database> <role>
#   engine-db.sh check-isolation <database> <own role> <other role>
#
# create:
#   1. installs the maintained legacy MPI/Prioriza schema into a NEW database
#      with dev/sql/install.sh (one transaction; the database is removed on
#      any error). With --if-missing, an existing database that already holds
#      the schema is kept as it is (re-runs of the provisioning of #72), and
#      only steps 2 and 3 run. --with-data also loads the legacy seed
#      (datos/datos_por_defecto.sql: roles and an administrator WITHOUT a
#      usable password), which synthetic fixtures such as dev/e2e/fixture.sql
#      need as record owner;
#   2. creates the site's engine runtime role (LOGIN, no superuser, no
#      CREATEDB/CREATEROLE), unless it already exists, and sets its password
#      from --password-file when given (never from the command line). It
#      fails if the role has SUPERUSER, REPLICATION, BYPASSRLS, CREATEDB or
#      CREATEROLE. Nothing here needs a superuser: an operator role with
#      CREATEDB and CREATEROLE (PostgreSQL 16+ rules) can run it;
#   3. revokes CONNECT and TEMP on the database from PUBLIC and grants the
#      runtime role exactly: CONNECT and TEMP on this database, USAGE on its
#      schemas, SELECT/INSERT/UPDATE/DELETE on its tables, USAGE/SELECT/UPDATE
#      on its sequences and EXECUTE on its functions. Nothing is granted on
#      any other database, so the role of site A cannot even connect to the
#      engine database of site B.
#
# check-isolation: exits 0 only if <own role> can connect to <database> and
#   <other role> cannot.
#
# The libpq environment selects the server. By default it must be a verified
# disposable cluster (dev/sql/tools/with-ephemeral-pg.sh); --admin-install is
# the deliberate opt-in for a server the operator chose, and also requires
# AGOH_ADMIN_INSTALL_CONFIRM=<database> (see dev/sql/install.sh). The
# provisioning of #72 calls "engine-db.sh create" once per organization.
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd -P)
repo=$(cd "$here/../.." && pwd -P)
# shellcheck source=../../dev/sql/tools/disposable-guard.sh
. "$repo/dev/sql/tools/disposable-guard.sh"

usage() { sed -n '2,4p' "$0" | sed 's/^# \{0,1\}//'; exit 2; }
ident() { [[ $1 =~ ^[a-z_][a-z0-9_]{0,62}$ ]] || { echo "invalid identifier: $1 (lowercase letters, digits, _)" >&2; exit 2; }; }

cmd=${1:-}; [ $# -gt 0 ] && shift
case "$cmd" in
  create)
    install_args=(); admin=0; password_file=""; if_missing=0
    while [ $# -gt 0 ]; do
      case "$1" in
        --with-data) install_args+=(--with-data) ;;
        --admin-install) admin=1; install_args+=(--admin-install) ;;
        --if-missing) if_missing=1 ;;
        --password-file) password_file=${2:-}; shift ;;
        -*) usage ;;
        *) break ;;
      esac
      shift
    done
    [ $# -eq 2 ] || usage
    db=$1 role=$2
    ident "$db"; ident "$role"
    if [ $admin = 1 ]; then agoh_require_admin_confirmation "$db" || exit 3; else agoh_require_disposable || exit 3; fi
    if [ -n "$password_file" ] && { [ ! -r "$password_file" ] || [ ! -s "$password_file" ]; }; then
      echo "the password file is missing, unreadable or empty" >&2; exit 2
    fi

    exists=$(psql -X -At -d postgres -v db="$db" <<<"SELECT count(*) FROM pg_database WHERE datname = :'db'")
    if [ "$exists" = 1 ] && [ $if_missing = 1 ]; then
      # install.sh is one transaction and drops the database on failure, so an
      # existing database is complete; check it is an engine database anyway.
      [ "$(psql -X -At -d "$db" -c "SELECT to_regclass('public.actividad') IS NOT NULL AND to_regproc('public.generar') IS NOT NULL")" = t ] \
        || { echo "database $db exists but does not hold the engine schema" >&2; exit 1; }
      echo "[OK] engine database $db already installed"
    else
      "$repo/dev/sql/install.sh" "${install_args[@]}" "$db"
    fi

    psql -X -q -v ON_ERROR_STOP=1 -d "$db" -v role="$role" <<'SQL'
SELECT NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = :'role') AS create_role \gset
\if :create_role
  CREATE ROLE :"role" LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT;
\endif
SQL
    # psql reads the password file itself: the password never appears in a
    # process list, a command line or a log.
    if [ -n "$password_file" ]; then
      psql -X -q -v ON_ERROR_STOP=1 -d "$db" -v role="$role" <<SQL
\\set password \`cat "$password_file"\`
ALTER ROLE :"role" PASSWORD :'password';
SQL
    fi
    psql -X -q -v ON_ERROR_STOP=1 -d "$db" -v role="$role" -v db="$db" <<'SQL'
SELECT NOT (rolsuper OR rolreplication OR rolbypassrls OR rolcreatedb OR rolcreaterole) AS role_ok
  FROM pg_roles WHERE rolname = :'role' \gset
\if :role_ok
\else
  \echo 'the engine role has SUPERUSER, REPLICATION, BYPASSRLS, CREATEDB or CREATEROLE'
  SELECT 1/0;
\endif
REVOKE ALL ON DATABASE :"db" FROM PUBLIC;
GRANT CONNECT, TEMPORARY ON DATABASE :"db" TO :"role";
SELECT format('GRANT USAGE ON SCHEMA %I TO %I', nspname, :'role'),
       format('GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA %I TO %I', nspname, :'role'),
       format('GRANT USAGE, SELECT, UPDATE ON ALL SEQUENCES IN SCHEMA %I TO %I', nspname, :'role'),
       format('GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA %I TO %I', nspname, :'role')
  FROM pg_namespace
 WHERE nspname NOT LIKE 'pg\_%' AND nspname <> 'information_schema'
\gexec
SQL
    echo "[OK] engine database $db ready for role $role"
    ;;
  check-isolation)
    [ $# -eq 3 ] || usage
    db=$1 own=$2 other=$3
    ident "$db"; ident "$own"; ident "$other"
    agoh_require_disposable || exit 3
    if ! psql -X -At -U "$own" -d "$db" -c 'SELECT 1' >/dev/null 2>&1; then
      echo "[FAIL] $own cannot connect to $db" >&2; exit 1
    fi
    if out=$(psql -X -At -U "$other" -d "$db" -c 'SELECT 1' 2>&1); then
      echo "[FAIL] $other can connect to $db" >&2; exit 1
    fi
    case "$out" in
      *"permission denied for database"*) echo "[OK] $other is refused by $db (permission denied); $own connects" ;;
      *) echo "[FAIL] $other could not connect to $db, but not because of a missing CONNECT privilege" >&2; exit 1 ;;
    esac
    ;;
  *) usage ;;
esac
