#!/usr/bin/env bash
# The R1 scheduling journey, end to end, on the real stack (Caddy and PHP-FPM
# on 127.0.0.1) against a disposable PostgreSQL cluster:
#
#   dev/sql/tools/with-ephemeral-pg.sh modern/scripts/r1-journey.sh [--skip-composer]
#
# Environment: CADDY (default caddy), PHP_FPM (default php-fpm8.4, then
# php-fpm8.5, then php-fpm). Requires jq.
#
# 1. The operator tool (scripts/ancora-site.sh), run as a NON-superuser role
#    with CREATEDB and CREATEROLE as on the real host, installs the control
#    site and provisions alpha and beta, each with its own engine database
#    and engine role.
# 2. On alpha, the synthetic organization of the legacy E2E harness is
#    entered through the Entity API (drush ancora-academic:load-fixture,
#    including the teacher the harness enters over HTTP), owned by alpha's
#    planner: as in legacy, each user sees and schedules their own plans
#    (#70, legacy creador).
# 3. A planner of alpha logs in over HTTP and requests a timetable with the
#    request form (Drupal form token); nothing runs in the request. The
#    worker runs once: the job syncs the plan into tt_alpha_engine in the
#    run's transaction and succeeds with demands=8 placed=8 unplaced=0
#    collisions=0 overflow=0 and the E2E placement digest. The timetable page
#    lists the 8 placements in the right cells.
# 4. Beta's planner and anonymous users cannot see alpha's timetable or jobs;
#    beta's engine role cannot connect to alpha's engine database.
# 5. A run cancelled mid-flight over HTTP is shown only as a draft; a new
#    request gives the final timetable again. A failed sync rolls back and
#    leaves the final timetable intact.
#
# Every check prints PASS or FAIL; the summary has the counts. Exit status 0
# only if every check passes. Everything is deleted on exit.
set -euo pipefail

skip_composer=0
for arg in "$@"; do
  case "$arg" in
    --skip-composer) skip_composer=1 ;;
    *) echo "unknown option: $arg" >&2; exit 2 ;;
  esac
done

project=$(cd "$(dirname "$0")/.." && pwd -P)
repo=$(dirname "$project")
web=$project/web
# shellcheck source=../../dev/sql/tools/disposable-guard.sh
. "$repo/dev/sql/tools/disposable-guard.sh"
agoh_require_disposable || exit 3
command -v jq >/dev/null || { echo "jq is required" >&2; exit 2; }

caddy_bin=${CADDY:-caddy}
fpm_bin=${PHP_FPM:-}
if [ -z "$fpm_bin" ]; then
  for c in php-fpm8.4 php-fpm8.5 php-fpm; do
    if command -v "$c" >/dev/null; then fpm_bin=$c; break; fi
  done
fi
command -v "$caddy_bin" >/dev/null || { echo "caddy not found" >&2; exit 2; }
[ -n "$fpm_bin" ] && command -v "$fpm_bin" >/dev/null || { echo "php-fpm not found" >&2; exit 2; }
if [ -e "$web/o" ] || compgen -G "$web/sites/org-*" >/dev/null; then
  echo "refusing: web/o or web/sites/org-* already exist in $web" >&2
  exit 3
fi

started=$SECONDS
step() { printf '\n==> [%3ss] %s\n' "$((SECONDS - started))" "$*"; }
work=$(mktemp -d "${TMPDIR:-/tmp}/ancora-r1.XXXXXX")
work=$(cd "$work" && pwd -P)
pids=()
cleanup() {
  local pid d
  for pid in "${pids[@]}"; do kill "$pid" 2>/dev/null || true; done
  for pid in "${pids[@]}"; do wait "$pid" 2>/dev/null || true; done
  rm -rf "$web/o"
  for d in "$web"/sites/org-*; do
    [ -e "$d" ] || continue
    chmod -R u+w "$d"
    rm -rf "$d"
  done
  chmod u+w "$web/sites/default" "$web/sites/default/settings.php" 2>/dev/null || true
  if [ "${ANCORA_VERIFY_KEEP:-0}" = 1 ]; then echo "kept $work"; else rm -rf "$work"; fi
}
trap cleanup EXIT

# shellcheck source=lib/stack.sh
. "$project/scripts/lib/stack.sh"
eq() { [ "$1" = "$2" ] || { echo "      expected '$2', got '$1'"; return 1; }; }

port=$(free_port)
base="http://127.0.0.1:$port"
mkdir -p "$work/etc/control" "$work/var/control/private" "$work/var/control/tmp" "$work/backup" "$work/log"
export ANCORA_SITES_DIR=$work/etc/sites ANCORA_SITES_DATA_DIR=$work/var/sites ANCORA_PUBLIC_URL=$base
export ANCORA_ENVIRONMENT=production ANCORA_TRUSTED_HOSTS=127.0.0.1,localhost
export ANCORA_DB_HOST=$PGHOST ANCORA_DB_PORT=$PGPORT
export ANCORA_DB_NAME=tt_control ANCORA_DB_USER=tt_control_app ANCORA_DB_OWNER_USER=tt_control_owner
export ANCORA_DB_PASSWORD_FILE=$work/etc/control/db-password
export ANCORA_DB_OWNER_PASSWORD_FILE=$work/etc/control/owner-password
export ANCORA_HASH_SALT_FILE=$work/etc/control/hash-salt
export ANCORA_PRIVATE_PATH=$work/var/control/private ANCORA_TMP_PATH=$work/var/control/tmp
# A short grace, so a soft cancellation that is not honoured escalates fast.
export ANCORA_ENGINE_CANCEL_GRACE=20
site=$project/scripts/ancora-site.sh
# The operator tool runs as a non-superuser role, as on the real host; only
# this script's own checks and test fixtures use the cluster superuser.
operator=ancora_journey_operator
site_op() { PGUSER=$operator "$site" "$@"; }
alpha_drush() { ANCORA_EXPECT_SITE_PATH=sites/org-alpha "$project/vendor/bin/drush" --root="$web" --uri="$base/o/alpha" --yes "$@"; }
beta_drush() { ANCORA_EXPECT_SITE_PATH=sites/org-beta "$project/vendor/bin/drush" --root="$web" --uri="$base/o/beta" --yes "$@"; }
job_json() { alpha_drush ancora-engine:job "$1"; }
field() { job_json "$1" | jq -r "$2"; }
engine() { psql -X -At -v ON_ERROR_STOP=1 -d tt_alpha_engine -c "$1"; }
expected=$(grep -v '^#' "$repo/dev/e2e/expected-placements.md5" | head -1)

cd "$project"
if [ "$skip_composer" -eq 0 ]; then
  step "composer install"
  composer install --no-interaction --no-progress
fi

psql -X -q -d postgres -c "ALTER SYSTEM SET log_connections = on" -c "SELECT pg_reload_conf()" >/dev/null
pg_log=$(dirname "$AGOH_DISPOSABLE_DATA")/server.log
psql -X -q -d postgres -c "CREATE ROLE $operator LOGIN CREATEDB CREATEROLE" \
  -c "ALTER ROLE $operator SET createrole_self_grant = 'set, inherit'" >/dev/null

step "1. Provisioning as a non-superuser operator: control site, alpha, beta ($(psql -X -At -d postgres -c 'SHOW server_version'))"
site_op control-install --admin-mail operator@example.invalid
site_op provision alpha --name "Alpha School" --admin-mail admin@alpha.example.invalid
site_op provision beta --name "Beta Institute" --admin-mail admin@beta.example.invalid

section "Provisioning"
check "the operator role is not a superuser" test "$(sql postgres "SELECT rolsuper FROM pg_roles WHERE rolname = '$operator'")" = f
check "alpha and beta have their engine databases" test "$(sql postgres "SELECT string_agg(datname, ',' ORDER BY datname) FROM pg_database WHERE datname LIKE 'tt\_%\_engine'")" = tt_alpha_engine,tt_beta_engine
check "the engine roles are not superusers and cannot create roles or databases" test "$(sql postgres "SELECT count(*) FROM pg_roles WHERE rolname LIKE 'tt\_%\_engine\_app' AND NOT (rolsuper OR rolcreatedb OR rolcreaterole OR rolreplication OR rolbypassrls)")" = 2
check "check-isolation: tt_alpha_engine refuses beta's engine role" "$project/scripts/engine-db.sh" check-isolation tt_alpha_engine tt_alpha_engine_app tt_beta_engine_app
check "check-isolation: tt_beta_engine refuses alpha's engine role" "$project/scripts/engine-db.sh" check-isolation tt_beta_engine tt_beta_engine_app tt_alpha_engine_app
check "re-running provision alpha keeps the engine database (idempotent)" bash -c "PGUSER=$operator '$site' provision alpha --name 'Alpha School' --admin-mail admin@alpha.example.invalid >'$work/log/rerun.log' 2>&1"

step "2. Planners of alpha and beta; alpha's planning data through the Entity API, owned by alpha's planner"
random_hex() { od -An -N"$1" -tx1 /dev/urandom | tr -d ' \n'; }
alpha_pass=$(random_hex 12) beta_pass=$(random_hex 12)
for s in alpha beta; do
  d=${s}_drush pass_var=${s}_pass
  $d role:create planner Planner >/dev/null
  $d role:perm:add planner 'view academic data,edit academic data,view ancora engine jobs,request ancora engine jobs' >/dev/null
  $d user:create "planner-$s" --mail="planner@$s.example.invalid" --password="${!pass_var}" >/dev/null
  $d user:role:add planner "planner-$s" >/dev/null
done
alpha_drush ancora-academic:load-fixture --owner=planner-alpha
ids=$(alpha_drush php:eval '
  $m = \Drupal::entityTypeManager();
  $one = fn ($type, $props) => (int) array_key_first($m->getStorage($type)->loadByProperties($props));
  $uid = $one("user", ["name" => "planner-alpha"]);
  $plan = $one("ancora_plan", ["code" => "P", "uid" => $uid]);
  print implode(" ", [$plan, $one("ancora_period", ["plan" => $plan, "code" => "PER"]), $one("ancora_specialty", ["uid" => $uid, "code" => "E"]), $one("ancora_level", ["uid" => $uid, "code" => "N"]), $one("ancora_room", ["uid" => $uid, "code" => "L1"]), $uid]);')
read -r plan period specialty level room planner_uid <<<"$ids"
# Engine ids: the entity id, zero-padded, ":" and the code (LegacyRows::engineId()).
eid() { printf '%010d:%s' "$1" "$2"; }
echo "plan $plan, period $period, specialty $specialty, level $level, room L1 $room, owner uid $planner_uid"

stack_start
stack_wait "$base/user/login" "$base/o/alpha/user/login"

login() { # jar slug name password
  req "$1" "/o/$2/user/login"
  local build_id
  build_id=$(form_value form_build_id)
  req "$1" "/o/$2/user/login" --data-urlencode "name=$3" --data-urlencode "pass=$4" \
    --data-urlencode form_id=user_login_form --data-urlencode "form_build_id=$build_id" --data-urlencode "op=Log in"
  status_in 302 303
}
request_run() { # jar: submits the request form for alpha's plan; prints the job id
  req "$1" "/o/alpha/ancora/scheduling/request?plan=$plan"
  local build_id token
  build_id=$(form_value form_build_id) token=$(form_value form_token)
  req "$1" /o/alpha/ancora/scheduling/request --data-urlencode "plan=$plan" --data-urlencode "period=$period" \
    --data-urlencode "specialty=$specialty" --data-urlencode "level=$level" --data-urlencode form_id=ancora_scheduling_request_run \
    --data-urlencode "form_build_id=$build_id" --data-urlencode "form_token=$token" --data-urlencode "op=Request the timetable"
  sed -n 's#^[Ll]ocation: .*/o/alpha/ancora/scheduling/jobs/\([0-9]*\).*#\1#p' "$work/r.headers" | tr -d '\r'
}
timetable_cells() { php "$project/scripts/lib/timetable-cells.php" "$work/r.body" "$1" | LC_ALL=C sort; }
# The code of a numbered engine id: what follows the separator.
code_of() { echo "regexp_replace($1, '^[0-9]{10}:', '')"; }
in_period="a.plantilla IN (SELECT pa.id FROM plantilla_de_actividad pa JOIN desglose d ON d.id = pa.desglose WHERE d.periodo = '$(eid "$period" PER)')"
engine_cells() { # the scope's placements in the engine database, as the page should list them
  engine "SELECT a.dia || '/' || a.turno || '/' || $(code_of a.grupo) || '/' || $(code_of a.profesor)
                 || '/' || $(code_of a.lugar) || '/' || g.descripcion || '/' || p.nombre || '/' || l.nombre
            FROM actividad a JOIN grupo g ON g.id = a.grupo JOIN profesor p ON p.id = a.profesor JOIN lugar l ON l.id = a.lugar
           WHERE $in_period" | LC_ALL=C sort
}
digest_now() { # the E2E placement digest of the engine database, with the codes
  engine "SELECT md5(coalesce(string_agg($(code_of a.plantilla) || '/' || $(code_of a.grupo) || '/' || p.nombre
                 || '/' || $(code_of a.lugar) || '/' || a.dia || '/' || a.turno, ',' ORDER BY a.plantilla, a.grupo), ''))
            FROM actividad a JOIN profesor p ON p.id = a.profesor WHERE $in_period"
}
timetable() { req "$1" "/o/alpha/ancora/planning/$plan/timetable?period=$period${2:-}"; }

step "3. Alpha's planner requests a timetable over HTTP; the worker runs it"
section "Request"
check "alpha's planner logs in over HTTP" login alpha alpha planner-alpha "$alpha_pass"
req alpha /o/alpha/ancora/scheduling/request
check "the request form answers 200 with a form token" bash -c "[ \"\$(cat '$work/r.status')\" = 200 ] && grep -q 'name=\"form_token\"' '$work/r.body'"
job1=$(request_run alpha)
check "submitting the form queues a job and redirects to its status page" test -n "$job1"
check "nothing ran in the request: job $job1 is queued, the engine has no data of the plan" eq "$(field "$job1" .state)/$(engine "SELECT count(*) FROM planificacion WHERE id = '$(eid "$plan" P)'")" "queued/0"
check "the job's scope is the numbered engine ids, with its Drupal source" eq "$(field "$job1" '[.scope.specialty, .scope.level, .scope.period, (.source.plan|tostring), .source.id_prefix] | join(" ")')" "$(eid "$specialty" E) $(eid "$level" N) $(eid "$period" PER) $plan :"
req alpha "/o/alpha/ancora/scheduling/jobs/$job1"
check "the status page shows the queued job" bash -c "[ \"\$(cat '$work/r.status')\" = 200 ] && grep -q 'Queued' '$work/r.body'"

section "Worker"
site_op work alpha --once >"$work/log/work1.log" 2>&1
j=$(job_json "$job1")
check "job $job1 succeeded (final timetable)" eq "$(jq -r '.state + "/" + (.final_timetable|tostring)' <<<"$j")" "succeeded/true"
check "metrics demands=8 placed=8 unplaced=0 collisions=0 overflow=0" eq "$(jq -r '[.result.demands, .result.placed, .result.unplaced, .result.collisions, .result.overflow] | map(tostring) | join(" ")' <<<"$j")" "8 8 0 0 0"
check "placement digest = E2E digest $expected" eq "$(jq -r .result.digest <<<"$j")" "$expected"
check "the engine database agrees (digest recomputed with SQL)" eq "$(digest_now)" "$expected"
export_hash=$(alpha_drush php:eval '$p = \Drupal::entityTypeManager()->getStorage("ancora_plan")->load('"$plan"'); print \Drupal\ancora_scheduling\ScopeMapper::hash(\Drupal::service("Drupal\ancora_academic\Export\AcademicExport")->export($p));')
check "the job records the input snapshot: sha256 of the canonical export" eq "$(jq -r .result.input_hash <<<"$j")/$(jq -r .result.input.committed <<<"$j")" "$export_hash/true"
check "the sync wrote the plan's rows and reused the trigger's breakdown rows" eq "$(jq -r '.result.input.tables.desglose.inserted // 0' <<<"$j")/$(engine "SELECT count(*) FROM desglose d WHERE d.asignatura ~ '^[0-9]{10}:'")" "0/2"
check "the rows' legacy owner is the planner who created them (creador), not a per-plan user" eq "$(engine "SELECT string_agg(DISTINCT creador, ',') FROM (SELECT creador FROM asignatura UNION SELECT creador FROM planificacion UNION SELECT creador FROM profesor UNION SELECT creador FROM lugar) c")" "u$planner_uid"

section "Timetable page"
req alpha "/o/alpha/ancora/scheduling/jobs/$job1"
check "the status page shows 'Succeeded: final timetable' and the digest" bash -c "grep -q 'Succeeded: final timetable' '$work/r.body' && grep -q '$expected' '$work/r.body'"
timetable alpha
check "the timetable page answers 200" status_is 200
check "it shows a final timetable and no draft" bash -c "grep -q 'data-timetable=\"final\"' '$work/r.body' && ! grep -q 'data-timetable=\"draft\"' '$work/r.body'"
timetable_cells final >"$work/page.cells"
engine_cells >"$work/engine.cells"
check "the page lists the 8 placements" eq "$(wc -l <"$work/page.cells")" 8
check "each placement is in its day/slot cell with its group, teacher and room" diff "$work/engine.cells" "$work/page.cells"
check "the names come from the Drupal records (Group 1, Teacher Alpha, Teacher Beta, Room 1)" bash -c "grep -q '/Group 1/' '$work/page.cells' && grep -q '/Teacher Alpha/' '$work/page.cells' && grep -q '/Teacher Beta/' '$work/page.cells' && grep -q '/Room 1\$' '$work/page.cells'"
timetable alpha "&group=G1"
check "filter by group G1: 4 placements, all of G1" eq "$(timetable_cells final | cut -d/ -f3 | sort | uniq -c | tr -s ' ')" " 4 G1"
timetable alpha "&teacher=TB"
check "filter by teacher TB: 4 placements, all of Teacher Beta" eq "$(timetable_cells final | cut -d/ -f7 | sort | uniq -c | tr -s ' ')" " 4 Teacher Beta"
timetable alpha "&room=L1"
check "filter by room L1: only Room 1" eq "$(timetable_cells final | cut -d/ -f8 | sort -u)" "Room 1"
timetable alpha
# Since #70 the plan is visible to its creator only (legacy creador), so
# the page varies by user; Dynamic Page Cache does not cache per-user
# responses (core: the "user" context is an auto-placeholder condition).
check "the timetable page varies by user (creator rule): not cached by Dynamic Page Cache" header_has '^x-drupal-dynamic-cache: UNCACHEABLE'

step "4. Isolation: beta and anonymous users"
section "Isolation"
check "beta's planner logs in on beta" login beta beta planner-beta "$beta_pass"
for p in "/o/alpha/ancora/planning/$plan/timetable" "/o/alpha/ancora/scheduling/jobs/$job1" /o/alpha/ancora/scheduling/request /o/alpha/admin/ancora/engine; do
  req beta "$p"
  check "beta's planner on alpha's $p: 403" status_is 403
done
beta_cookie=$(awk '$6 ~ /^S?SESS/ {print $6 "=" $7}' "$work/jar/beta" | tail -1)
req - "/o/alpha/ancora/planning/$plan/timetable" -H "Cookie: $beta_cookie"
check "beta's session cookie replayed on alpha's timetable: 403" status_is 403
req beta "/o/beta/ancora/scheduling/jobs/$job1"
check "alpha's job id on beta: 404 (beta has no such job)" status_is 404
req beta "/o/beta/ancora/planning/$plan/timetable"
check "alpha's plan id on beta: 404 (beta has no such plan)" status_is 404
check "beta's site has no job and its engine database no plan data" eq "$(sql tt_beta 'SELECT count(*) FROM ancora_engine_job')/$(psql -X -At -d tt_beta_engine -c 'SELECT count(*) FROM planificacion')" "0/0"
req - "/o/alpha/ancora/planning/$plan/timetable"
check "anonymous on alpha's timetable: 403" status_is 403
check "beta's engine role cannot connect to alpha's engine database" bash -c "psql -X -U tt_beta_engine_app -d tt_alpha_engine -c 'SELECT 1' 2>&1 | grep -q 'permission denied for database'"
check "the timetable read tt_alpha_engine as tt_alpha_engine_app (read-only session)" grep -q 'connection authorized: user=tt_alpha_engine_app database=tt_alpha_engine application_name=ancora_engine_read' "$pg_log"
foreign_engine_logins() { # logins of the application (worker, web) to an engine database with another role
  grep -o 'connection authorized: user=[^ ]* database=tt_[a-z0-9_]*_engine application_name=ancora_engine' "$pg_log" | sed 's/.*user=\([^ ]*\) database=\([^ ]*\) .*/\1 \2/' \
    | awk -v op="$operator" '$1 != "postgres" && $1 != op && $1 != $2 "_app"' | sort -u
}
check "every application login to an engine database (worker and web) used that site's engine role" eq "$(foreign_engine_logins)" ""
check "the worker ran its jobs on tt_alpha_engine as tt_alpha_engine_app" grep -q 'connection authorized: user=tt_alpha_engine_app database=tt_alpha_engine application_name=ancora_engine_job_' "$pg_log"

step "5. A run cancelled mid-flight is only a draft; a new request is final again"
# Test-only slow placements (superuser, alpha's engine database only), as in
# scripts/engine-integration.sh.
psql -X -q -v ON_ERROR_STOP=1 -d tt_alpha_engine <<'SQL'
CREATE SCHEMA tst_r1;
CREATE SEQUENCE tst_r1.placements_started;
CREATE FUNCTION tst_r1.slow_placement() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  PERFORM nextval('tst_r1.placements_started');
  PERFORM pg_sleep(0.5);
  RETURN NULL;
END $$;
GRANT USAGE ON SCHEMA tst_r1 TO tt_alpha_engine_app;
GRANT USAGE, SELECT, UPDATE ON SEQUENCE tst_r1.placements_started TO tt_alpha_engine_app;
CREATE TRIGGER tst_r1_slow AFTER INSERT ON actividad FOR EACH ROW EXECUTE FUNCTION tst_r1.slow_placement();
SQL
started_count() { engine "SELECT CASE WHEN is_called THEN last_value ELSE 0 END FROM tst_r1.placements_started"; }
section "Soft cancellation over HTTP"
job2=$(request_run alpha)
check "a second request queues job $job2" test -n "$job2" -a "$job2" != "$job1"
dup=$(request_run alpha)
check "a third request while job $job2 is queued is refused by the form" bash -c "[ -z '$dup' ] && grep -q 'already queued' '$work/r.body'"
site_op work alpha --once >"$work/log/work2.log" 2>&1 &
worker=$!
deadline=$((SECONDS + 60))
while [ "$(started_count)" -lt 3 ] && [ $SECONDS -lt $deadline ]; do sleep 0.1; done
req alpha "/o/alpha/admin/ancora/engine/$job2/cancel"
build_id=$(form_value form_build_id) token=$(form_value form_token)
req alpha "/o/alpha/admin/ancora/engine/$job2/cancel" --data-urlencode confirm=1 --data-urlencode form_id=ancora_engine_cancel_job \
  --data-urlencode "form_build_id=$build_id" --data-urlencode "form_token=$token" --data-urlencode "op=Cancel the job"
check "the planner cancels the running job over HTTP" status_in 302 303
wait "$worker" || true
j=$(job_json "$job2")
partial=$(jq -r .result.placed <<<"$j")
check "job $job2 ended cancelled, kept = partial, never final" eq "$(jq -r '.state + "/" + .result.kept + "/" + (.final_timetable|tostring)' <<<"$j")" "cancelled/partial/false"
check "it kept some but not all placements (1..7: $partial)" bash -c "[ '$partial' -ge 1 ] && [ '$partial' -lt 8 ]"
timetable alpha
check "the cached page was invalidated when the job ended: only a draft now, labelled as such" bash -c "grep -q 'data-timetable=\"draft\"' '$work/r.body' && ! grep -q 'data-timetable=\"final\"' '$work/r.body' && grep -q 'DRAFT: not a final timetable' '$work/r.body'"
check "the draft lists the $partial placements kept" eq "$(timetable_cells draft | wc -l)" "$partial"
check "no placement is presented as final" eq "$(timetable_cells final | wc -l)" 0
req alpha "/o/alpha/ancora/scheduling/jobs/$job2"
check "the status page says cancelled, draft" grep -q 'Cancelled: draft with the placements made before the cancellation' "$work/r.body"
engine "DROP TRIGGER tst_r1_slow ON actividad" >/dev/null

section "Retry"
job3=$(request_run alpha)
site_op work alpha --once >"$work/log/work3.log" 2>&1
j=$(job_json "$job3")
check "job $job3 succeeded with the E2E digest" eq "$(jq -r '.state + "/" + .result.digest' <<<"$j")" "succeeded/$expected"
check "it replaced the draft's $partial placements" eq "$(jq -r .result.replaced <<<"$j")" "$partial"
check "the sync of an unchanged plan changes no row" eq "$(jq -r '[.result.input.inserted, .result.input.updated, .result.input.deleted] | map(tostring) | join(" ")' <<<"$j")" "0 0 0"
timetable alpha
check "the timetable is final again, with the 8 placements" bash -c "grep -q 'data-timetable=\"final\"' '$work/r.body' && ! grep -q 'data-timetable=\"draft\"' '$work/r.body'"
check "the 8 placements are in their cells" bash -c "diff <(php '$project/scripts/lib/timetable-cells.php' '$work/r.body' final | LC_ALL=C sort) '$work/engine.cells'"

section "A failed sync rolls back and keeps the final timetable"
psql -X -q -v ON_ERROR_STOP=1 -d tt_alpha_engine <<'SQL'
CREATE FUNCTION tst_r1.refuse() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  RAISE EXCEPTION 'test: room updates are refused';
END $$;
CREATE TRIGGER tst_r1_refuse BEFORE UPDATE ON lugar FOR EACH ROW EXECUTE FUNCTION tst_r1.refuse();
SQL
alpha_drush php:eval '$r = \Drupal::entityTypeManager()->getStorage("ancora_room")->load('"$room"'); $r->set("capacity", 60)->save();' >/dev/null
job4=$(request_run alpha)
site_op work alpha --once >"$work/log/work4.log" 2>&1
j=$(job_json "$job4")
check "job $job4 failed in the sync" bash -c "[ '$(jq -r .state <<<"$j")' = failed ] && grep -q 'input sync failed' <<<'$(jq -r .error <<<"$j")'"
check "the engine kept the previous data (room capacity 50) and placements (digest)" eq "$(engine "SELECT capacidad FROM lugar WHERE id = '$(eid "$room" L1)'")/$(digest_now)" "50/$expected"
timetable alpha
check "the timetable is still the final one of job $job3, marked as out of date" bash -c "grep -q 'data-timetable=\"final\"' '$work/r.body' && grep -q 'generated by job $job3' '$work/r.body' && grep -q 'planning data changed after this run' '$work/r.body'"
engine "DROP TRIGGER tst_r1_refuse ON lugar" >/dev/null
job5=$(request_run alpha)
site_op work alpha --once >"$work/log/work5.log" 2>&1
j=$(job_json "$job5")
check "after the fix, job $job5 syncs the change (one room updated) and succeeds" eq "$(jq -r '.state + "/" + (.result.input.tables.lugar.updated|tostring)' <<<"$j")/$(engine "SELECT capacidad FROM lugar WHERE id = '$(eid "$room" L1)'")" "succeeded/1/60"

section "Logs"
check "PostgreSQL logged no permission error other than the refused cross-site connections" bash -c "! grep 'permission denied' '$pg_log' | grep -v 'permission denied for database' | grep -q ."
check "no PHP error logged by PHP-FPM" bash -c "! grep -qi 'fatal\|warning\|exception' '$work/log/php.log' 2>/dev/null"

step "R1 scheduling journey: $pass passed, $fail failed ($((SECONDS - started)) s)"
if [ "$fail" -ne 0 ]; then
  for f in "$work"/log/*; do echo "--- $f"; tail -20 "$f"; done
fi
[ "$fail" -eq 0 ]
