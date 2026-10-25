#!/usr/bin/env bash
# Fresh Áncora GOH database from the tracked sources in dev/sql/ (issue #9).
#
#   dev/sql/install.sh [--with-data] <dbname>
#
# Builds the bundle with compilasql.php, creates <dbname> (it must not exist),
# and loads the bundle in one transaction with ON_ERROR_STOP=1 and
# check_function_bodies=on. Any SQL error aborts and leaves no database.
# Connection comes from the libpq environment (PGHOST, PGPORT, PGUSER, ~/.pgpass);
# for a throwaway server use dev/sql/tools/with-ephemeral-pg.sh.
#
# --with-data also loads datos/datos_por_defecto.sql: the default roles and an
# administrator account with a well-known default password. Change it at once,
# and never use it on a reachable server (docs/modernization/security-privacy.md).
#
# This installs an empty database only. It is not an upgrade path.
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
php=${PHP:-php}

data=()
if [ "${1:-}" = --with-data ]; then data=(--with-data); shift; fi
[ $# -eq 1 ] || { sed -n '2,16p' "$0" | sed 's/^# \{0,1\}//'; exit 2; }
db=$1

bundle=$(mktemp "${TMPDIR:-/tmp}/agoh-bundle.XXXXXX.sql")
trap 'rm -f "$bundle"' EXIT
"$php" "$here/compilasql.php" "${data[@]}" --out "$bundle"

createdb "$db"
if ! PGOPTIONS="-c check_function_bodies=on -c client_min_messages=warning" \
     psql -X -q -v ON_ERROR_STOP=1 --single-transaction -d "$db" -f "$bundle"; then
  dropdb --if-exists "$db"
  echo "[FAIL] installation aborted; database $db removed" >&2
  exit 1
fi
echo "[OK] installed $db"
