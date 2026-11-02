#!/usr/bin/env bash
# The complete academic planning journey, as a reproducible functional
# demonstration (#105, functional completion package item 4), on a
# DISPOSABLE site:
#
#   dev/sql/tools/with-ephemeral-pg.sh modern/scripts/planning-journey.sh [playwright args...]
#
# Requires modern/vendor (composer install), jq, and dev/browser/node_modules
# (npm ci) with a Playwright Chromium. It is a functional application test,
# not a benchmark: synthetic datasets of 22 activities in two generation
# scopes (plan J), 1 (plan K) and 13 in two scopes (plan P, the positive
# path) (scripts/lib/planning-journey/fixture.php), small enough for the
# default 120 s run limit. Nothing leaves the disposable cluster.
#
# One workspace-mode site database ("ancora_ws") holds Drupal and the
# unchanged engine; the real worker (drush ancora-engine:work) runs beside
# the PHP server. The account "planner" (view and edit academic data,
# request runs) then goes through the journey in a real browser
# (dev/browser/tests/planning-journey.spec.js), one Playwright test per step:
#   1. create the plan, its period and a break;
#   2. configure catalogues;
#   3. groups and activity templates (in place from the breakdown list with
#      #153);
#   4. teachers, rooms, other resources, priorities, a restriction and a
#      daily load limit;
#   5. request generation of both scopes through the existing worker
#      (prefilled from the plan page with #154);
#   6. the runs and the stored placements, checked on the server;
#   7. the timetable perspectives (workspace and explorer);
#   8. move one activity and undo it (in place with #148, otherwise the
#      move page), checked on the server;
#   9. reload, back and forward, with the context kept.
#  10. the positive path: a second plan (P) whose two scopes the unchanged
#      engine completes (every run succeeded, every demand placed, no
#      collision, no overflow, one other-resource placement), requested
#      through the UI and checked on the server;
#  11. UI coverage: the planner creates and edits, through the Drupal UI,
#      every record type that the fixture enters by the Entity API.
# Plan J's run outcomes (steps 5-6) are a characterization of historical
# behaviour (CS/Y1 with one overflowing lab, labelled "Final"; MA/Y2 failed
# with 2202E), not a working timetable; step 10 is the complete outcome.
# The parts of the dataset that a step does not enter through the UI come
# from the fixture script, called by the step itself, and each step records
# which route it used (UI page, admin form or fixture).
#
# Feature detection: open PRs that the journey uses where present. A step,
# or the part of a step, that needs one of them is reported as "skipped:
# needs #N" when the code is not there; the step then uses the route that
# exists on master. Detected from the code:
#   #148 (S1b, move in place):       ancora_scheduling WorkspaceVarySubscriber
#   #153 (slice B, templates in place): AcademicEntityForm's ?breakdown=
#   #154 (slice A, generate from the context): ancora_scheduling js/job-refresh.js
#
# Results go to ${ANCORA_JOURNEY_OUT:-dev/browser/journey-results}:
# journey.json (per step: outcome, route, page loads, notes),
# inspect-after-generation.json, inspect-after-undo.json and
# inspect-positive.json (the server's view), report.json
# (Playwright) and screenshots/. Playwright writes into a short temporary
# directory first (long paths broke trace files before), then the results
# are copied.
set -euo pipefail

project=$(cd "$(dirname "$0")/.." && pwd -P)
repo=$(dirname "$project")
# shellcheck source=../../dev/sql/tools/disposable-guard.sh
. "$repo/dev/sql/tools/disposable-guard.sh"
agoh_require_disposable || exit 3
[ -x "$project/vendor/bin/drush" ] || { echo "run composer install in modern/ first" >&2; exit 2; }
command -v jq >/dev/null || { echo "jq is required" >&2; exit 2; }
[ -x "$repo/dev/browser/node_modules/.bin/playwright" ] || { echo "run npm ci in dev/browser first" >&2; exit 2; }

started=$SECONDS
work=$(mktemp -d "${TMPDIR:-/tmp}/apj.XXXXXX")
bg=()
cleanup() {
  for p in "${bg[@]}"; do kill "$p" 2>/dev/null || true; wait "$p" 2>/dev/null || true; done
  rm -rf "$work"
  chmod u+w "$project/web/sites/default" "$project/web/sites/default/settings.php" 2>/dev/null || true
}
trap cleanup EXIT
mkdir -p "$work/secrets" "$work/private" "$work/tmp" "$work/o"
chmod 700 "$work/secrets"
random_hex() { od -An -N"$1" -tx1 /dev/urandom | tr -d ' \n'; }
(umask 077
  for f in db-password hash-salt worker-password planner; do random_hex 16 >"$work/secrets/$f"; done)

modules=$project/web/modules/custom
has148=false has153=false has154=false
[ -f "$modules/ancora_scheduling/src/Workspace/WorkspaceVarySubscriber.php" ] && has148=true
grep -q "'breakdown'" "$modules/ancora_academic/src/Form/AcademicEntityForm.php" && has153=true
[ -f "$modules/ancora_scheduling/js/job-refresh.js" ] && has154=true
features=$(jq -cn --argjson a "$has148" --argjson b "$has153" --argjson c "$has154" '{"148": $a, "153": $b, "154": $c}')
echo "==> code under test: $(git -C "$repo" rev-parse --short HEAD 2>/dev/null || echo unknown); features $features"

psql -X -q -v ON_ERROR_STOP=1 -d postgres <<SQL
\set db_password \`cat "$work/secrets/db-password"\`
\set worker_password \`cat "$work/secrets/worker-password"\`
CREATE ROLE ancora LOGIN PASSWORD :'db_password';
CREATE ROLE ancora_ws_worker LOGIN PASSWORD :'worker_password';
CREATE DATABASE ancora_ws OWNER ancora ENCODING 'UTF8' TEMPLATE template0;
REVOKE CONNECT, TEMPORARY ON DATABASE ancora_ws FROM PUBLIC;
GRANT CONNECT, TEMPORARY ON DATABASE ancora_ws TO ancora;
\c ancora_ws
CREATE EXTENSION IF NOT EXISTS pg_trgm;
SQL

export ANCORA_ENVIRONMENT=production
export ANCORA_DB_HOST="$PGHOST" ANCORA_DB_PORT="$PGPORT" ANCORA_DB_NAME=ancora_ws ANCORA_DB_USER=ancora
export ANCORA_DB_PASSWORD_FILE="$work/secrets/db-password" ANCORA_HASH_SALT_FILE="$work/secrets/hash-salt"
export ANCORA_TRUSTED_HOSTS=127.0.0.1,localhost
export ANCORA_PRIVATE_PATH="$work/private" ANCORA_TMP_PATH="$work/tmp"
export ANCORA_ENGINE_MODE=workspace
export ANCORA_ENGINE_DB_HOST="$PGHOST" ANCORA_ENGINE_DB_PORT="$PGPORT"
export ANCORA_ENGINE_DB_NAME=ancora_ws ANCORA_ENGINE_DB_USER=ancora_ws_worker
export ANCORA_ENGINE_DB_PASSWORD_FILE="$work/secrets/worker-password"

cd "$project"
drush() { vendor/bin/drush --yes "$@"; }
echo "==> a disposable workspace-mode site"
drush site:install --existing-config --site-name=Áncora --site-mail=noreply@example.invalid \
  --account-name=admin --account-mail=admin@example.invalid --account-pass="$(random_hex 16)" >"$work/install.log" 2>&1 \
  || { cat "$work/install.log"; exit 1; }
"$repo/dev/workspace/install.sh" --worker ancora_ws_worker ancora_ws >"$work/engine.log" 2>&1 || { cat "$work/engine.log"; exit 1; }
{
  drush role:create planner Planner
  drush role:perm:add planner "view academic data,edit academic data,view ancora engine jobs,request ancora engine jobs"
  drush user:create planner --mail=planner@example.invalid --password="$(cat "$work/secrets/planner")"
  drush user:role:add planner planner
} >"$work/users.log" 2>&1 || { cat "$work/users.log"; exit 1; }

# The steps call the fixture script and read the server through this
# wrapper (the same site environment, exported above).
cat >"$work/drush" <<EOF
#!/bin/sh
cd "$project" && exec vendor/bin/drush "\$@"
EOF
chmod 700 "$work/drush"

port=$(php -r '$s = stream_socket_server("tcp://127.0.0.1:0"); echo explode(":", stream_socket_get_name($s, FALSE))[1];')
(cd web && PHP_CLI_SERVER_WORKERS=4 exec php -S "127.0.0.1:$port" .ht.router.php) >"$work/server.log" 2>&1 &
bg+=("$!")
idle=${ANCORA_JOURNEY_IDLE_SLEEP:-5}
vendor/bin/drush ancora-engine:work --idle-sleep="$idle" --max-runtime=3000 >"$work/worker.log" 2>&1 &
bg+=("$!")
up=false
for _ in $(seq 1 50); do curl -s -o /dev/null "http://127.0.0.1:$port/user/login" && { up=true; break; }; sleep 0.2; done
[ "$up" = true ] || { echo "the PHP server did not start"; tail -n 20 "$work/server.log"; exit 1; }
jq -n '{}' >"$work/ids.json"

echo "==> the journey in Playwright ($((SECONDS - started)) s so far)"
rc=0
(
  cd "$repo/dev/browser"
  BASE_URL="http://127.0.0.1:$port" \
  ANCORA_IDS="$work/ids.json" \
  ANCORA_SECRETS="$work/secrets" \
  ANCORA_BROWSER_OUT="$work/o" \
  ANCORA_JOURNEY=1 \
  ANCORA_JOURNEY_FEATURES="$features" \
  ANCORA_DRUSH="$work/drush" \
  ANCORA_JOURNEY_FIXTURE="$project/scripts/lib/planning-journey/fixture.php" \
  ANCORA_ENQUEUE="$project/scripts/lib/workspace-journey/enqueue.php" \
  PLAYWRIGHT_JSON_OUTPUT_NAME="$work/o/report.json" \
    node_modules/.bin/playwright test tests/planning-journey.spec.js --reporter=list,json "$@"
) || rc=$?

echo
echo "==> step results (Playwright)"
if [ -s "$work/o/report.json" ]; then
  jq -r '.. | objects | select(has("specs")) | .specs[] | . as $s | .tests[] |
    [($s.title), (.results[-1].status // "not run"),
     ((.annotations // []) | map(select(.type == "skip") | .description) | join("; "))] | @tsv' "$work/o/report.json" \
    | awk -F'\t' '{ printf "  %-60s %-8s %s\n", $1, $2, $3 }'
fi
if [ -s "$work/o/journey.json" ]; then
  echo
  echo "==> routes and page loads (journey.json)"
  jq -r '.steps[] | "  \(.step): \(.outcome); route \(.route // "-"); page loads \(.page_loads // "-")\(if .notes then "; " + (.notes | join("; ")) else "" end)"' "$work/o/journey.json"
fi
echo
echo "==> $((SECONDS - started)) s in all; features $features"

out=${ANCORA_JOURNEY_OUT:-$repo/dev/browser/journey-results}
mkdir -p "$out"
cp -r "$work/o/." "$out/" 2>/dev/null || true
echo "    results in $out"
if [ "$rc" -ne 0 ]; then
  echo "--- server log: 4xx and 5xx responses (tail)"
  grep -E '\[[45][0-9]{2}\]:' "$work/server.log" | tail -n 20 || true
  echo "--- worker log (tail)"; tail -n 20 "$work/worker.log" || true
fi
exit "$rc"
