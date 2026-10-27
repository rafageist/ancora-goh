#!/usr/bin/env bash
# Installs the unchanged legacy engine and the job workspace layer into an
# existing tenant database (issues #93, #95, #96).
#
#   dev/workspace/install.sh [--worker <login role>] [--owner <role>] [--no-measure]
#                            [--admin-install] <tenant database>
#
# Everything below runs in ONE transaction: a failed install leaves nothing
# in the database, and running it again is the recovery.
#
# 1. The legacy bundle (dev/sql/compilasql.php, the same 268 entries as
#    dev/sql/install.sh) is loaded with search_path = scheduler: its
#    unqualified objects land in schema "scheduler" instead of "public"
#    (where Drupal's tables are); its schema-qualified objects keep their
#    legacy schemas.
# 2. workspace.sql: the tenant's job group role, control schema, per-job
#    cancellation view, workspace DDL generator, closure and lifecycle
#    functions, privileges.
# 3. measure.sql: run measurement (test code; --no-measure leaves it out, as
#    the tenant provisioning does).
# 4. With --worker: worker.sql, the grants of the site's engine worker (an
#    existing login role): the workspace provisioning functions and the soft
#    cancellation of its own jobs (#95).
#
# --owner: the role that owns every engine object and the SECURITY DEFINER
# provisioning functions (SET ROLE before anything is created). It needs
# CREATE on the database, CONNECT and TEMPORARY on it WITH GRANT OPTION and
# CREATEROLE, and nothing else: no superuser (#96, see
# docs/modernization/tenant-provisioning.md). Without --owner the connecting
# role owns them (a superuser in the disposable harnesses).
#
# The libpq environment selects the server. By default it must be a verified
# disposable cluster (dev/sql/tools/with-ephemeral-pg.sh). --admin-install is
# the deliberate opt-in for a server the operator chose (the tenant
# provisioning, modern/scripts/ancora-site.sh) and also requires
# AGOH_ADMIN_INSTALL_CONFIRM=<database> (dev/sql/tools/disposable-guard.sh).
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
repo=$(cd "$here/../.." && pwd)
. "$repo/dev/sql/tools/disposable-guard.sh"
worker="" owner="" measure=1 admin=0
while [ $# -gt 1 ]; do
  case "$1" in
    --worker) worker=${2:?--worker needs a role}; shift 2 ;;
    --owner) owner=${2:?--owner needs a role}; shift 2 ;;
    --no-measure) measure=0; shift ;;
    --admin-install) admin=1; shift ;;
    *) break ;;
  esac
done
[ $# -eq 1 ] && [ "${1#-}" = "$1" ] || { sed -n '2,6p' "$0" | sed 's/^# \{0,1\}//' >&2; exit 2; }
db=$1
for name in "$db" "$owner" "$worker"; do
  [ -z "$name" ] || [[ $name =~ ^[a-z_][a-z0-9_]{0,62}$ ]] || { echo "invalid identifier: $name (lowercase letters, digits, _)" >&2; exit 2; }
done
if [ $admin = 1 ]; then agoh_require_admin_confirmation "$db" || exit 3; else agoh_require_disposable || exit 3; fi
tmp=$(mktemp -d "${TMPDIR:-/tmp}/agoh-ws-install.XXXXXX")
trap 'rm -rf "$tmp"' EXIT
php "$repo/dev/sql/compilasql.php" --out "$tmp/bundle.sql" >/dev/null
{
  echo '\set ON_ERROR_STOP on'
  echo 'SET client_min_messages = warning;'
  if [ -n "$owner" ]; then echo '\set owner '"'$owner'"; echo 'SET ROLE :"owner";'; fi
  echo 'CREATE SCHEMA scheduler;'
  echo 'SET search_path = scheduler;'
  echo 'SET check_function_bodies = on;'
  echo "\\i '$tmp/bundle.sql'"
  echo 'RESET search_path;'
  echo "\\i '$here/workspace.sql'"
  if [ $measure = 1 ]; then echo "\\i '$here/measure.sql'"; fi
  if [ -n "$worker" ]; then echo '\set worker '"'$worker'"; echo "\\i '$here/worker.sql'"; fi
} >"$tmp/install.sql"
psql -X -q -v ON_ERROR_STOP=1 --single-transaction -d "$db" -f "$tmp/install.sql"
