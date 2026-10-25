#!/usr/bin/env bash
# Fresh Áncora GOH database from the tracked sources in dev/sql/ (issue #9).
#
#   dev/sql/install.sh [--with-data] <dbname>
#   dev/sql/install.sh --admin-install [--with-data] <dbname>
#
# Builds the bundle with compilasql.php, creates <dbname> (it must not exist),
# and loads the bundle in one transaction with ON_ERROR_STOP=1 and
# check_function_bodies=on. Any SQL error aborts and leaves no database.
# Connection comes from the libpq environment. By default the target must be a
# verified disposable cluster from dev/sql/tools/with-ephemeral-pg.sh; anything
# else is refused before connecting (tools/disposable-guard.sh, #26).
# --admin-install is the deliberate opt-in for a server the operator chose: it
# also requires AGOH_ADMIN_INSTALL_CONFIRM=<dbname> and an explicit PGHOST,
# PGHOSTADDR or PGSERVICE. Tests and CI never use it.
#
# --with-data also loads datos/datos_por_defecto.sql: the default roles and an
# administrator account "admin" WITHOUT a usable password; set one with
# php bin/setpassword.php -nick admin before the first login. Never use the
# data seed on a reachable server (docs/modernization/security-privacy.md).
#
# This installs an empty database only. It is not an upgrade path.
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
php=${PHP:-php}

data=(); admin=0
while [ $# -gt 1 ]; do
  case "$1" in
    --with-data) data=(--with-data) ;;
    --admin-install) admin=1 ;;
    *) break ;;
  esac
  shift
done
[ $# -eq 1 ] && [ "${1#-}" = "$1" ] || { sed -n '2,22p' "$0" | sed 's/^# \{0,1\}//'; exit 2; }
db=$1

. "$here/tools/disposable-guard.sh"
if [ $admin = 1 ]; then
  agoh_require_admin_confirmation "$db" || exit 3
else
  agoh_require_disposable || exit 3
fi

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
