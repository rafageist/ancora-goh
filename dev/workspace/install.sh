#!/usr/bin/env bash
# Installs the unchanged legacy engine and the job workspace layer into an
# existing tenant database (issue #93). Prototype, disposable clusters only.
#
#   dev/workspace/install.sh [--worker <login role>] <tenant database>
#
# 1. The legacy bundle (dev/sql/compilasql.php, the same 268 entries as
#    dev/sql/install.sh) is loaded in one transaction with
#    search_path = scheduler: its unqualified objects land in schema
#    "scheduler" instead of "public" (where Drupal's tables are); its
#    schema-qualified objects keep their legacy schemas.
# 2. workspace.sql: control schema, per-job cancellation view, workspace DDL
#    generator, closure and lifecycle functions, privileges.
# 3. measure.sql: run measurement (test code).
# 4. With --worker: worker.sql, the grants of the site's engine worker (an
#    existing login role): CONNECT, the workspace provisioning functions and
#    the soft cancellation of its own jobs (#95).
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
repo=$(cd "$here/../.." && pwd)
. "$repo/dev/sql/tools/disposable-guard.sh"
agoh_require_disposable || exit 3
worker=""
if [ "${1:-}" = --worker ]; then worker=${2:?--worker needs a role}; shift 2; fi
db=${1:?usage: install.sh [--worker <login role>] <tenant database>}
bundle=$(mktemp "${TMPDIR:-/tmp}/agoh-ws-bundle.XXXXXX.sql")
trap 'rm -f "$bundle"' EXIT
php "$repo/dev/sql/compilasql.php" --out "$bundle" >/dev/null
psql -X -q -v ON_ERROR_STOP=1 -d "$db" -c 'CREATE SCHEMA scheduler'
PGOPTIONS="-c search_path=scheduler -c check_function_bodies=on -c client_min_messages=warning" \
  psql -X -q -v ON_ERROR_STOP=1 --single-transaction -d "$db" -f "$bundle"
PGOPTIONS="-c client_min_messages=warning" psql -X -q -v ON_ERROR_STOP=1 --single-transaction -d "$db" -f "$here/workspace.sql"
PGOPTIONS="-c client_min_messages=warning" psql -X -q -v ON_ERROR_STOP=1 --single-transaction -d "$db" -f "$here/measure.sql"
if [ -n "$worker" ]; then
  PGOPTIONS="-c client_min_messages=warning" psql -X -q -v ON_ERROR_STOP=1 -v worker="$worker" -d "$db" -f "$here/worker.sql"
fi
