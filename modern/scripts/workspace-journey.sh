#!/usr/bin/env bash
# The workspace mode of the engine worker, end to end (issue #95), against a
# disposable PostgreSQL cluster:
#
#   dev/sql/tools/with-ephemeral-pg.sh modern/scripts/workspace-journey.sh
#
# Requires modern/vendor (composer install) and jq.
#
# One Drupal site database ("ancora_ws") holds Drupal, the unchanged engine
# (dev/workspace/install.sh --worker) and, during a run, the job's workspace.
# There is NO engine database. The real worker (drush ancora-engine:work) runs
# with ANCORA_ENGINE_MODE=workspace and the site's worker role. The E2E
# organization is entered through the Entity API (drush
# ancora-academic:load-fixture). Every placement checked below is read back
# from the placement ENTITIES (scripts/lib/workspace-journey/placements.php).
#
# Checks:
#  1. a request gives 8/8 and the E2E digest from the entities; nothing of
#     the run is left (no workspace schema, no job role);
#  2. a second request reads the stored placements and keeps them: it
#     publishes nothing and the entities are unchanged (D7: no delete);
#  3. a run cancelled mid-flight keeps what it placed (a draft), and the next
#     request continues from it exactly as legacy does (legacy oracle: the
#     same cancellation in a full legacy database, then a second run);
#  4. a hard cancellation and a timeout keep nothing;
#  5. a failure halfway through the publication keeps nothing (test-only
#     trigger on the placement table), and the retry publishes all;
#  6. data changed between the snapshot and the publication: nothing is
#     published (stale input);
#  7. the worker killed during a run: the job is recovered, its workspace
#     dropped, and the next attempt publishes everything once;
#  8. the same job processed twice (requeued after it succeeded): nothing is
#     duplicated;
#  9. one running job per site: a second worker claims nothing meanwhile;
# 10. the worker role of site A cannot connect to site B's database, holds no
#     privilege on Drupal's tables (it cannot write placements itself), and
#     its job roles are per database.
# Test-only objects (a slow-placement trigger on the engine's template table
# "scheduler.actividad", which every workspace copies, and a failing trigger
# on the placement table) are created by the superuser in this disposable
# database only.
set -euo pipefail

project=$(cd "$(dirname "$0")/.." && pwd -P)
repo=$(dirname "$project")
# shellcheck source=../../dev/sql/tools/disposable-guard.sh
. "$repo/dev/sql/tools/disposable-guard.sh"
agoh_require_disposable || exit 3
command -v jq >/dev/null || { echo "jq is required" >&2; exit 2; }
[ -x "$project/vendor/bin/drush" ] || { echo "run composer install in modern/ first" >&2; exit 2; }

started=$SECONDS
step() { printf '\n==> [%3ss] %s\n' "$((SECONDS - started))" "$*"; }
work=$(mktemp -d "${TMPDIR:-/tmp}/ancora-ws-journey.XXXXXX")
bg=()
cleanup() {
  for p in "${bg[@]}"; do kill -9 "$p" 2>/dev/null || true; done
  rm -rf "$work"
  chmod u+w "$project/web/sites/default" "$project/web/sites/default/settings.php" 2>/dev/null || true
}
trap cleanup EXIT
mkdir -p "$work/secrets" "$work/private" "$work/tmp"
chmod 700 "$work/secrets"
random_hex() { od -An -N"$1" -tx1 /dev/urandom | tr -d ' \n'; }
(umask 077
  for f in db-password hash-salt worker-a-password worker-b-password; do random_hex 24 >"$work/secrets/$f"; done)

pass=0 fail=0
ok() { pass=$((pass + 1)); echo "PASS  $1"; }
bad() { fail=$((fail + 1)); echo "FAIL  $1"; }
check() { local d=$1; shift; if "$@"; then ok "$d"; else bad "$d"; fi; }
eq() { [ "$1" = "$2" ] || { echo "      expected '$2', got '$1'"; return 1; }; }
site() { psql -X -At -v ON_ERROR_STOP=1 -d ancora_ws -c "$1"; }
expected=$(grep -v '^#' "$repo/dev/e2e/expected-placements.md5" | head -1)

step "PostgreSQL $(psql -X -At -d postgres -c 'SHOW server_version'): one site database per tenant, no engine database"
psql -X -q -v ON_ERROR_STOP=1 -d postgres <<SQL
\set db_password \`cat "$work/secrets/db-password"\`
\set a_password \`cat "$work/secrets/worker-a-password"\`
\set b_password \`cat "$work/secrets/worker-b-password"\`
CREATE ROLE ancora LOGIN PASSWORD :'db_password';
CREATE ROLE ancora_ws_worker LOGIN PASSWORD :'a_password';
CREATE ROLE tenant_b_worker LOGIN PASSWORD :'b_password';
CREATE DATABASE ancora_ws OWNER ancora ENCODING 'UTF8' TEMPLATE template0;
CREATE DATABASE tenant_b OWNER ancora ENCODING 'UTF8' TEMPLATE template0;
REVOKE CONNECT, TEMPORARY ON DATABASE ancora_ws FROM PUBLIC;
REVOKE CONNECT, TEMPORARY ON DATABASE tenant_b FROM PUBLIC;
GRANT CONNECT, TEMPORARY ON DATABASE ancora_ws, tenant_b TO ancora;
\c ancora_ws
CREATE EXTENSION IF NOT EXISTS pg_trgm;
SQL

export ANCORA_ENVIRONMENT=production
export ANCORA_DB_HOST="$PGHOST" ANCORA_DB_PORT="$PGPORT" ANCORA_DB_NAME=ancora_ws ANCORA_DB_USER=ancora
export ANCORA_DB_PASSWORD_FILE="$work/secrets/db-password"
export ANCORA_HASH_SALT_FILE="$work/secrets/hash-salt"
export ANCORA_TRUSTED_HOSTS=127.0.0.1,localhost
export ANCORA_PRIVATE_PATH="$work/private" ANCORA_TMP_PATH="$work/tmp"
# Workspace mode: the engine "database" is the site's own, the role the
# site's engine worker.
export ANCORA_ENGINE_MODE=workspace
export ANCORA_ENGINE_DB_HOST="$PGHOST" ANCORA_ENGINE_DB_PORT="$PGPORT"
export ANCORA_ENGINE_DB_NAME=ancora_ws ANCORA_ENGINE_DB_USER=ancora_ws_worker
export ANCORA_ENGINE_DB_PASSWORD_FILE="$work/secrets/worker-a-password"
export ANCORA_ENGINE_HEARTBEAT_INTERVAL=1 ANCORA_ENGINE_STALE_AFTER=3 ANCORA_ENGINE_CANCEL_GRACE=30 ANCORA_ENGINE_MAX_ATTEMPTS=5

cd "$project"
drush() { vendor/bin/drush --yes "$@"; }
job() { drush ancora-engine:job "$1"; }
field() { job "$1" | jq -r "$2"; }
lib=$project/scripts/lib/workspace-journey
stored() { drush php:script "$lib/placements.php" 2>/dev/null; }
count() { stored | jq -r .count; }
digest() { stored | jq -j .e2e | md5sum | cut -d' ' -f1; }
enqueue() { drush php:script "$lib/enqueue.php" -- planner P PER E N ${1:+"$1"} 2>"$work/enqueue.err" | tail -1 || { cat "$work/enqueue.err" >&2; return 1; }; }
clear_placements() { drush php:eval '$s = \Drupal::entityTypeManager()->getStorage("ancora_placement"); $s->delete($s->loadMultiple());' >/dev/null; }

step "drush site:install --existing-config, then the engine into the same database"
drush site:install --existing-config --site-name=Áncora --site-mail=noreply@example.invalid \
  --account-name=admin --account-mail=admin@example.invalid --account-pass="$(random_hex 16)" >"$work/install.log" 2>&1 \
  || { cat "$work/install.log"; exit 1; }
"$repo/dev/workspace/install.sh" --worker ancora_ws_worker ancora_ws >"$work/engine-install.log" 2>&1 || { cat "$work/engine-install.log"; exit 1; }
"$repo/dev/workspace/install.sh" --worker tenant_b_worker tenant_b >"$work/engine-install-b.log" 2>&1 || { cat "$work/engine-install-b.log"; exit 1; }
check "no engine database: the cluster holds only the two tenant databases" eq \
  "$(psql -X -At -d postgres -c "SELECT string_agg(datname, ',' ORDER BY datname) FROM pg_database WHERE datname NOT IN ('postgres', 'template0', 'template1')")" "ancora_ws,tenant_b"
drush role:create planner Planner >"$work/role.log" 2>&1 && drush role:perm:add planner "view academic data,edit academic data,view ancora engine jobs,request ancora engine jobs" >>"$work/role.log" 2>&1 || { cat "$work/role.log"; exit 1; }
drush user:create planner --mail=planner@example.invalid --password="$(random_hex 16)" >"$work/user.log" 2>&1 || { cat "$work/user.log"; exit 1; }
drush user:role:add planner planner >>"$work/role.log" 2>&1 || { cat "$work/role.log"; exit 1; }
drush ancora-academic:load-fixture --owner=planner >"$work/fixture.log" 2>&1 || { cat "$work/fixture.log"; exit 1; }

# Test-only objects (superuser, this disposable database only).
site "CREATE SCHEMA tst_ws;
  CREATE SEQUENCE tst_ws.started; CREATE TABLE tst_ws.delay (seconds float8 NOT NULL); INSERT INTO tst_ws.delay VALUES (0);
  CREATE FUNCTION tst_ws.slow() RETURNS trigger LANGUAGE plpgsql AS \$\$
  BEGIN PERFORM nextval('tst_ws.started'); PERFORM pg_sleep((SELECT seconds FROM tst_ws.delay)); RETURN NULL; END \$\$;
  GRANT USAGE ON SCHEMA tst_ws TO agoh_engine; GRANT USAGE, SELECT, UPDATE ON SEQUENCE tst_ws.started TO agoh_engine;
  GRANT SELECT ON tst_ws.delay TO agoh_engine;
  CREATE TRIGGER tst_ws_slow AFTER INSERT ON scheduler.actividad FOR EACH ROW EXECUTE FUNCTION tst_ws.slow();" >/dev/null
delay() { site "UPDATE tst_ws.delay SET seconds = $1" >/dev/null; }
started_count() { site "SELECT CASE WHEN is_called THEN last_value ELSE 0 END FROM tst_ws.started"; }
wait_started() { local target=$(( $1 + mark )) deadline=$((SECONDS + 60)); while [ "$(started_count)" -lt "$target" ]; do [ $SECONDS -lt $deadline ] || return 1; sleep 0.05; done; }
workspaces() { site "SELECT count(*) FROM pg_namespace WHERE nspname LIKE 'scheduler_job_%'"; }
job_roles() { site "SELECT count(*) FROM pg_roles WHERE rolname LIKE 'agoh_ancora_ws_j%'"; }

step "1. a request: 8/8, the E2E digest read back from the entities"
id1=$(enqueue)
drush ancora-engine:work --once >"$work/w1.log" 2>&1
j=$(job "$id1")
echo "      job $id1: $(jq -c "{state, error, input: .result.input | {rows, placements_loaded, input_rows, closure_rows, pruned}}" <<<"$j")"
check "job $id1 succeeded in workspace mode" eq "$(jq -r '.state + "/" + .result.mode' <<<"$j")" "succeeded/workspace"
check "metrics demands=8 placed=8 unplaced=0 collisions=0 overflow=0" eq "$(jq -r '.result | "\(.demands)/\(.placed)/\(.unplaced)/\(.collisions)/\(.overflow)"' <<<"$j")" "8/8/0/0/0"
check "nothing was deleted before the run (no D7)" eq "$(jq -r .result.replaced <<<"$j")" 0
check "8 placements published" eq "$(jq -r '.result.input.publication | "\(.published)/\(.already_published)"' <<<"$j")" "8/0"
check "8 placement entities" eq "$(count)" 8
check "E2E digest from the entities = $expected" eq "$(digest)" "$expected"
check "the job's digest (workspace) agrees" eq "$(jq -r .result.digest <<<"$j")" "$expected"
check "the input was one REPEATABLE READ snapshot" eq "$(jq -r .result.input.snapshot.isolation <<<"$j")" "repeatable read"
check "nothing of the run is left: no workspace schema, no job role" eq "$(workspaces)/$(job_roles)" "0/0"
check "the timetable read model shows the 8 placements as final" eq \
  "$(drush php:eval '$p = \Drupal::entityTypeManager()->getStorage("ancora_plan")->loadByProperties(["code" => "P"]); $p = reset($p); $per = \Drupal::entityTypeManager()->getStorage("ancora_period")->loadByProperties(["code" => "PER"]); $t = \Drupal::service("ancora_scheduling.timetable")->read($p, reset($per)); echo count($t["scopes"][0]["placements"]) . "/" . ($t["scopes"][0]["final"] ? "final" : "draft");')" "8/final"
before=$(stored | jq -c '[.rows[] | .engine_id + " " + .line]')

step "2. a second request continues: it keeps the stored placements (D7)"
id2=$(enqueue)
drush ancora-engine:work --once >"$work/w2.log" 2>&1
j=$(job "$id2")
check "job $id2 succeeded, loading the 8 stored placements and publishing none" eq \
  "$(jq -r '.state + "/" + (.result.input.placements_loaded | tostring) + "/" + (.result.input.publication.published | tostring)' <<<"$j")" "succeeded/8/0"
check "the stored placements are unchanged (engine ids, cells)" eq "$(stored | jq -c '[.rows[] | .engine_id + " " + .line]')" "$before"

step "3. a run cancelled mid-flight keeps what it placed; the next request continues as legacy"
clear_placements
delay 0.5; mark=$(started_count)
id3=$(enqueue)
vendor/bin/drush ancora-engine:work --once >"$work/w3.log" 2>&1 & wpid=$!; bg+=("$wpid")
wait_started 3 || bad "the run never reached 3 placements"
drush ancora-engine:cancel "$id3" >/dev/null
wait "$wpid" || true
delay 0
j=$(job "$id3")
n=$(count)
check "job $id3 cancelled, a draft with the placements made so far ($n)" eq "$(jq -r '.state + "/" + .result.kept' <<<"$j")" "cancelled/partial"
check "its $n placements were published (3..7)" bash -c "[ '$n' -ge 3 ] && [ '$n' -le 7 ] && [ '$(jq -r .result.input.publication.published <<<"$j")' = '$n' ]"
check "the read model shows them as a draft" eq \
  "$(drush php:eval '$p = \Drupal::entityTypeManager()->getStorage("ancora_plan")->loadByProperties(["code" => "P"]); $p = reset($p); $per = \Drupal::entityTypeManager()->getStorage("ancora_period")->loadByProperties(["code" => "PER"]); $t = \Drupal::service("ancora_scheduling.timetable")->read($p, reset($per)); echo count($t["scopes"][0]["placements"]) . "/" . ($t["scopes"][0]["final"] ? "final" : "draft");')" "$n/draft"
partial=$(stored | jq -c '[.rows[] | .engine_id + " " + .line]')
id4=$(enqueue)
drush ancora-engine:work --once >"$work/w4.log" 2>&1
j=$(job "$id4")
check "job $id4 succeeded and published the other $((8 - n))" eq "$(jq -r '.state + "/" + (.result.input.publication.published | tostring)' <<<"$j")" "succeeded/$((8 - n))"
check "the cancelled run's placements are kept unchanged" bash -c "[ \"\$(jq -c --argjson p '$partial' '[.rows[] | .engine_id + \" \" + .line] | .[0:(\$p | length)] == \$p' <<<'$(stored)')\" = true ]"
# Legacy oracle: the same cancellation after $n placements in a full legacy
# database (test-only trigger), then a second run.
"$repo/dev/sql/install.sh" --with-data oracle >/dev/null 2>&1
for f in dev/e2e/fixture.sql dev/e2e/fixture-http-steps.sql; do psql -X -q -v ON_ERROR_STOP=1 -d oracle -f "$repo/$f" >/dev/null; done
oracle_list=$(PGOPTIONS="-c client_min_messages=warning" psql -X -q -At -v ON_ERROR_STOP=1 -d oracle <<SQL
CREATE FUNCTION pg_temp.cancel_after() RETURNS trigger LANGUAGE plpgsql AS \$\$
BEGIN IF (SELECT count(*) FROM actividad) = $n THEN INSERT INTO generador.cancelar VALUES ('E', 'N', 'PER'); END IF; RETURN NULL; END \$\$;
CREATE TRIGGER t AFTER INSERT ON actividad FOR EACH ROW EXECUTE FUNCTION pg_temp.cancel_after();
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false) \g /dev/null
DROP TRIGGER t ON actividad;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false) \g /dev/null
SELECT string_agg(a.plantilla || '/' || a.grupo || '/' || p.nombre || '/' || a.lugar || '/' || a.dia || '/' || a.turno, ',' ORDER BY a.plantilla, a.grupo) FROM actividad a JOIN profesor p ON p.id = a.profesor;
SQL
)
check "the continued timetable equals the legacy oracle's (cancel after $n, then continue)" eq "$(stored | jq -r .e2e)" "$oracle_list"

step "4. a hard cancellation and a timeout keep nothing"
clear_placements
delay 0.5; mark=$(started_count)
id5=$(enqueue)
vendor/bin/drush ancora-engine:work --once >"$work/w5.log" 2>&1 & wpid=$!; bg+=("$wpid")
wait_started 2 || bad "the run never reached 2 placements"
drush ancora-engine:cancel "$id5" --hard >/dev/null
wait "$wpid" || true
check "job $id5 hard-cancelled, nothing kept or published" eq "$(field "$id5" '.state + "/" + .result.kept')/$(count)" "cancelled/none/0"
id6=$(enqueue 2)
drush ancora-engine:work --once >"$work/w6.log" 2>&1
check "job $id6 timed out, nothing published" eq "$(field "$id6" .state)/$(count)" "timed_out/0"
delay 0
check "no workspace is left after them" eq "$(workspaces)/$(job_roles)" "0/0"

step "5. a failure halfway through the publication keeps nothing"
site "CREATE FUNCTION tst_ws.fail_third() RETURNS trigger LANGUAGE plpgsql AS \$\$
  BEGIN IF (SELECT count(*) FROM public.ancora_placement) >= 2 THEN RAISE EXCEPTION 'test: publication fails at the third placement'; END IF; RETURN NEW; END \$\$;
  CREATE TRIGGER tst_ws_fail BEFORE INSERT ON public.ancora_placement FOR EACH ROW EXECUTE FUNCTION tst_ws.fail_third();" >/dev/null
id7=$(enqueue)
drush ancora-engine:work --once >"$work/w7.log" 2>&1
check "job $id7 failed: nothing published (rolled back after 2 saves)" eq "$(field "$id7" '.state + "/" + .result.input.publication.failed')/$(count)" "failed/failed/0"
site "DROP TRIGGER tst_ws_fail ON public.ancora_placement" >/dev/null
id8=$(enqueue)
drush ancora-engine:work --once >"$work/w8.log" 2>&1
check "the retry (job $id8) publishes all 8, digest $expected" eq "$(field "$id8" .state)/$(count)/$(digest)" "succeeded/8/$expected"

step "6. data changed between the snapshot and the publication: nothing is published"
clear_placements
delay 0.3; mark=$(started_count)
id9=$(enqueue)
vendor/bin/drush ancora-engine:work --once >"$work/w9.log" 2>&1 & wpid=$!; bg+=("$wpid")
wait_started 1 || bad "the run never started"
drush php:eval '$t = \Drupal::entityTypeManager()->getStorage("ancora_room")->loadByProperties(["code" => "L2"]); $t = reset($t); $t->set("capacity", 51)->save();' >/dev/null
wait "$wpid" || true
delay 0
check "job $id9 failed as stale, nothing published" eq "$(field "$id9" '.state + "/" + .result.input.publication.failed')/$(count)" "failed/stale/0"

step "7. the worker killed during a run: recovery, then one publication"
delay 0.5; mark=$(started_count)
id10=$(enqueue)
vendor/bin/drush ancora-engine:work --once >"$work/w10.log" 2>&1 & wpid=$!; bg+=("$wpid")
wait_started 2 || bad "the run never reached 2 placements"
pkill -9 -f "ancora-engine:work" || true
wait "$wpid" 2>/dev/null || true
delay 0
check "the killed run published nothing" eq "$(count)" 0
sleep 4
drush ancora-engine:work --once >"$work/w10b.log" 2>&1
j=$(job "$id10")
check "job $id10 was recovered and its next attempt published 8 once" eq "$(jq -r '.state + "/" + (.attempts | tostring)' <<<"$j")/$(count)/$(digest)" "succeeded/2/8/$expected"
check "the lost attempt's workspace and role were dropped" eq "$(workspaces)/$(job_roles)" "0/0"

step "8. the same job processed twice: nothing duplicated"
before=$(stored | jq -c '[.rows[] | .engine_id]')
site "UPDATE ancora_engine_job SET state = 'running', heartbeat = 1, finished = NULL WHERE id = $id10" >/dev/null
drush ancora-engine:work --once >"$work/w11.log" 2>&1
j=$(job "$id10")
check "job $id10 ran a third time (requeued as lost) and published nothing new" eq "$(jq -r '.state + "/" + (.attempts | tostring) + "/" + (.result.input.publication.published | tostring)' <<<"$j")" "succeeded/3/0"
check "the placements are the same 8 records" eq "$(stored | jq -c '[.rows[] | .engine_id]')" "$before"

step "9. one running job per site"
delay 0.5; mark=$(started_count)
id12=$(enqueue); id13=$(enqueue)
vendor/bin/drush ancora-engine:work --once >"$work/w12.log" 2>&1 & wpid=$!; bg+=("$wpid")
wait_started 0 || true
sleep 1
drush ancora-engine:work --once >"$work/w13.log" 2>&1
check "while job $id12 runs, a second worker claims nothing (job $id13 stays queued)" eq "$(field "$id13" .state)" "queued"
wait "$wpid" || true
delay 0
drush ancora-engine:work --once >"$work/w14.log" 2>&1
check "job $id13 then runs and continues (nothing new)" eq "$(field "$id13" '.state + "/" + (.result.input.publication.published | tostring)')" "succeeded/0"

step "10. tenant and privilege boundaries"
check "site A's worker role cannot connect to site B's database" bash -c "! psql -X -At -U ancora_ws_worker -d tenant_b -c 'SELECT 1' >/dev/null 2>&1"
check "site B's worker role cannot connect to site A's database" bash -c "! psql -X -At -U tenant_b_worker -d ancora_ws -c 'SELECT 1' >/dev/null 2>&1"
check "the worker role holds no privilege on any Drupal table (it cannot write placements)" eq \
  "$(site "SELECT count(*) FROM pg_class c WHERE c.relnamespace = 'public'::regnamespace AND c.relkind = 'r' AND (has_table_privilege('ancora_ws_worker', c.oid, 'SELECT') OR has_table_privilege('ancora_ws_worker', c.oid, 'INSERT,UPDATE,DELETE,TRUNCATE'))")" 0
check "the worker role cannot read the engine templates" bash -c "! psql -X -At -U ancora_ws_worker -d ancora_ws -c 'SELECT count(*) FROM scheduler.actividad' >/dev/null 2>&1"
check "the worker role cannot create workspaces by hand (no CREATE on the database)" bash -c "! psql -X -At -U ancora_ws_worker -d ancora_ws -c 'CREATE SCHEMA x' >/dev/null 2>&1"

step "workspace journey: $pass passed, $fail failed ($((SECONDS - started)) s)"
[ "$fail" -eq 0 ]
