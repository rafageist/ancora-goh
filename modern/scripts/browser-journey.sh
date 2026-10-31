#!/usr/bin/env bash
# The planning workspace in a real browser (#141), on a DISPOSABLE site:
#
#   dev/sql/tools/with-ephemeral-pg.sh modern/scripts/browser-journey.sh [playwright args...]
#
# Requires modern/vendor (composer install), jq, and dev/browser/node_modules
# (npm ci) with a Playwright Chromium. One workspace-mode site database
# ("ancora_ws") on the disposable cluster holds Drupal and the unchanged
# engine; the real worker (drush ancora-engine:work) runs beside the PHP
# server, so the move page's destination checks run as they do in
# production (its latency is measured, not changed: the worker uses its
# default idle sleep unless ANCORA_BROWSER_IDLE_SLEEP says otherwise).
#
# Accounts: "planner" and "other" (the planner role: view and edit academic
# data, request runs) each own the synthetic workspace fixture
# (scripts/lib/browser/fixture.php); "viewer" holds only "view timetable
# viewer". "other" also owns the E2E organization of #130 (8 activities),
# placed by a real run of the worker. Then dev/browser runs Playwright
# (tests/*.spec.js) against the site. Screenshots, the measurements and the
# server's record of every destination check (checks.json) go to
# ${ANCORA_BROWSER_OUT:-dev/browser/test-results}. Synthetic data only;
# nothing leaves the disposable cluster.
set -euo pipefail

project=$(cd "$(dirname "$0")/.." && pwd -P)
repo=$(dirname "$project")
# shellcheck source=../../dev/sql/tools/disposable-guard.sh
. "$repo/dev/sql/tools/disposable-guard.sh"
agoh_require_disposable || exit 3
[ -x "$project/vendor/bin/drush" ] || { echo "run composer install in modern/ first" >&2; exit 2; }
command -v jq >/dev/null || { echo "jq is required" >&2; exit 2; }
[ -x "$repo/dev/browser/node_modules/.bin/playwright" ] || { echo "run npm ci in dev/browser first" >&2; exit 2; }

work=$(mktemp -d "${TMPDIR:-/tmp}/ancora-browser.XXXXXX")
bg=()
cleanup() {
  for p in "${bg[@]}"; do kill "$p" 2>/dev/null || true; wait "$p" 2>/dev/null || true; done
  rm -rf "$work"
  chmod u+w "$project/web/sites/default" "$project/web/sites/default/settings.php" 2>/dev/null || true
}
trap cleanup EXIT
mkdir -p "$work/secrets" "$work/private" "$work/tmp"
chmod 700 "$work/secrets"
random_hex() { od -An -N"$1" -tx1 /dev/urandom | tr -d ' \n'; }
(umask 077
  for f in db-password hash-salt worker-password planner other viewer; do random_hex 16 >"$work/secrets/$f"; done)

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
  drush role:create timetable_viewer "Timetable viewer"
  drush role:perm:add timetable_viewer "view timetable viewer"
  for u in planner other viewer; do
    drush user:create "$u" --mail="$u@example.invalid" --password="$(cat "$work/secrets/$u")"
  done
  drush user:role:add planner planner
  drush user:role:add planner other
  drush user:role:add timetable_viewer viewer
} >"$work/users.log" 2>&1 || { cat "$work/users.log"; exit 1; }
drush php:script "$project/scripts/lib/browser/fixture.php" 2>"$work/fixture.err" | tail -1 >"$work/fixture.json" \
  || { cat "$work/fixture.err"; exit 1; }
# The E2E organization of #130 (8 activities), owned by "other": a real run
# places it, below.
drush ancora-academic:load-fixture --owner=other >"$work/e2e.log" 2>&1 || { cat "$work/e2e.log"; exit 1; }

port=$(php -r '$s = stream_socket_server("tcp://127.0.0.1:0"); echo explode(":", stream_socket_get_name($s, FALSE))[1];')
(cd web && PHP_CLI_SERVER_WORKERS=4 exec php -S "127.0.0.1:$port" .ht.router.php) >"$work/server.log" 2>&1 &
bg+=("$!")
idle=${ANCORA_BROWSER_IDLE_SLEEP:-5}
vendor/bin/drush ancora-engine:work --idle-sleep="$idle" --max-runtime=3000 >"$work/worker.log" 2>&1 &
bg+=("$!")
for _ in $(seq 1 50); do curl -s -o /dev/null "http://127.0.0.1:$port/user/login" && break; sleep 0.2; done

echo "==> the E2E organization through the real worker"
job=$(drush php:script "$project/scripts/lib/workspace-journey/enqueue.php" -- other P PER E N 2>"$work/enqueue.err" | tail -1) \
  || { cat "$work/enqueue.err"; exit 1; }
state=
for _ in $(seq 1 120); do
  state=$(drush ancora-engine:job "$job" 2>/dev/null | jq -r .state)
  case "$state" in succeeded|partial|failed|cancelled|timed_out) break ;; esac
  sleep 1
done
[ "$state" = succeeded ] || { echo "the E2E run ended as '$state'"; tail -n 20 "$work/worker.log"; exit 1; }
placements=$(drush php:eval '$u = current(\Drupal::entityTypeManager()->getStorage("user")->loadByProperties(["name" => "other"])); $p = current(\Drupal::entityTypeManager()->getStorage("ancora_plan")->loadByProperties(["code" => "P", "uid" => $u->id()])); echo json_encode(array_map("intval", array_values(\Drupal::entityQuery("ancora_placement")->accessCheck(FALSE)->condition("plan", $p->id())->sort("id")->execute())));')
jq -c --argjson p "$placements" '. + {e2e: {placements: $p}}' "$work/fixture.json" >"$work/ids.json"
echo "    fixture: $(cat "$work/ids.json")"

echo "==> Playwright"
out=${ANCORA_BROWSER_OUT:-$repo/dev/browser/test-results}
mkdir -p "$out"
rc=0
(
  cd "$repo/dev/browser"
  BASE_URL="http://127.0.0.1:$port" \
  ANCORA_IDS="$work/ids.json" \
  ANCORA_SECRETS="$work/secrets" \
  ANCORA_WORKER_IDLE_SLEEP="$idle" \
  ANCORA_BROWSER_OUT="$out" \
    node_modules/.bin/playwright test "$@"
) || rc=$?
# The server's own record of every destination check of the session
# (created and finished, whole seconds): a cross-check of the latency.
psql -X -At -d ancora_ws -c "SELECT coalesce(json_agg(json_build_object('id', id, 'state', state, 'seconds', changed - created) ORDER BY id), '[]') FROM ancora_placement_check" >"$out/checks.json" || true
if [ "$rc" -ne 0 ]; then
  echo "--- server log (tail)"; tail -n 40 "$work/server.log" || true
  echo "--- worker log (tail)"; tail -n 20 "$work/worker.log" || true
fi
exit "$rc"
