#!/usr/bin/env bash
# Integration test of the engine worker (issue #71), against a disposable
# PostgreSQL cluster:
#
#   dev/sql/tools/with-ephemeral-pg.sh modern/scripts/engine-integration.sh
#
# Requires modern/vendor (composer install) and jq.
#
# 1. A Drupal site database installed from config/sync (ancora_engine
#    enabled) and two engine databases, one per site, provisioned with
#    scripts/engine-db.sh: ancora_engine_a (role engine_a) and
#    ancora_engine_b (role engine_b). The E2E organization (dev/e2e/fixture.sql
#    and fixture-http-steps.sql) is loaded into ancora_engine_a.
# 2. The worker runs with the credentials of site A from the environment
#    (ANCORA_ENGINE_DB_*, password from a 0600 file), never from job data.
# 3. Checks: a job on the E2E scope succeeds with demands=8 placed=8
#    collisions=0 overflow=0 and the E2E digest (dev/e2e/expected-placements.md5),
#    equal to tst.metrics; a slow run cancelled mid-flight ends cancelled with
#    partial placements kept (a draft, not final); its retry replaces them and
#    succeeds; one running job per site; a hard cancellation and two timeouts
#    keep nothing; stale-heartbeat recovery requeues once, then fails; option
#    validation; an unknown scope fails; the role of site B cannot connect to
#    the engine database of site A, and a worker misconfigured that way fails
#    without leaking its password.
# A test-only trigger (pg_sleep per placement, counted in a sequence) makes
# runs slow and observable; it is created by the superuser in the test
# database only, like dev/sql/tests/concurrency/cancel_midflight.sh.
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
work=$(mktemp -d "${TMPDIR:-/tmp}/ancora-engine-it.XXXXXX")
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
  for f in db-password hash-salt admin-password engine-a-password engine-b-password; do random_hex 24 >"$work/secrets/$f"; done)

pass=0 fail=0
ok() { pass=$((pass + 1)); echo "PASS  $1"; }
bad() { fail=$((fail + 1)); echo "FAIL  $1"; }
check() { local d=$1; shift; if "$@"; then ok "$d"; else bad "$d"; fi; }
eq() { [ "$1" = "$2" ] || { echo "      expected '$2', got '$1'"; return 1; }; }
sqla() { psql -X -At -v ON_ERROR_STOP=1 -d ancora_engine_a -c "$1"; }

step "PostgreSQL $(psql -X -At -d postgres -c 'SHOW server_version'): Drupal site database"
psql -X -q -v ON_ERROR_STOP=1 -d postgres <<SQL
\set db_password \`cat "$work/secrets/db-password"\`
CREATE ROLE ancora LOGIN PASSWORD :'db_password';
CREATE DATABASE ancora OWNER ancora ENCODING 'UTF8' TEMPLATE template0;
\c ancora
CREATE EXTENSION IF NOT EXISTS pg_trgm;
SQL

step "engine databases of two sites (scripts/engine-db.sh)"
"$project/scripts/engine-db.sh" create --with-data --password-file "$work/secrets/engine-a-password" ancora_engine_a engine_a
"$project/scripts/engine-db.sh" create --with-data --password-file "$work/secrets/engine-b-password" ancora_engine_b engine_b
for f in dev/e2e/fixture.sql dev/e2e/fixture-http-steps.sql dev/sql/tests/fixtures/helpers.sql; do
  psql -X -q -v ON_ERROR_STOP=1 -d ancora_engine_a -f "$repo/$f" >/dev/null
done
psql -X -q -v ON_ERROR_STOP=1 -d ancora_engine_a <<'SQL'
CREATE SCHEMA tst_it;
CREATE SEQUENCE tst_it.placements_started;
CREATE TABLE tst_it.delay (seconds float8 NOT NULL);
INSERT INTO tst_it.delay VALUES (0.5);
CREATE FUNCTION tst_it.slow_placement() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  PERFORM nextval('tst_it.placements_started');
  PERFORM pg_sleep((SELECT seconds FROM tst_it.delay));
  RETURN NULL;
END $$;
GRANT USAGE ON SCHEMA tst_it TO engine_a;
GRANT USAGE, SELECT, UPDATE ON SEQUENCE tst_it.placements_started TO engine_a;
GRANT SELECT ON tst_it.delay TO engine_a;
SQL
delay() { sqla "UPDATE tst_it.delay SET seconds = $1" >/dev/null; }
slow_on() { sqla "CREATE TRIGGER tst_it_slow AFTER INSERT ON actividad FOR EACH ROW EXECUTE FUNCTION tst_it.slow_placement()" >/dev/null; }
slow_off() { sqla "DROP TRIGGER IF EXISTS tst_it_slow ON actividad" >/dev/null; }
started_count() { sqla "SELECT CASE WHEN is_called THEN last_value ELSE 0 END FROM tst_it.placements_started"; }
wait_started() { # n placements started after the mark (30 s)
  local target=$(( $1 + mark )) deadline=$((SECONDS + 30))
  while [ "$(started_count)" -lt "$target" ]; do [ $SECONDS -lt $deadline ] || return 1; sleep 0.05; done
}
placements() { sqla "SELECT count(*) FROM actividad"; }
expected=$(grep -v '^#' "$repo/dev/e2e/expected-placements.md5" | head -1)

export ANCORA_ENVIRONMENT=production
export ANCORA_DB_HOST="$PGHOST" ANCORA_DB_PORT="$PGPORT" ANCORA_DB_NAME=ancora ANCORA_DB_USER=ancora
export ANCORA_DB_PASSWORD_FILE="$work/secrets/db-password"
export ANCORA_HASH_SALT_FILE="$work/secrets/hash-salt"
export ANCORA_TRUSTED_HOSTS=127.0.0.1,localhost
export ANCORA_PRIVATE_PATH="$work/private" ANCORA_TMP_PATH="$work/tmp"
# Engine credentials of site A: server-side, from the environment and a file.
export ANCORA_ENGINE_DB_HOST="$PGHOST" ANCORA_ENGINE_DB_PORT="$PGPORT"
export ANCORA_ENGINE_DB_NAME=ancora_engine_a ANCORA_ENGINE_DB_USER=engine_a
export ANCORA_ENGINE_DB_PASSWORD_FILE="$work/secrets/engine-a-password"
export ANCORA_ENGINE_HEARTBEAT_INTERVAL=1 ANCORA_ENGINE_STALE_AFTER=3 ANCORA_ENGINE_CANCEL_GRACE=30

cd "$project"
drush() { vendor/bin/drush --yes "$@"; }
job() { drush ancora-engine:job "$1"; }
field() { job "$1" | jq -r "$2"; }

step "drush site:install --existing-config (ancora_engine enabled)"
drush site:install --existing-config --site-name=Áncora --site-mail=noreply@example.invalid \
  --account-name=admin --account-mail=admin@example.invalid --account-pass="$(random_hex 16)" >"$work/install.log" 2>&1 \
  || { cat "$work/install.log"; exit 1; }
check "ancora_engine is enabled" eq "$(drush pm:list --status=enabled --field=name --filter=ancora_engine 2>/dev/null | tr -d ' ')" ancora_engine

step "separation: no Drupal table in the engine database, no engine table in the Drupal database"
check "engine database has no Drupal table" eq "$(sqla "SELECT count(*) FROM pg_tables WHERE tablename IN ('ancora_engine_job', 'users', 'config', 'key_value')")" 0
check "Drupal database has the job table and no engine table" eq \
  "$(psql -X -At -d ancora -c "SELECT count(*) FILTER (WHERE tablename = 'ancora_engine_job') || '/' || count(*) FILTER (WHERE tablename = 'actividad') FROM pg_tables")" "1/0"

step "option validation (CLI)"
check "forma=3 is refused" bash -c "! vendor/bin/drush ancora-engine:enqueue --specialty=E --level=N --period=PER --forma=3 >/dev/null 2>&1"
check "compactar=11 is refused" bash -c "! vendor/bin/drush ancora-engine:enqueue --specialty=E --level=N --period=PER --compactar=11 >/dev/null 2>&1"
check "a time limit above the plan maximum is refused" bash -c "! vendor/bin/drush ancora-engine:enqueue --specialty=E --level=N --period=PER --time-limit=121 >/dev/null 2>&1"

step "1. success on the E2E scope"
id=$(drush ancora-engine:enqueue --specialty=E --level=N --period=PER)
check "job $id is queued (nothing runs at enqueue time)" eq "$(field "$id" .state)/$(placements)" "queued/0"
drush ancora-engine:work --once
j=$(job "$id")
echo "      $(jq -c .result <<<"$j")"
check "state succeeded, a final timetable" eq "$(jq -r '.state + "/" + (.final_timetable|tostring)' <<<"$j")" "succeeded/true"
check "demands=8 placed=8 unplaced=0 collisions=0 overflow=0" eq \
  "$(jq -r '.result | "demands=\(.demands) placed=\(.placed) unplaced=\(.unplaced) collisions=\(.collisions) overflow=\(.overflow)"' <<<"$j")" \
  "demands=8 placed=8 unplaced=0 collisions=0 overflow=0"
check "worker metrics equal tst.metrics" eq "METRIC $(jq -r '.result | "demands=\(.demands) placed=\(.placed) unplaced=\(.unplaced) collisions=\(.collisions) overflow=\(.overflow)"' <<<"$j")" "$(sqla "SELECT tst.metrics('E', 'N', 'PER')")"
check "placement digest is the E2E digest ($expected)" eq "$(jq -r .result.digest <<<"$j")" "$expected"
digest_now() {
  sqla "SELECT string_agg(a.plantilla || '/' || a.grupo || '/' || p.nombre || '/' || a.lugar || '/' || a.dia || '/' || a.turno, ',' ORDER BY a.plantilla, a.grupo) FROM actividad a JOIN profesor p ON p.id = a.profesor" | tr -d '\n' | md5sum | cut -d' ' -f1
}
check "the engine database holds that timetable (e2e_placements digest)" eq "$(digest_now)" "$expected"

step "2. soft cancellation mid-flight (separate connection, after placement started)"
slow_on; mark=$(started_count)
id2=$(drush ancora-engine:enqueue --specialty=E --level=N --period=PER)
vendor/bin/drush ancora-engine:work --once >"$work/w2.log" 2>&1 & wpid=$!; bg+=("$wpid")
wait_started 3 || bad "placement 3 never started"
t0=$SECONDS
drush ancora-engine:cancel "$id2"
wait "$wpid" || true
j=$(job "$id2")
echo "      $(jq -c .result <<<"$j") (returned $((SECONDS - t0)) s after the request)"
check "state cancelled, never a final timetable" eq "$(jq -r '.state + "/" + (.final_timetable|tostring)' <<<"$j")" "cancelled/false"
check "partial placements kept as a draft (kept=partial, partial=true)" eq "$(jq -r '.result.kept + "/" + (.result.partial|tostring)' <<<"$j")" "partial/true"
placed2=$(jq -r .result.placed <<<"$j")
check "some but not all activities placed (3 <= $placed2 < 8) and committed" bash -c "[ $placed2 -ge 3 ] && [ $placed2 -lt 8 ] && [ $(placements) = $placed2 ]"
check "the run stopped early (within 5 s of the request)" test $((SECONDS - t0)) -le 5
check "the cancellation row was consumed" eq "$(sqla 'SELECT count(*) FROM generador.cancelar')" 0

step "3. retry: replaces the partial placements, runs fresh"
slow_off
id4=$(drush ancora-engine:retry "$id2")
drush ancora-engine:work --once
j=$(job "$id4")
check "retry $id4 of $id2 succeeded with the E2E digest" eq "$(jq -r '.state + "/" + .result.digest + "/" + (.retry_of|tostring)' <<<"$j")" "succeeded/$expected/$id2"
check "the retry first removed the $placed2 partial placements" eq "$(jq -r .result.replaced <<<"$j")" "$placed2"

step "4. timeout: rolled back, nothing of the run kept"
slow_on; mark=$(started_count)
id5=$(drush ancora-engine:enqueue --specialty=E --level=N --period=PER --time-limit=2)
drush ancora-engine:work --once
j=$(job "$id5")
check "state timed_out, kept=none" eq "$(jq -r '.state + "/" + .result.kept' <<<"$j")" "timed_out/none"
check "placement had started before the timeout" test "$(started_count)" -gt "$mark"
check "the previous final timetable is untouched (rollback of the replace)" eq "$(digest_now)" "$expected"
sqla "DELETE FROM actividad" >/dev/null; mark=$(started_count)
id6=$(drush ancora-engine:enqueue --specialty=E --level=N --period=PER --time-limit=2)
drush ancora-engine:work --once
check "on an empty scope: timed_out and zero placements kept" eq "$(field "$id6" .state)/$(placements)" "timed_out/0"
check "  (placement had started)" test "$(started_count)" -gt "$mark"

step "5. one running job per site; hard cancellation (pg_cancel_backend): rolled back"
delay 2; mark=$(started_count)
id7=$(drush ancora-engine:enqueue --specialty=E --level=N --period=PER)
vendor/bin/drush ancora-engine:work --once >"$work/w7.log" 2>&1 & wpid=$!; bg+=("$wpid")
wait_started 1 || bad "placement never started"
id3=$(drush ancora-engine:enqueue --specialty=E --level=N --period=PER)
drush ancora-engine:work --once
check "while job $id7 runs, a second worker claims nothing: job $id3 stays queued" eq "$(field "$id3" .state)/$(field "$id7" .state)" "queued/running"
drush ancora-engine:cancel "$id3"
check "a queued job is cancelled at once and never runs" eq "$(field "$id3" '.state + "/" + (.attempts|tostring) + "/" + .result.kept')" "cancelled/0/none"
drush ancora-engine:cancel --hard "$id7"
wait "$wpid" || true
check "state cancelled, kept=none, zero placements" eq "$(field "$id7" '.state + "/" + .result.kept')/$(placements)" "cancelled/none/0"
delay 0.5

step "6. stale worker: requeued once, then failed"
mark=$(started_count)
id8=$(drush ancora-engine:enqueue --specialty=E --level=N --period=PER)
vendor/bin/drush ancora-engine:work --once >"$work/w8.log" 2>&1 & wpid=$!; bg+=("$wpid")
wait_started 1 || bad "placement never started"
kill -9 "$wpid"; wait "$wpid" 2>/dev/null || true
check "the engine backend of the lost worker is still running" eq "$(sqla "SELECT count(*) FROM pg_stat_activity WHERE application_name = 'ancora_engine_job_${id8}_1'")" 1
sleep 4
drush ancora-engine:recover
check "recover: requeued (attempt 1 of 2)" eq "$(field "$id8" '.state + "/" + (.attempts|tostring)')" "queued/1"
check "recover terminated the orphaned engine backend" eq "$(sqla "SELECT count(*) FROM pg_stat_activity WHERE application_name LIKE 'ancora_engine_job_${id8}_%'")" 0
check "its partial work was rolled back" eq "$(placements)" 0
slow_off
drush ancora-engine:work --once
check "the requeued job succeeds on attempt 2 with the E2E digest" eq "$(field "$id8" '.state + "/" + (.attempts|tostring) + "/" + .result.digest')" "succeeded/2/$expected"
slow_on; mark=$(started_count)
id9=$(drush ancora-engine:enqueue --specialty=E --level=N --period=PER)
vendor/bin/drush ancora-engine:work --once >"$work/w9.log" 2>&1 & wpid=$!; bg+=("$wpid")
wait_started 1 || bad "placement never started"
kill -9 "$wpid"; wait "$wpid" 2>/dev/null || true
sleep 4
ANCORA_ENGINE_MAX_ATTEMPTS=1 vendor/bin/drush ancora-engine:recover
check "after its last attempt a lost job fails" eq "$(field "$id9" .state)" failed
check "  and the previous timetable is untouched" eq "$(digest_now)" "$expected"
slow_off

step "7. unknown scope"
id10=$(drush ancora-engine:enqueue --specialty=E --level=N --period=NOPE)
drush ancora-engine:work --once
check "a scope missing from the engine database fails" eq "$(field "$id10" '.state + ": " + .error')" "failed: The scope does not exist in the engine database."

step "8. two sites: role boundary"
check "engine_b cannot connect to site A's engine database" "$project/scripts/engine-db.sh" check-isolation ancora_engine_a engine_a engine_b
check "engine_a cannot connect to site B's engine database" "$project/scripts/engine-db.sh" check-isolation ancora_engine_b engine_b engine_a
id11=$(drush ancora-engine:enqueue --specialty=E --level=N --period=PER)
ANCORA_ENGINE_DB_USER=engine_b ANCORA_ENGINE_DB_PASSWORD_FILE="$work/secrets/engine-b-password" vendor/bin/drush ancora-engine:work --once
err=$(field "$id11" .error)
echo "      error: $err"
check "a worker with site B's role on site A's database fails: permission denied" bash -c "[ \"\$(vendor/bin/drush ancora-engine:job $id11 | jq -r .state)\" = failed ] && grep -q 'permission denied for database' <<<\"$err\""
check "the error summary contains no password" bash -c "! grep -qF -e '$(cat "$work/secrets/engine-b-password")' -e '$(cat "$work/secrets/engine-a-password")' <<<\"$err\""

echo
echo "# engine integration: $pass passed, $fail failed ($((SECONDS - started)) s)"
[ "$fail" = 0 ]
