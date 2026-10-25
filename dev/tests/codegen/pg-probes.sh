#!/usr/bin/env bash
# Injection probes through the generated model against real PostgreSQL (#35).
#
#   dev/sql/tools/with-ephemeral-pg.sh dev/tests/codegen/pg-probes.sh
#
# Refuses to run unless the target is a verified disposable cluster
# (dev/sql/tools/disposable-guard.sh). It installs the schema with the data
# seed from the tracked sources, then runs dev/tests/codegen/pg-probes.php,
# which uses the generated classes and PostgresDatabaseImpl exactly as the
# application does. Synthetic values only; the cluster is removed by the
# launcher. Exit status 0 only if every check passes.
set -uo pipefail

repo=$(cd "$(dirname "$0")/../../.." && pwd)
. "$repo/dev/sql/tools/disposable-guard.sh"
agoh_require_disposable || exit 3

PHP=${PHP:-php}
db=agoh_codegen
work=$(mktemp -d "${TMPDIR:-/tmp}/agoh-codegen.XXXXXX")
trap 'rm -rf "$work"' EXIT

if ! "$repo/dev/sql/install.sh" --with-data "$db" >"$work/install.log" 2>&1; then
  cat "$work/install.log"; echo "FAIL  installation"; exit 1
fi
pg_log="$PGHOST/server.log"   # written by with-ephemeral-pg.sh
start=$(wc -l < "$pg_log")

AGOH_PROBE_DB=$db "$PHP" "$repo/dev/tests/codegen/pg-probes.php"
status=$?

# the probes provoke rejected values in PHP only; PostgreSQL must see no
# syntax error (which would mean SQL text was built from a value)
if tail -n +"$((start + 1))" "$pg_log" | grep -E 'ERROR: +syntax error' >"$work/syntax.log"; then
  echo "FAIL  PostgreSQL logged syntax errors:"; cat "$work/syntax.log"; status=1
else
  echo "PASS  no SQL syntax error in the PostgreSQL log"
fi
exit $status
