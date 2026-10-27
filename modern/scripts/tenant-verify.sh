#!/usr/bin/env bash
# Tenant provisioning in the single-database layout (issue #96), as on the
# real host, against a disposable PostgreSQL cluster:
#
#   dev/sql/tools/with-ephemeral-pg.sh modern/scripts/tenant-verify.sh [--skip-composer]
#
# Requires modern/vendor (composer install) and jq. No web server: the
# HTTP stack is covered by multisite-verify.sh and r1-journey.sh.
#
# The operator tool (scripts/ancora-site.sh) runs as a NON-superuser role with
# LOGIN CREATEDB CREATEROLE, as ancora_operator on the host; the engine worker
# runs through "ancora-site.sh work" with the site's own roles. Only this
# script's checks and its test-only objects use the cluster superuser.
#
# 1. Provisioning: the control site and the organizations alpha and beta get
#    ONE database each, the engine in schema "scheduler" owned by the
#    tenant's engine owner (NOLOGIN, CREATEROLE, no superuser), the
#    workspace mode in the site file, isolated credentials; no tt_*_engine
#    database. Every SECURITY DEFINER function pins its search_path. No
#    runtime, worker or job role can create anything in "public" or in the
#    engine schemas. Re-running provision changes nothing.
# 2. A job on each site: 8/8 and the E2E digest, read back from the
#    placement entities; nothing of the run is left.
# 3. Isolation during a run (test-only slow-placement trigger): the job role
#    is NOLOGIN and per tenant; the other tenant's worker and runtime roles
#    cannot connect, cannot SET ROLE to it, and the site's own runtime role
#    cannot read the workspace.
# 4. Recovery: a worker killed during a run is recovered, the lost attempt's
#    workspace and job role are dropped, the next attempt publishes once; a
#    workspace kept for diagnosis is dropped by the cleanup when it expires.
# 5. Provisioning recovery and deprovisioning: a provision that stopped
#    after the database step is repaired by running it again; a slug ending
#    in "-engine" is refused; deprovisioning removes the database and every
#    role of the tenant, including the job role of a workspace never closed.
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
if [ -e "$web/o" ] || compgen -G "$web/sites/org-*" >/dev/null; then
  echo "refusing: web/o or web/sites/org-* already exist in $web" >&2
  exit 3
fi

started=$SECONDS
step() { printf '\n==> [%3ss] %s\n' "$((SECONDS - started))" "$*"; }
work=$(mktemp -d "${TMPDIR:-/tmp}/ancora-tenant.XXXXXX")
work=$(cd "$work" && pwd -P)
bg=()
cleanup() {
  local p d
  for p in "${bg[@]}"; do kill -9 "$p" 2>/dev/null || true; done
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

pass=0 fail=0
ok() { pass=$((pass + 1)); echo "PASS  $1"; }
bad() { fail=$((fail + 1)); echo "FAIL  $1"; }
check() { local d=$1; shift; if "$@"; then ok "$d"; else bad "$d"; fi; }
eq() { [ "$1" = "$2" ] || { echo "      expected '$2', got '$1'"; return 1; }; }
sql() { psql -X -At -v ON_ERROR_STOP=1 -d "$1" -c "$2"; }
refused() { # role database sql: the statement fails for the role
  ! psql -X -At -U "$1" -d "$2" -c "$3" >/dev/null 2>&1
}
expected=$(grep -v '^#' "$repo/dev/e2e/expected-placements.md5" | head -1)

mkdir -p "$work/etc/control" "$work/var/control/private" "$work/var/control/tmp" "$work/backup" "$work/log"
export ANCORA_SITES_DIR=$work/etc/sites ANCORA_SITES_DATA_DIR=$work/var/sites ANCORA_PUBLIC_URL=http://127.0.0.1:9
export ANCORA_ENVIRONMENT=production ANCORA_TRUSTED_HOSTS=127.0.0.1,localhost
export ANCORA_DB_HOST=$PGHOST ANCORA_DB_PORT=$PGPORT
export ANCORA_DB_NAME=tt_control ANCORA_DB_USER=tt_control_app ANCORA_DB_OWNER_USER=tt_control_owner
export ANCORA_DB_PASSWORD_FILE=$work/etc/control/db-password
export ANCORA_DB_OWNER_PASSWORD_FILE=$work/etc/control/owner-password
export ANCORA_HASH_SALT_FILE=$work/etc/control/hash-salt
export ANCORA_PRIVATE_PATH=$work/var/control/private ANCORA_TMP_PATH=$work/var/control/tmp
# Shared engine limits (environment): fast recovery for the checks.
export ANCORA_ENGINE_HEARTBEAT_INTERVAL=1 ANCORA_ENGINE_STALE_AFTER=3 ANCORA_ENGINE_CANCEL_GRACE=30 ANCORA_ENGINE_MAX_ATTEMPTS=5
site=$project/scripts/ancora-site.sh
operator=ancora_tenant_operator
site_op() { PGUSER=$operator "$site" "$@"; }
sdrush() { local s=$1; shift; ANCORA_EXPECT_SITE_PATH="sites/org-$s" "$project/vendor/bin/drush" --root="$web" --uri="$ANCORA_PUBLIC_URL/o/$s" --yes "$@"; }
work_once() { "$site" work "$1" --once >>"$work/log/worker-$1.log" 2>&1; }
job() { sdrush "$1" ancora-engine:job "$2"; }
field() { job "$1" "$2" | jq -r "$3"; }
lib=$project/scripts/lib/workspace-journey
stored() { sdrush "$1" php:script "$lib/placements.php" 2>/dev/null; }
count() { stored "$1" | jq -r .count; }
digest() { stored "$1" | jq -j .e2e | md5sum | cut -d' ' -f1; }
enqueue() { sdrush "$1" php:script "$lib/enqueue.php" -- "planner-$1" P PER E N 2>>"$work/log/enqueue.err" | tail -1; }
workspaces() { sql "tt_$1" "SELECT count(*) FROM pg_namespace WHERE nspname LIKE 'scheduler\_job\_%'"; }
job_roles() { sql postgres "SELECT count(*) FROM pg_roles WHERE rolname ~ '^tt_$1_job_[0-9]+_[0-9]+\$'"; }

cd "$project"
if [ "$skip_composer" -eq 0 ]; then
  step "composer install"
  composer install --no-interaction --no-progress
fi

psql -X -q -d postgres -c "CREATE ROLE $operator LOGIN CREATEDB CREATEROLE" \
  -c "ALTER ROLE $operator SET createrole_self_grant = 'set, inherit'" >/dev/null

step "1. Provisioning as a non-superuser operator: control site, alpha, beta ($(sql postgres 'SHOW server_version'))"
site_op control-install --admin-mail operator@example.invalid >"$work/log/control.log" 2>&1 || { cat "$work/log/control.log"; exit 1; }
for s in alpha beta; do
  site_op provision "$s" --name "${s^} School" --admin-mail "admin@$s.example.invalid" >"$work/log/provision-$s.log" 2>&1 \
    || { cat "$work/log/provision-$s.log"; exit 1; }
done
check "the operator role is not a superuser" eq "$(sql postgres "SELECT rolsuper FROM pg_roles WHERE rolname = '$operator'")" f
check "one database per site, no engine database" eq \
  "$(sql postgres "SELECT string_agg(datname, ',' ORDER BY datname) FROM pg_database WHERE datname LIKE 'tt\_%'")" "tt_alpha,tt_beta,tt_control"
check "alpha's site file: workspace mode, its own database, its worker role" bash -c "grep -q '^ENGINE_MODE = \"workspace\"' '$ANCORA_SITES_DIR/alpha/site.ini' && grep -q '^ENGINE_DB_NAME = \"tt_alpha\"' '$ANCORA_SITES_DIR/alpha/site.ini' && grep -q '^ENGINE_DB_USER = \"tt_alpha_engine_app\"' '$ANCORA_SITES_DIR/alpha/site.ini'"
engine_of() { sdrush "$1" php:eval '$s = \Drupal::service("ancora_engine.settings"); $d = $s->database(); print $s->mode() . "/" . $d["database"] . "/" . $d["username"];' 2>/dev/null; }
check "Drupal on alpha resolves the engine to the workspace mode in tt_alpha" eq "$(engine_of alpha)" workspace/tt_alpha/tt_alpha_engine_app
check "Drupal on beta resolves the engine to the workspace mode in tt_beta" eq "$(engine_of beta)" workspace/tt_beta/tt_beta_engine_app
check "no site role is a superuser or has CREATEDB, REPLICATION or BYPASSRLS" eq \
  "$(sql postgres "SELECT count(*) FROM pg_roles WHERE rolname LIKE 'tt\_%' AND (rolsuper OR rolcreatedb OR rolreplication OR rolbypassrls)")" 0
check "only the engine owners have CREATEROLE, and they cannot log in" eq \
  "$(sql postgres "SELECT string_agg(rolname || ':' || rolcanlogin, ',' ORDER BY rolname) FROM pg_roles WHERE rolname LIKE 'tt\_%' AND rolcreaterole")" \
  "tt_alpha_engine_owner:false,tt_beta_engine_owner:false"
check "alpha's roles are its own: owner, runtime, worker, engine owner, job group" eq \
  "$(sql postgres "SELECT string_agg(rolname, ',' ORDER BY rolname) FROM pg_roles WHERE rolname LIKE 'tt\_alpha\_%'")" \
  "tt_alpha_app,tt_alpha_engine_app,tt_alpha_engine_owner,tt_alpha_jobs,tt_alpha_owner"
check "no role of alpha is a member of a role of beta, or the reverse" eq \
  "$(sql postgres "SELECT count(*) FROM pg_auth_members m JOIN pg_roles r ON r.oid = m.roleid JOIN pg_roles u ON u.oid = m.member
     WHERE (r.rolname LIKE 'tt\_alpha\_%' AND u.rolname LIKE 'tt\_beta\_%') OR (r.rolname LIKE 'tt\_beta\_%' AND u.rolname LIKE 'tt\_alpha\_%')")" 0
for s in alpha beta; do
  check "$s: every engine schema is owned by tt_${s}_engine_owner" eq \
    "$(sql "tt_$s" "SELECT string_agg(DISTINCT nspowner::regrole::text, ',') FROM pg_namespace WHERE nspname IN ('scheduler', 'scheduler_control', 'generador', 'seguridad', 'tiempo', 'matrix', 'prioriza', 'portal')")" "tt_${s}_engine_owner"
  check "$s: the SECURITY DEFINER functions are the five provisioning functions, owned by the engine owner, with search_path = pg_catalog, pg_temp" eq \
    "$(sql "tt_$s" "SELECT count(*) || '/' || string_agg(DISTINCT proowner::regrole::text, ',') || '/' || bool_and('search_path=pg_catalog, pg_temp' = ANY (proconfig)) FROM pg_proc WHERE prosecdef")" "5/tt_${s}_engine_owner/true"
  check "$s: no function or relation of the tenant database is owned by a superuser outside pg_catalog, information_schema and extensions" eq \
    "$(sql "tt_$s" "SELECT (SELECT count(*) FROM pg_proc p JOIN pg_roles r ON r.oid = p.proowner JOIN pg_namespace n ON n.oid = p.pronamespace WHERE r.rolsuper AND n.nspname NOT IN ('pg_catalog', 'information_schema')
          AND NOT EXISTS (SELECT 1 FROM pg_depend d WHERE d.classid = 'pg_proc'::regclass AND d.objid = p.oid AND d.deptype = 'e'))
       + (SELECT count(*) FROM pg_class c JOIN pg_roles r ON r.oid = c.relowner JOIN pg_namespace n ON n.oid = c.relnamespace WHERE r.rolsuper AND n.nspname NOT IN ('pg_catalog', 'information_schema', 'pg_toast'))")" 0
  check "$s: the runtime, worker and job roles cannot create in public or in an engine schema, nor create schemas" eq \
    "$(sql "tt_$s" "SELECT count(*) FROM unnest(ARRAY['tt_${s}_app', 'tt_${s}_engine_app', 'tt_${s}_jobs']) r,
       unnest(ARRAY['public', 'scheduler', 'scheduler_control', 'generador', 'seguridad', 'tiempo', 'matrix', 'prioriza', 'portal']) n
       WHERE has_schema_privilege(r, n, 'CREATE') OR has_database_privilege(r, current_database(), 'CREATE')")" 0
  check "$s: the worker and the job roles have no USAGE on public; the runtime role none on the engine schemas" eq \
    "$(sql "tt_$s" "SELECT count(*) FILTER (WHERE has_schema_privilege('tt_${s}_engine_app', 'public', 'USAGE') OR has_schema_privilege('tt_${s}_jobs', 'public', 'USAGE'))
       + count(*) FILTER (WHERE has_schema_privilege('tt_${s}_app', n, 'USAGE')) FROM unnest(ARRAY['scheduler', 'scheduler_control', 'generador', 'seguridad', 'tiempo']) n")" 0
  check "$s: the worker has no TEMPORARY and holds only EXECUTE on the provisioning functions" eq \
    "$(sql "tt_$s" "SELECT has_database_privilege('tt_${s}_engine_app', current_database(), 'TEMPORARY') || '/' ||
       (SELECT count(*) FROM pg_proc p JOIN pg_namespace n ON n.oid = p.pronamespace WHERE n.nspname = 'scheduler_control' AND has_function_privilege('tt_${s}_engine_app', p.oid, 'EXECUTE')) || '/' ||
       (SELECT count(*) FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace WHERE n.nspname IN ('scheduler', 'scheduler_control', 'generador', 'seguridad') AND has_table_privilege('tt_${s}_engine_app', c.oid, 'SELECT,INSERT,UPDATE,DELETE'))")" "false/5/0"
done
check "the measurement schema (test code) is not installed in a tenant database" eq "$(sql tt_alpha "SELECT to_regnamespace('wsproof') IS NULL")" t
oid_before=$(sql tt_alpha "SELECT oid FROM pg_namespace WHERE nspname = 'scheduler'")
check "re-running provision alpha succeeds" bash -c "PGUSER=$operator '$site' provision alpha --name 'Alpha School' --admin-mail admin@alpha.example.invalid >'$work/log/rerun.log' 2>&1"
check "re-running provision alpha keeps the installed engine (same schema)" eq "$(sql tt_alpha "SELECT oid FROM pg_namespace WHERE nspname = 'scheduler'")" "$oid_before"
check "list shows the engine layout" bash -c "PGUSER=$operator '$site' list | grep -Eq '^alpha +active +workspace '"

step "2. One job on each site (planner, E2E organization through the Entity API, worker through ancora-site.sh work)"
random_hex() { od -An -N"$1" -tx1 /dev/urandom | tr -d ' \n'; }
for s in alpha beta; do
  {
    sdrush "$s" role:create planner Planner
    sdrush "$s" role:perm:add planner 'view academic data,edit academic data,view ancora engine jobs,request ancora engine jobs'
    sdrush "$s" user:create "planner-$s" --mail="planner@$s.example.invalid" --password="$(random_hex 12)"
    sdrush "$s" user:role:add planner "planner-$s"
    sdrush "$s" ancora-academic:load-fixture --owner="planner-$s"
  } >"$work/log/setup-$s.log" 2>&1 || { cat "$work/log/setup-$s.log"; exit 1; }
  id=$(enqueue "$s")
  work_once "$s"
  check "$s: job $id succeeded in the workspace mode, 8/8 published" eq \
    "$(field "$s" "$id" '.state + "/" + .result.mode + "/" + (.result.placed | tostring) + "/" + (.result.input.publication.published | tostring)')" "succeeded/workspace/8/8"
  check "$s: the E2E digest from the placement entities = $expected" eq "$(digest "$s")" "$expected"
  check "$s: nothing of the run is left (no workspace schema, no job role)" eq "$(workspaces "$s")/$(job_roles "$s")" "0/0"
done

step "3. Isolation during a run (test-only slow-placement trigger in tt_alpha)"
sql tt_alpha "CREATE SCHEMA tst; CREATE TABLE tst.delay (seconds float8 NOT NULL); INSERT INTO tst.delay VALUES (0);
  CREATE SEQUENCE tst.started;
  CREATE FUNCTION tst.slow() RETURNS trigger LANGUAGE plpgsql AS \$\$
  BEGIN PERFORM nextval('tst.started'); PERFORM pg_sleep((SELECT seconds FROM tst.delay)); RETURN NULL; END \$\$;
  GRANT USAGE ON SCHEMA tst TO tt_alpha_jobs; GRANT SELECT ON tst.delay TO tt_alpha_jobs; GRANT USAGE, UPDATE ON SEQUENCE tst.started TO tt_alpha_jobs;
  CREATE TRIGGER tst_slow AFTER INSERT ON scheduler.actividad FOR EACH ROW EXECUTE FUNCTION tst.slow();" >/dev/null
delay() { sql tt_alpha "UPDATE tst.delay SET seconds = $1" >/dev/null; }
started_count() { sql tt_alpha "SELECT CASE WHEN is_called THEN last_value ELSE 0 END FROM tst.started"; }
wait_started() { local target=$(( $1 + mark )) deadline=$((SECONDS + 90)); while [ "$(started_count)" -lt "$target" ]; do [ $SECONDS -lt $deadline ] || return 1; sleep 0.1; done; }
clear_placements() { sdrush "$1" php:eval '$s = \Drupal::entityTypeManager()->getStorage("ancora_placement"); $s->delete($s->loadMultiple());' >/dev/null; }
clear_placements alpha
delay 0.4; mark=$(started_count)
id=$(enqueue alpha)
"$site" work alpha --once >"$work/log/w-iso.log" 2>&1 & wpid=$!; bg+=("$wpid")
wait_started 2 || bad "the run never reached 2 placements"
jr=tt_alpha_job_${id}_1
check "the run's job role $jr exists, cannot log in and is a member of tt_alpha_jobs only" eq \
  "$(sql postgres "SELECT r.rolcanlogin || '/' || string_agg(g.rolname, ',') FROM pg_roles r JOIN pg_auth_members m ON m.member = r.oid JOIN pg_roles g ON g.oid = m.roleid WHERE r.rolname = '$jr' GROUP BY r.rolcanlogin")" "false/tt_alpha_jobs"
check "the job role cannot log in" refused "$jr" tt_alpha "SELECT 1"
check "beta's worker role cannot connect to tt_alpha" refused tt_beta_engine_app tt_alpha "SELECT 1"
check "beta's runtime role cannot connect to tt_alpha" refused tt_beta_app tt_alpha "SELECT 1"
check "beta's worker role cannot SET ROLE to alpha's job role" refused tt_beta_engine_app tt_beta "SET ROLE $jr"
check "alpha's runtime role cannot SET ROLE to the job role" refused tt_alpha_app tt_alpha "SET ROLE $jr"
check "alpha's runtime role cannot read the workspace" refused tt_alpha_app tt_alpha "SELECT count(*) FROM scheduler_job_${id}_1.actividad"
check "alpha's worker role cannot read the workspace without SET ROLE" refused tt_alpha_engine_app tt_alpha "SELECT count(*) FROM scheduler_job_${id}_1.actividad"
check "beta's job group has no privilege on tt_alpha" eq "$(sql postgres "SELECT has_database_privilege('tt_beta_jobs', 'tt_alpha', 'CONNECT') OR has_database_privilege('tt_beta_jobs', 'tt_alpha', 'TEMPORARY')")" f
check "alpha's job group has no privilege on tt_beta or tt_control" eq "$(sql postgres "SELECT bool_or(has_database_privilege('tt_alpha_jobs', d, 'CONNECT')) FROM unnest(ARRAY['tt_beta', 'tt_control']) d")" f
wait "$wpid" || true
delay 0
check "the isolated run then finished: 8/8 and the E2E digest" eq "$(field alpha "$id" .state)/$(count alpha)/$(digest alpha)" "succeeded/8/$expected"

step "4. Recovery: a killed worker, an expired kept workspace"
clear_placements alpha
delay 0.4; mark=$(started_count)
id=$(enqueue alpha)
"$site" work alpha --once >"$work/log/w-kill.log" 2>&1 & wpid=$!; bg+=("$wpid")
wait_started 2 || bad "the run never reached 2 placements"
pkill -9 -f "ancora-engine:work" || true
wait "$wpid" 2>/dev/null || true
delay 0
check "the killed run published nothing; its workspace is still there" eq "$(count alpha)/$(workspaces alpha)" "0/1"
sleep 4
work_once alpha
check "job $id was recovered; its next attempt published 8 once" eq "$(field alpha "$id" '.state + "/" + (.attempts | tostring)')/$(count alpha)/$(digest alpha)" "succeeded/2/8/$expected"
check "the lost attempt's workspace and job role were dropped" eq "$(workspaces alpha)/$(job_roles alpha)" "0/0"
id=$(enqueue alpha)
ANCORA_ENGINE_WORKSPACE_RETENTION=2 work_once alpha
check "a workspace kept for diagnosis (retention 2 s) is there after its run" eq "$(workspaces alpha)/$(sql tt_alpha "SELECT state FROM scheduler_control.workspace WHERE job = '${id}_1'")" "1/kept"
sleep 3
id2=$(enqueue alpha)
work_once alpha
check "the next worker cycle dropped the expired workspace and its job role" eq "$(workspaces alpha)/$(job_roles alpha)/$(field alpha "$id2" .state)" "0/0/succeeded"

step "5. Provisioning recovery, reserved slugs, deprovisioning"
check "provision gamma, stopping after the database step (test hook), exits non-zero" bash -c "! ANCORA_PROVISION_TEST_FAIL_AFTER=database PGUSER=$operator '$site' provision gamma --name Gamma --admin-mail admin@gamma.example.invalid >'$work/log/gamma-fail.log' 2>&1"
check "gamma's engine was installed before the stop" eq "$(sql tt_gamma "SELECT to_regclass('scheduler_control.workspace') IS NOT NULL")" t
check "re-running provision gamma recovers" bash -c "PGUSER=$operator '$site' provision gamma --name Gamma --admin-mail admin@gamma.example.invalid >'$work/log/gamma.log' 2>&1"
check "a new slug ending in -engine is refused (it would share tt_<slug>_engine names)" bash -c "! PGUSER=$operator '$site' provision alpha-engine --name x --admin-mail x@example.invalid >/dev/null 2>&1"
sql tt_gamma "SELECT 1" >/dev/null
psql -X -At -U tt_gamma_engine_app -d tt_gamma -c "SELECT schema_name FROM scheduler_control.open_job_workspace('99_1')" >/dev/null
check "a workspace of gamma that was never closed has its job role" eq "$(job_roles gamma)" 1
site_op suspend gamma >/dev/null 2>&1
check "deprovision gamma (suspended) with a final backup" bash -c "PGUSER=$operator '$site' deprovision gamma --backup-dir '$work/backup' --confirm gamma >'$work/log/deprovision.log' 2>&1"
check "gamma's database and every role of gamma are gone" eq \
  "$(sql postgres "SELECT (SELECT count(*) FROM pg_database WHERE datname LIKE 'tt\_gamma%') || '/' || (SELECT count(*) FROM pg_roles WHERE rolname LIKE 'tt\_gamma\_%')")" "0/0"

step "tenant verification: $pass passed, $fail failed ($((SECONDS - started)) s)"
[ "$fail" -eq 0 ]
