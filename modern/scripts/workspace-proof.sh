#!/usr/bin/env bash
# Single-database, job-scoped SQL workspace: disposable proof (issue #93).
#
#   dev/sql/tools/with-ephemeral-pg.sh modern/scripts/workspace-proof.sh
#
# Test and prototype code only (docs/modernization/single-db-workspace.md).
# It never touches a persistent server: every database tool checks the
# disposable identity of the ephemeral cluster first. Synthetic data only.
#
# One tenant database ("tenant") holds a stand-in of Drupal's canonical tables
# (schema public, owned by the site role), the unchanged legacy engine
# (schema scheduler and the legacy schemas, dev/workspace/install.sh)
# and one workspace schema per job, owned by that job's login role.
# Legacy oracles are full legacy databases installed with dev/sql/install.sh.
#
# Sections (one PASS/FAIL line per check, METRIC/TIMING lines are reported):
#   1. schema-resolution model and privileges
#   2. E2E organization: workspace = legacy oracle (placements, inserted rows,
#      DML counters, E2E digest 4199818b...)
#   3. closure world (#92): two creators, one creator with two plans, a teacher
#      shared by two specialties, partial run then continuation; each job in
#      its own workspace with the #92 closure, = the full legacy database
#   4. concurrency: two jobs at once on different scopes and on the same scope
#   5. cancellation from another connection, statement_timeout, killed backend,
#      cleanup and recovery, lifecycle
#   6. least privilege between jobs, Drupal tables untouched
#   7. the TEMP alternative: where it breaks and what it costs
#   8. measurements against the two-database layout
# Exit status 0 only if every check passes.
set -uo pipefail
here=$(cd "$(dirname "$0")" && pwd)
repo=$(cd "$here/../.." && pwd)
. "$repo/dev/sql/tools/disposable-guard.sh"
agoh_require_disposable || exit 3
lib=$repo/dev/workspace
work=$(mktemp -d "${TMPDIR:-/tmp}/agoh-ws.XXXXXX")
trap '[ "${WS_PROOF_KEEP:-0}" = 1 ] && echo "kept $work" || rm -rf "$work"' EXIT
DELAY=${WS_PROOF_DELAY:-0.3}
export PGOPTIONS="-c client_min_messages=warning"
expected=$(grep -v '^#' "$repo/dev/e2e/expected-placements.md5" | head -1)

pass=0; fail=0
ok()  { pass=$((pass + 1)); printf 'PASS  %s\n' "$1"; }
bad() { fail=$((fail + 1)); printf 'FAIL  %s\n' "$1"; }
check() { local d=$1; shift; if "$@"; then ok "$d"; else bad "$d"; fi; }
eq() { [ "$1" = "$2" ]; }
now() { date +%s.%N; }
ms() { awk -v s="$1" -v e="$2" 'BEGIN { printf "%d", (e - s) * 1000 }'; }
q() { psql -X -At -v ON_ERROR_STOP=1 -d "$1" -c "$2"; }               # as the cluster superuser
path_of() { echo "scheduler_job_$1,scheduler,pg_temp"; }
qj() { # job, sql: as the job's role, with the workspace search_path
  PGOPTIONS="-c search_path=$(path_of "$1") -c client_min_messages=warning ${QJ_OPTS:-}" \
    psql -X -At -v ON_ERROR_STOP=1 -v VERBOSITY=verbose -U "agoh_job_$1" -d tenant -c "$2"
}
qe() { # sql: as the superuser with the engine code in the path (the legacy
  # functions of tiempo, matrix, ... call unqualified engine functions)
  PGOPTIONS="-c search_path=scheduler -c client_min_messages=warning" psql -X -At -v ON_ERROR_STOP=1 -d tenant -c "$1"
}
fj() { # job, file
  PGOPTIONS="-c search_path=$(path_of "$1") -c client_min_messages=warning" \
    psql -X -q -At -v ON_ERROR_STOP=1 -U "agoh_job_$1" -d tenant -f "$2" >/dev/null
}
sqlstate_of() { sed -nE 's/.*(ERROR|FATAL): +([0-9A-Z]{5}):.*/\2/p' "$1" | head -1; }
state_of() { q tenant "SELECT state FROM scheduler_control.workspace WHERE job = '$1'"; }
set_state() { q tenant "UPDATE scheduler_control.workspace SET state = '$2', updated_at = clock_timestamp() WHERE job = '$1'" >/dev/null; }
new_job() { # job
  q tenant "CREATE ROLE agoh_job_$1 LOGIN IN ROLE tenant_jobs; GRANT CONNECT ON DATABASE tenant TO agoh_job_$1" >/dev/null
  q tenant "SELECT scheduler_control.create_workspace('$1', 'scheduler_job_$1', 'agoh_job_$1')" >/dev/null
}
GEN_E2E="'E', 'N', 'PER', 1, false, 0, false, false"
e2e_digest() { # job
  qj "$1" "SELECT wsproof.e2e_list('E', 'N', 'PER', '')" | tr -d '\n' | md5sum | cut -d' ' -f1
}
load_e2e() { # job
  fj "$1" "$repo/dev/e2e/fixture.sql" && fj "$1" "$repo/dev/e2e/fixture-http-steps.sql"
}

# ======================================================================= setup
echo "# setup: tenant database with Drupal stand-in tables, engine and workspace layer"
t0=$(now)
q postgres "CREATE ROLE ancora_site LOGIN" >/dev/null
q postgres "CREATE DATABASE tenant OWNER ancora_site" >/dev/null
q tenant "REVOKE ALL ON DATABASE tenant FROM PUBLIC; REVOKE CREATE ON SCHEMA public FROM PUBLIC" >/dev/null
PGOPTIONS="-c client_min_messages=warning" psql -X -q -v ON_ERROR_STOP=1 -U ancora_site -d tenant <<'SQL' || exit 2
-- Stand-ins for Drupal's canonical entity tables (the Drupal-sourced path is
-- parity-diff.sh --workspace): owned by the site role, in schema public.
CREATE TABLE ancora_plan (id serial PRIMARY KEY, uid int NOT NULL, code varchar NOT NULL, label varchar NOT NULL);
CREATE TABLE ancora_teacher (id serial PRIMARY KEY, uid int NOT NULL, code varchar NOT NULL, label varchar NOT NULL);
INSERT INTO ancora_plan (uid, code, label) VALUES (1, 'P', 'Plan');
INSERT INTO ancora_teacher (uid, code, label) VALUES (1, 'TA', 'Teacher Alpha'), (1, 'TBETA', 'Teacher Beta');
SQL
"$lib/install.sh" tenant >"$work/install.log" 2>&1 || { cat "$work/install.log"; exit 2; }
t_install=$(ms "$t0" "$(now)")
echo "TIMING engine install into the tenant database ${t_install} ms"
qe "INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena) VALUES ('admin-superuser', 'admin', '-', 'admin@example.invalid', 'admin', '!unusable')" >/dev/null || exit 2
drupal_sum() { q tenant "SELECT md5(string_agg(t || ':' || r, ',' ORDER BY t, r)) FROM (SELECT 'plan' t, p::text r FROM ancora_plan p UNION ALL SELECT 'teacher', x::text FROM ancora_teacher x) s"; }
drupal_before=$(drupal_sum)

echo "# setup: legacy oracle (full legacy database, dev/sql/install.sh --with-data)"
"$repo/dev/sql/install.sh" --with-data oracle_tpl >/dev/null 2>&1 || exit 2
PGOPTIONS="-c client_min_messages=warning" psql -X -q -v ON_ERROR_STOP=1 -d oracle_tpl -f "$lib/measure.sql" >/dev/null || exit 2
oracle() { # name -> fresh copy of the oracle template
  q postgres "CREATE DATABASE $1 TEMPLATE oracle_tpl" >/dev/null; echo "$1"
}

# =================================================== 1. schema-resolution model
echo "# 1. schema-resolution model"
new_job 1
check "the workspace has the 54 legacy tables, 2 views, 26 RULEs, 12 triggers, 134 FKs and 12 inheritance links" test \
  "$(q tenant "SELECT (SELECT count(*) FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace WHERE n.nspname = 'scheduler_job_1' AND relkind = 'r') || '/' ||
                      (SELECT count(*) FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace WHERE n.nspname = 'scheduler_job_1' AND relkind = 'v') || '/' ||
                      (SELECT count(*) FROM pg_rewrite w JOIN pg_class c ON c.oid = w.ev_class JOIN pg_namespace n ON n.oid = c.relnamespace WHERE n.nspname = 'scheduler_job_1' AND rulename <> '_RETURN') || '/' ||
                      (SELECT count(*) FROM pg_trigger g JOIN pg_class c ON c.oid = g.tgrelid JOIN pg_namespace n ON n.oid = c.relnamespace WHERE n.nspname = 'scheduler_job_1' AND NOT tgisinternal) || '/' ||
                      (SELECT count(*) FROM pg_constraint k JOIN pg_class c ON c.oid = k.conrelid JOIN pg_namespace n ON n.oid = c.relnamespace WHERE n.nspname = 'scheduler_job_1' AND contype = 'f') || '/' ||
                      (SELECT count(*) FROM pg_inherits i JOIN pg_class c ON c.oid = i.inhrelid JOIN pg_namespace n ON n.oid = c.relnamespace WHERE n.nspname = 'scheduler_job_1')")" = "54/2/26/12/134/12"
check "no function, type or sequence is copied into the workspace" test \
  "$(q tenant "SELECT (SELECT count(*) FROM pg_proc p JOIN pg_namespace n ON n.oid = p.pronamespace WHERE n.nspname = 'scheduler_job_1')
               + (SELECT count(*) FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace WHERE n.nspname = 'scheduler_job_1' AND t.typtype = 'c' AND t.typrelid = 0)
               + (SELECT count(*) FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace WHERE n.nspname = 'scheduler_job_1' AND relkind = 'S')")" = 0
# Byte identity of the engine code: every function of the legacy schemas in the
# tenant has the same name, arguments and source as in a database installed
# by dev/sql/install.sh (where the unqualified objects are in public).
code_sum() { # db, schema of the unqualified objects
  PGOPTIONS="-c search_path=$2 -c client_min_messages=warning" q "$1" "SELECT md5(string_agg(CASE n.nspname WHEN '$2' THEN '~' ELSE n.nspname END || '.' || p.proname || '(' || pg_get_function_identity_arguments(p.oid) || ')' || md5(p.prosrc), ',' ORDER BY 1))
            FROM pg_proc p JOIN pg_namespace n ON n.oid = p.pronamespace
           WHERE n.nspname IN ('$2', 'matrix', 'prioriza', 'tiempo', 'seguridad', 'generador', 'portal')"
}
check "the engine functions are byte-identical to dev/sql/install.sh (name, arguments, source of $(q tenant "SELECT count(*) FROM pg_proc p JOIN pg_namespace n ON n.oid = p.pronamespace WHERE n.nspname IN ('scheduler','matrix','prioriza','tiempo','seguridad','generador','portal')") functions)" \
  eq "$(code_sum tenant scheduler)" "$(code_sum oracle_tpl public)"
check "the job's search_path is workspace, engine code, pg_temp (pg_temp last)" \
  eq "$(qj 1 'SHOW search_path')" "scheduler_job_1,scheduler,pg_temp"
check "PUBLIC has CREATE on no schema of the path, nor on public" test \
  "$(q tenant "SELECT count(*) FROM unnest(ARRAY['scheduler_job_1', 'scheduler', 'public', 'matrix', 'prioriza', 'tiempo', 'generador', 'seguridad']) s WHERE has_schema_privilege('public', s, 'CREATE')")" = 0
check "only the job role (and superusers) can create in its workspace" test \
  "$(q tenant "SELECT has_schema_privilege('agoh_job_1', 'scheduler_job_1', 'CREATE') AND NOT has_schema_privilege('tenant_jobs', 'scheduler_job_1', 'USAGE') AND NOT has_schema_privilege('agoh_job_1', 'scheduler', 'CREATE') AND (SELECT nspowner::regrole::text FROM pg_namespace WHERE nspname = 'scheduler_job_1') = 'agoh_job_1'")" = t
check "the job role has no privilege on any table of the template schema" test \
  "$(q tenant "SELECT count(*) FROM pg_tables WHERE schemaname = 'scheduler' AND (has_table_privilege('agoh_job_1', schemaname || '.' || tablename, 'SELECT') OR has_table_privilege('agoh_job_1', schemaname || '.' || tablename, 'INSERT,UPDATE,DELETE,TRUNCATE'))")" = 0
qj 1 "SELECT count(*) FROM scheduler.actividad" >/dev/null 2>"$work/guard.err"
check "the guard is armed: reading a template table as the job role fails (42501)" eq "$(sqlstate_of "$work/guard.err")" 42501
# Bindings: the workspace views, RULE actions, triggers and FKs reference only
# workspace tables (and the tenant-level seguridad.usuario).
check "views and RULEs of the workspace depend on workspace tables only" test \
  "$(q tenant "SELECT count(*) FROM pg_depend d JOIN pg_rewrite w ON d.classid = 'pg_rewrite'::regclass AND d.objid = w.oid
                 JOIN pg_class e ON e.oid = w.ev_class JOIN pg_class c ON c.oid = d.refobjid AND d.refclassid = 'pg_class'::regclass
                WHERE e.relnamespace = 'scheduler_job_1'::regnamespace AND c.relnamespace <> 'scheduler_job_1'::regnamespace")" = 0
rule_fn=$(q tenant "SELECT count(*) FROM pg_depend d JOIN pg_rewrite w ON d.classid = 'pg_rewrite'::regclass AND d.objid = w.oid
                 JOIN pg_class e ON e.oid = w.ev_class JOIN pg_proc p ON p.oid = d.refobjid AND d.refclassid = 'pg_proc'::regclass
                WHERE e.relnamespace = 'scheduler_job_1'::regnamespace AND p.pronamespace <> 'scheduler'::regnamespace")
trig_fn=$(q tenant "SELECT count(*) FROM pg_trigger g JOIN pg_class e ON e.oid = g.tgrelid JOIN pg_proc p ON p.oid = g.tgfoid
                WHERE e.relnamespace = 'scheduler_job_1'::regnamespace AND NOT g.tgisinternal AND p.pronamespace <> 'scheduler'::regnamespace")
check "the RULE and trigger functions are the engine's (schema scheduler)" test "$rule_fn/$trig_fn" = "0/0"
check "every FK of the workspace references a workspace table or seguridad.usuario" test \
  "$(q tenant "SELECT count(*) FROM pg_constraint k JOIN pg_class c ON c.oid = k.conrelid JOIN pg_class f ON f.oid = k.confrelid
                WHERE k.contype = 'f' AND c.relnamespace = 'scheduler_job_1'::regnamespace
                  AND f.relnamespace <> 'scheduler_job_1'::regnamespace AND f.oid <> 'seguridad.usuario'::regclass")" = 0
check "generador.cancelar is a per-job view over scheduler_control.cancel_request" \
  eq "$(q tenant "SELECT relkind FROM pg_class WHERE oid = 'generador.cancelar'::regclass")" v

# ================================================================ 2. E2E fixture
echo "# 2. E2E organization: workspace against the legacy oracle"
t0=$(now); load_e2e 1; t_load=$(ms "$t0" "$(now)")
set_state 1 loaded
t0=$(now); qj 1 "SELECT scheduler_control.ws_analyze()" >/dev/null; t_an=$(ms "$t0" "$(now)")
set_state 1 running
t0=$(now); ws_out=$(qj 1 "SELECT wsproof.run($GEN_E2E)"); t_run=$(ms "$t0" "$(now)")
set_state 1 ran
db=$(oracle oracle_e2e)
for f in dev/e2e/fixture.sql dev/e2e/fixture-http-steps.sql; do psql -X -q -v ON_ERROR_STOP=1 -d "$db" -f "$repo/$f" >/dev/null; done
t0=$(now); or_out=$(q "$db" "SELECT wsproof.run($GEN_E2E)"); t_orun=$(ms "$t0" "$(now)")
echo "METRIC e2e workspace $(jq -c '{metrics, dml}' <<<"$ws_out")"
echo "TIMING e2e load ${t_load} ms, analyze ${t_an} ms, run ${t_run} ms (oracle run ${t_orun} ms)"
check "E2E: 8/8 placed, no collision" eq "$(jq -r .metrics <<<"$ws_out")" "demands=8 placed=8 unplaced=0 collisions=0 overflow=0"
check "E2E: workspace digest is $expected" eq "$(e2e_digest 1)" "$expected"
check "E2E: placements = legacy oracle" eq "$(jq -c .placements <<<"$ws_out")" "$(jq -c .placements <<<"$or_out")"
check "E2E: inserted rows = legacy oracle ($(jq '.inserted | length' <<<"$ws_out") rows)" eq "$(jq -c .inserted <<<"$ws_out")" "$(jq -c .inserted <<<"$or_out")"
check "E2E: DML counters per table = legacy oracle" eq "$(jq -cS .dml <<<"$ws_out")" "$(jq -cS .dml <<<"$or_out")"
check "E2E: no row reached the template tables" test \
  "$(q tenant "SELECT coalesce(sum(n_tup_ins + n_tup_upd + n_tup_del), 0) FROM pg_stat_user_tables WHERE schemaname = 'scheduler'")" = 0

set_state 1 kept

# ==================================================== 3. closure world (#92)
echo "# 3. closure world: one workspace per job, input = #92 closure, = full legacy database"
world=$work/world.sql
# The tenant's engine users are shared: the fixture's user rows are entered once.
sed '/^INSERT INTO seguridad.usuario/,/;$/d' "$repo/dev/sql/tests/fixtures/closure_world.sql" >"$world"
qe "INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena) VALUES
      ('u1', 'Alice', '-', 'alice@example.org', 'alice', '!unusable'), ('u2', 'Bob', '-', 'bob@example.org', 'bob', '!unusable')" >/dev/null || exit 2
run_job() { # job, buscalugar, scope args (esp, niv, per), generar options..., input files...
  local job=$1 bl=$2 scope=$3 gen=$4; shift 4
  local t0 rows kept pruned out
  t0=$(now); new_job "$job"; local t_create; t_create=$(ms "$t0" "$(now)")
  t0=$(now)
  for f in "$@"; do fj "$job" "$f" || { bad "job $job: input $f"; return 1; }; done
  qj "$job" "SELECT scheduler_control.load_stored_placements()" >/dev/null
  rows=$(qj "$job" "SELECT scheduler_control.ws_rows()")
  local t_input; t_input=$(ms "$t0" "$(now)")
  t0=$(now)
  read -r kept pruned < <(qj "$job" "SELECT scheduler_control.closure_keep($scope, $bl); SELECT scheduler_control.prune();" | tr '\n' ' ')
  local t_closure; t_closure=$(ms "$t0" "$(now)")
  t0=$(now); qj "$job" "SELECT scheduler_control.ws_analyze()" >/dev/null; local t_an; t_an=$(ms "$t0" "$(now)")
  set_state "$job" running
  t0=$(now); out=$(qj "$job" "SELECT wsproof.run($scope, $gen)" 2>"$work/job$job.err") || out="ERROR $(sqlstate_of "$work/job$job.err")"
  local t_run; t_run=$(ms "$t0" "$(now)")
  set_state "$job" ran
  q tenant "SELECT scheduler_control.store_output('$job')" >/dev/null
  printf '%s\n' "$out" >"$work/job$job.json"
  local size; size=$(q tenant "SELECT coalesce(sum(pg_total_relation_size(c.oid)), 0) FROM pg_class c WHERE c.relnamespace = 'scheduler_job_$job'::regnamespace AND c.relkind = 'r'")
  echo "METRIC job $job: input rows $rows, closure $kept rows kept ($pruned pruned), workspace tables ${size} bytes"
  echo "TIMING job $job: create ${t_create} ms, input ${t_input} ms, closure+prune ${t_closure} ms, analyze ${t_an} ms, run ${t_run} ms"
}
oracle_run() { # db, scope, options -> outcome JSON
  q "$1" "SELECT wsproof.run($2, $3)" 2>"$work/oracle.err" || echo "ERROR $(sqlstate_of "$work/oracle.err")"
}
same_outcome() { # label, workspace json file, oracle json
  local w; w=$(cat "$2")
  check "$1: placements = oracle" eq "$(jq -c .placements <<<"$w" 2>/dev/null)" "$(jq -c .placements <<<"$3" 2>/dev/null)"
  check "$1: inserted rows = oracle ($(jq '.inserted | length' <<<"$w" 2>/dev/null) rows)" eq "$(jq -c .inserted <<<"$w" 2>/dev/null)" "$(jq -c .inserted <<<"$3" 2>/dev/null)"
  check "$1: DML counters = oracle" eq "$(jq -cS .dml <<<"$w" 2>/dev/null)" "$(jq -cS .dml <<<"$3" 2>/dev/null)"
  check "$1: metrics = oracle ($(jq -r .metrics <<<"$3" 2>/dev/null))" eq "$(jq -r .metrics <<<"$w" 2>/dev/null)" "$(jq -r .metrics <<<"$3" 2>/dev/null)"
}
odb=$(oracle oracle_world)
psql -X -q -v ON_ERROR_STOP=1 -d "$odb" -f "$repo/dev/sql/tests/fixtures/closure_world.sql" >/dev/null || exit 2
GEN0="1, false, 0, false, false"
q tenant "TRUNCATE scheduler_control.placement_store, scheduler_control.resource_store" >/dev/null
i=10
for scope in "'EB', 'NB', 'QB'" "'E', 'N', 'QER'" "'F', 'N', 'PER'" "'E', 'N', 'PER'"; do
  run_job "$i" false "$scope" "$GEN0" "$world"
  same_outcome "world job $i ($scope)" "$work/job$i.json" "$(oracle_run "$odb" "$scope" "$GEN0")"
  i=$((i + 1))
done
check "world: Bob's job kept no row of Alice and Alice's PER job none of Bob" test \
  "$(q tenant "SELECT (SELECT count(*) FROM scheduler_job_10.planificacion WHERE creador = 'u1') + (SELECT count(*) FROM scheduler_job_13.planificacion WHERE creador = 'u2')")" = 0
check "world: the PER job received the placements of the earlier jobs it needs (shared teacher TA, room usage in QER)" test \
  "$(q tenant "SELECT (SELECT count(*) FROM scheduler_job_13.actividad WHERE plantilla = 'U1') || '/' || (SELECT count(*) FROM scheduler_job_13.actividad WHERE plantilla = 'TQ')")" = "1/3"
# The same request with the legacy window's default options (compactar 1,
# buscalugar true): job 13's output is withdrawn from the store, the oracle's
# T1/T3 placements are deleted, both run again.
q tenant "DELETE FROM scheduler_control.placement_store WHERE job = '13'" >/dev/null
run_job 14 true "'E', 'N', 'PER'" "1, false, 1, false, true" "$world"
same_outcome "world job 14 ('E', 'N', 'PER', legacy window options)" "$work/job14.json" \
  "$(psql -X -q -At -v ON_ERROR_STOP=1 -d "$odb" -c "BEGIN; DELETE FROM actividad WHERE plantilla IN ('T1', 'T3'); SELECT wsproof.run('E', 'N', 'PER', 1, false, 1, false, true); ROLLBACK;")"

echo "# 3b. partial run, then continuation in a new job"
restrict=$work/restrict-b2.sql
cat >"$restrict" <<'SQL'
INSERT INTO restriccion_de_brigada (id, brigada, periodo, dia, turno, valor, por_actividad)
     SELECT 'RB2' || d || t, 'B2', 'PER', d, t, 1, false FROM generate_series(1, 5) d, generate_series(1, 4) t;
SQL
q tenant "TRUNCATE scheduler_control.placement_store, scheduler_control.resource_store" >/dev/null
run_job 20 false "'E', 'N', 'PER'" "$GEN0" "$repo/dev/e2e/fixture.sql" "$repo/dev/e2e/fixture-http-steps.sql" "$restrict"
run_job 21 false "'E', 'N', 'PER'" "$GEN0" "$repo/dev/e2e/fixture.sql" "$repo/dev/e2e/fixture-http-steps.sql"
odb=$(oracle oracle_cont)
for f in dev/e2e/fixture.sql dev/e2e/fixture-http-steps.sql; do psql -X -q -v ON_ERROR_STOP=1 -d "$odb" -f "$repo/$f" >/dev/null; done
psql -X -q -v ON_ERROR_STOP=1 -d "$odb" -f "$restrict" >/dev/null
o1=$(oracle_run "$odb" "'E', 'N', 'PER'" "$GEN0")
q "$odb" "DELETE FROM restriccion_de_brigada WHERE id LIKE 'RB2%'" >/dev/null
o2=$(oracle_run "$odb" "'E', 'N', 'PER'" "$GEN0")
same_outcome "partial job 20" "$work/job20.json" "$o1"
same_outcome "continuation job 21" "$work/job21.json" "$o2"
check "partial: 4 of 8 placed" eq "$(jq -r .metrics "$work/job20.json")" "demands=8 placed=4 unplaced=4 collisions=0 overflow=0"
check "continuation: job 21 kept job 20's placements unchanged (ids, cells) and placed the other 4" test \
  "$(q tenant "SELECT (SELECT count(*) FROM scheduler_job_21.actividad a JOIN scheduler_control.placement_store s USING (id, plantilla, grupo, dia, turno) WHERE s.job = '20') || '/' || (SELECT count(*) FROM scheduler_job_21.actividad)")" = "4/8"
check "continuation: the continued timetable equals the oracle's (E2E list $(e2e_digest 21))" \
  eq "$(e2e_digest 21)" "$(q "$odb" "SELECT wsproof.e2e_list('E', 'N', 'PER', '')" | tr -d '\n' | md5sum | cut -d' ' -f1)"

for j in 10 11 12 13 14 20 21; do set_state "$j" ran; done

# ============================================================ 4. concurrency
echo "# 4. concurrency: two jobs at once (slow-placement trigger, ${DELAY}s per placement, test only)"
q tenant "CREATE SCHEMA wsproof_cc; GRANT USAGE ON SCHEMA wsproof_cc TO tenant_jobs;
  CREATE FUNCTION wsproof_cc.slow() RETURNS trigger LANGUAGE plpgsql AS \$\$
  BEGIN PERFORM nextval(TG_ARGV[0]); PERFORM pg_sleep(TG_ARGV[1]::float); RETURN NULL; END \$\$;" >/dev/null
q tenant "TRUNCATE scheduler_control.placement_store, scheduler_control.resource_store" >/dev/null
prep_slow() { # job, input files...: a workspace with the inputs and the slow trigger
  local job=$1; shift
  new_job "$job"
  for f in "$@"; do fj "$job" "$f"; done
  q tenant "CREATE SEQUENCE wsproof_cc.started_$job; GRANT USAGE ON SEQUENCE wsproof_cc.started_$job TO agoh_job_$job;
            CREATE TRIGGER wsproof_slow AFTER INSERT ON scheduler_job_$job.actividad FOR EACH ROW
            EXECUTE FUNCTION wsproof_cc.slow('wsproof_cc.started_$job', '$DELAY')" >/dev/null
  set_state "$job" loaded
}
started() { q tenant "SELECT CASE WHEN is_called THEN last_value ELSE 0 END FROM wsproof_cc.started_$1"; }
start_run() { # job, scope, extra PGOPTIONS: one autocommit statement in the background
  set_state "$1" running
  ( QJ_OPTS="${3:-}" qj "$1" "SELECT wsproof.run($2, $GEN0)" >"$work/run$1.out" 2>"$work/run$1.err"; echo $? >"$work/run$1.rc" ) &
  eval "bg_$1=\$!"
}
wait_run() { local p; eval "p=\$bg_$1"; wait "$p"; eval "rc$1=\$(cat \"\$work/run$1.rc\")"; }  # sets rc<job>
wait_started() { local deadline=$((SECONDS + 60)); while [ "$(started "$1")" -lt "$2" ]; do [ $SECONDS -lt $deadline ] || return 1; sleep 0.05; done; }
E2E_IN=("$repo/dev/e2e/fixture.sql" "$repo/dev/e2e/fixture-http-steps.sql")

# Different scopes: the E2E organization (E, N, PER) and the world's (F, N, PER).
prep_slow 30 "${E2E_IN[@]}"
prep_slow 31 "$world"
t0=$(now); start_run 30 "'E', 'N', 'PER'"; start_run 31 "'F', 'N', 'PER'"
wait_started 30 1; wait_started 31 1
check "different scopes: both jobs run at the same time" test "$(q tenant "SELECT count(*) FROM pg_stat_activity WHERE usename IN ('agoh_job_30', 'agoh_job_31') AND state = 'active'")" = 2
wait_run 30; wait_run 31; t_conc=$(ms "$t0" "$(now)")
set_state 30 ran; set_state 31 ran
echo "TIMING two concurrent jobs ${t_conc} ms"
check "different scopes: job 30 = the E2E oracle (digest $expected)" test "$rc30/$(e2e_digest 30)" = "0/$expected"
check "different scopes: job 30 inserted rows and DML = the E2E oracle" eq "$(jq -cS '{inserted, dml}' "$work/run30.out")" "$(jq -cS '{inserted, dml}' <<<"$or_out")"
odb=$(oracle oracle_f)
psql -X -q -v ON_ERROR_STOP=1 -d "$odb" -f "$repo/dev/sql/tests/fixtures/closure_world.sql" >/dev/null
check "different scopes: job 31 = the oracle of its scope" eq \
  "$(jq -cS '{placements, inserted, dml}' "$work/run31.out")" "$(jq -cS '{placements, inserted, dml}' <<<"$(oracle_run "$odb" "'F', 'N', 'PER'" "$GEN0")")"

# Same scope: two E2E jobs; job 32 is cancelled from another connection
# after 3 placements, job 33 is not.
prep_slow 32 "${E2E_IN[@]}"
prep_slow 33 "${E2E_IN[@]}"
start_run 32 "'E', 'N', 'PER'"; start_run 33 "'E', 'N', 'PER'"
wait_started 32 3 || bad "job 32 never reached 3 placements"
c_at=$(now)
q tenant "INSERT INTO scheduler_control.cancel_request (job_role, especialidad, nivel, periodo) VALUES ('agoh_job_32', 'E', 'N', 'PER')" >/dev/null
wait_run 32; latency=$(ms "$c_at" "$(now)")
wait_run 33
set_state 32 ran; set_state 33 ran
p32=$(q tenant "SELECT count(*) FROM scheduler_job_32.actividad")
echo "TIMING soft cancel latency ${latency} ms; job 32 kept $p32 placements"
check "same scope: the cancelled job returns normally" eq "$rc32" 0
check "same scope: the cancelled job keeps what it placed, committed (3 to 7 of 8)" test "$p32" -ge 3 -a "$p32" -le 7
check "same scope: the cancelled job consumed its own request (no request left)" eq "$(q tenant "SELECT count(*) FROM scheduler_control.cancel_request")" 0
check "same scope: the other job never saw the request and placed 8/8 (digest $expected)" test "$rc33/$(jq -r .cancel_consumed "$work/run33.out")/$(e2e_digest 33)" = "0/0/$expected"
check "same scope: the kept partial timetable has no collision" eq "$(qj 32 "SELECT wsproof.metrics('E', 'N', 'PER')")" "demands=8 placed=$p32 unplaced=$((8 - p32)) collisions=0 overflow=0"
# The legacy table is keyed by scope: a request meant for one run is consumed
# by whichever run of that scope checks first.
odb=$(oracle oracle_cancel)
q "$odb" "INSERT INTO generador.cancelar VALUES ('E', 'N', 'PER')" >/dev/null
check "legacy layout (contrast): another run of the same scope consumes the request" eq "$(q "$odb" "SELECT generador.fue_cancelado('E', 'N', 'PER')")" t

# ========================================== 5. timeout, kill, cleanup, lifecycle
echo "# 5. statement_timeout, killed backend, cleanup and recovery, lifecycle"
prep_slow 34 "${E2E_IN[@]}"
start_run 34 "'E', 'N', 'PER'" "-c statement_timeout=1s"
wait_run 34
check "statement_timeout: the run fails with 57014" eq "$(sqlstate_of "$work/run34.err")" 57014
check "statement_timeout: nothing is kept" eq "$(q tenant "SELECT count(*) FROM scheduler_job_34.actividad")" 0
prep_slow 35 "${E2E_IN[@]}"
start_run 35 "'E', 'N', 'PER'"
wait_started 35 2
q tenant "SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE usename = 'agoh_job_35'" >/dev/null
wait_run 35
check "killed backend: the client sees the connection end" test "$rc35" != 0
check "killed backend: nothing is kept, the job is left 'running'" test "$(q tenant "SELECT count(*) FROM scheduler_job_35.actividad")/$(state_of 35)" = "0/running"
set_state 34 failed
t0=$(now); dropped=$(q tenant "SELECT string_agg(j, ',' ORDER BY j) FROM scheduler_control.cleanup() j"); t_clean=$(ms "$t0" "$(now)")
echo "TIMING cleanup ${t_clean} ms (dropped: $dropped)"
check "cleanup drops the failed and the abandoned workspaces (34, 35), nothing else" eq "$dropped" "34,35"
check "cleanup: their schemas are gone, their state is 'dropped'" test \
  "$(q tenant "SELECT count(*) FROM pg_namespace WHERE nspname IN ('scheduler_job_34', 'scheduler_job_35')")/$(state_of 35)" = "0/dropped"
check "cleanup kept the workspace of a finished job (33) and the kept one (1)" test "$(state_of 33)/$(state_of 1)" = "ran/kept"
prep_slow 36 "${E2E_IN[@]}"
q tenant "DROP TRIGGER wsproof_slow ON scheduler_job_36.actividad" >/dev/null
start_run 36 "'E', 'N', 'PER'"; wait_run 36; set_state 36 ran
check "recovery: a new job from the same input gives 8/8 and the E2E digest" test "$rc36/$(e2e_digest 36)" = "0/$expected"
q tenant "UPDATE scheduler_control.workspace SET keep_until = clock_timestamp() + interval '1 hour' WHERE job = '1'" >/dev/null
check "lifecycle: a kept workspace survives cleanup before its retention ends" test -z "$(q tenant "SELECT string_agg(j, ',') FROM scheduler_control.cleanup() j")"
check "lifecycle: and is dropped after it" eq "$(q tenant "SELECT string_agg(j, ',') FROM scheduler_control.cleanup(clock_timestamp() + interval '2 hours') j")" 1
q tenant "INSERT INTO scheduler_control.cancel_request VALUES ('agoh_job_33', 'E', 'N', 'PER')" >/dev/null
q tenant "SELECT scheduler_control.drop_workspace('33')" >/dev/null
check "lifecycle: dropping a job also removes its cancellation rows and its schema" test \
  "$(q tenant "SELECT count(*) FROM scheduler_control.cancel_request")/$(q tenant "SELECT count(*) FROM pg_namespace WHERE nspname = 'scheduler_job_33'")/$(state_of 33)" = "0/0/dropped"

# ====================================================== 6. least privilege
echo "# 6. least privilege between jobs, Drupal tables untouched"
for stmt in "SELECT count(*) FROM scheduler_job_30.actividad" "INSERT INTO scheduler_job_30.lugar (id, nombre, capacidad, creador) VALUES ('X', 'x', 0, 'admin-superuser')" \
            "DROP TABLE scheduler_job_30.actividad" "CREATE TABLE scheduler_job_30.x (a int)" "SELECT count(*) FROM scheduler_control.cancel_request" \
            "SELECT count(*) FROM public.ancora_plan" "UPDATE public.ancora_teacher SET label = 'x'" "CREATE TABLE public.x (a int)" "CREATE TABLE scheduler.x (a int)"; do
  qj 31 "$stmt" >/dev/null 2>"$work/priv.err"
  check "job 31 is refused: $stmt" eq "$(sqlstate_of "$work/priv.err")" 42501
done
q tenant "INSERT INTO scheduler_control.cancel_request VALUES ('agoh_job_30', 'E', 'N', 'PER')" >/dev/null
check "job 31 does not see job 30's cancellation request" eq "$(qj 31 "SELECT count(*) FROM generador.cancelar")" 0
check "job 30 sees its own" eq "$(qj 30 "SELECT count(*) FROM generador.cancelar")" 1
check "no job role has a privilege on a Drupal table" test \
  "$(q tenant "SELECT count(*) FROM pg_tables t, pg_roles r WHERE t.schemaname = 'public' AND r.rolname LIKE 'agoh_job_%'
                AND (has_table_privilege(r.oid, t.schemaname || '.' || t.tablename, 'SELECT') OR has_table_privilege(r.oid, t.schemaname || '.' || t.tablename, 'INSERT,UPDATE,DELETE,TRUNCATE'))")" = 0
check "the Drupal tables are unchanged (checksum before and after every job)" eq "$(drupal_sum)" "$drupal_before"

# ===================================================== 7. the TEMP alternative
echo "# 7. the TEMP alternative"
# TEMP tables exist only in the session that creates them, so the job's own
# session must create them: it needs to read the template shapes (LIKE), which
# the workspace model never grants. Role agoh_job_40 gets that grant.
q tenant "CREATE ROLE agoh_job_40 LOGIN IN ROLE tenant_jobs" >/dev/null
qt() { PGOPTIONS="-c search_path=$1 -c client_min_messages=warning" psql -X -q -At -v ON_ERROR_STOP=1 -v VERBOSITY=verbose -U agoh_job_40 -d tenant; }
echo "CREATE TEMP TABLE zpriori (LIKE scheduler.zpriori);" | qt "pg_temp,scheduler" >/dev/null 2>"$work/t0.err"
check "TEMP: the job role cannot even copy the shapes without a read grant on the templates (42501)" eq "$(sqlstate_of "$work/t0.err")" 42501
q tenant "GRANT SELECT ON ALL TABLES IN SCHEMA scheduler TO agoh_job_40" >/dev/null
t1=$(qt "scheduler,pg_temp" 2>"$work/t1.err" <<'SQL'
CREATE TEMP TABLE zpriori (LIKE scheduler.zpriori INCLUDING ALL);
INSERT INTO pg_temp.zpriori (clasificacion, periodo, dia, turno, valor) VALUES ('C', 'P', 1, 1, 2);
SELECT count(*) FROM zpriori;
SELECT count(*) FROM pg_temp.zpriori;
SQL
)
check "TEMP with the safe path (pg_temp last): unqualified names silently read the shared template, not the TEMP table" eq "$(echo $t1)" "0 1"
qt "pg_temp,scheduler" >/dev/null 2>"$work/t2.err" <<'SQL'
CREATE TEMP TABLE planificacion (LIKE scheduler.planificacion INCLUDING ALL);
ALTER TABLE planificacion ADD FOREIGN KEY (creador) REFERENCES seguridad.usuario (id);
SQL
check "TEMP: a foreign key to seguridad.usuario is refused ($(sqlstate_of "$work/t2.err"))" test -n "$(sqlstate_of "$work/t2.err")"
# A full TEMP workspace (pg_temp FIRST, seguridad.usuario replaced by a TEMP
# copy), first without recreating the views: max_cant_act's view stays bound
# to the template tables.
temp_ws() { # recreate views: true or false
  # The definitions are printed before any TEMP table exists: afterwards the
  # TEMP tables shadow the templates and the printed names become qualified.
  cat <<SQL
DO \$\$
DECLARE r record; ddl text[] := '{}'; d text;
BEGIN
  PERFORM set_config('search_path', 'scheduler', true);
  FOR r IN SELECT t.relname AS tbl, c.conname, replace(pg_get_constraintdef(c.oid, true), 'seguridad.usuario', 'usuario') AS def
             FROM pg_constraint c JOIN scheduler_control.template_tables t ON t.oid = c.conrelid WHERE c.contype = 'f' ORDER BY c.oid LOOP
    ddl := ddl || format('ALTER TABLE pg_temp.%I ADD CONSTRAINT %I %s', r.tbl, r.conname, r.def);
  END LOOP;
  IF $1 THEN
    FOR r IN SELECT c.relname, pg_get_viewdef(c.oid, true) AS def FROM pg_class c WHERE c.relnamespace = 'scheduler'::regnamespace AND c.relkind = 'v' LOOP
      ddl := ddl || format('CREATE TEMP VIEW %I AS %s', r.relname, r.def);
    END LOOP;
  END IF;
  FOR r IN SELECT pg_get_ruledef(w.oid, true) AS def FROM pg_rewrite w JOIN scheduler_control.template_tables t ON t.oid = w.ev_class WHERE w.rulename <> '_RETURN' ORDER BY w.oid LOOP
    ddl := ddl || r.def;
  END LOOP;
  FOR r IN SELECT pg_get_triggerdef(g.oid, true) AS def FROM pg_trigger g JOIN scheduler_control.template_tables t ON t.oid = g.tgrelid WHERE NOT g.tgisinternal ORDER BY g.oid LOOP
    ddl := ddl || r.def;
  END LOOP;
  PERFORM set_config('search_path', 'pg_temp, scheduler', true);
  CREATE TEMP TABLE usuario (LIKE seguridad.usuario INCLUDING ALL);
  FOR r IN SELECT relname FROM scheduler_control.template_tables ORDER BY oid LOOP
    EXECUTE format('CREATE TEMP TABLE %I (LIKE scheduler.%I INCLUDING DEFAULTS INCLUDING CONSTRAINTS INCLUDING INDEXES)', r.relname, r.relname);
  END LOOP;
  FOR r IN SELECT c.relname AS child, p.relname AS parent FROM pg_inherits i JOIN pg_class c ON c.oid = i.inhrelid JOIN pg_class p ON p.oid = i.inhparent
            WHERE c.relnamespace = 'scheduler'::regnamespace ORDER BY c.oid LOOP
    EXECUTE format('ALTER TABLE pg_temp.%I INHERIT pg_temp.%I', r.child, r.parent);
  END LOOP;
  FOREACH d IN ARRAY ddl LOOP EXECUTE d; END LOOP;
END \$\$;
INSERT INTO usuario (id, nombre, info, correo, nick, contrasena) VALUES ('admin-superuser', 'admin', '-', 'admin@example.invalid', 'admin', '!unusable');
\i $repo/dev/e2e/fixture.sql
\i $repo/dev/e2e/fixture-http-steps.sql
SQL
}
{ temp_ws false; echo "SELECT wsproof.run($GEN_E2E);"; } | qt "pg_temp,scheduler" >/dev/null 2>"$work/t3.err"
check "TEMP without recreated views: max_cant_act reads the EMPTY shared template through its OID-bound view and the run fails (2202E)" eq "$(sqlstate_of "$work/t3.err")" 2202E
t0=$(now)
{ temp_ws true; echo "SELECT wsproof.run($GEN_E2E);"; echo "SELECT 'temp_schema|' || nspname FROM pg_namespace WHERE oid = pg_my_temp_schema();"; echo "SELECT pg_sleep(3);"; } \
  | qt "pg_temp,scheduler" >"$work/t4.out" 2>"$work/t4.err" &
tpid=$!
for _ in $(seq 1 400); do grep -q '^temp_schema' "$work/t4.out" 2>/dev/null && break; sleep 0.05; done
t_temp=$(ms "$t0" "$(now)")
tschema=$(sed -n 's/^temp_schema|//p' "$work/t4.out")
[ -n "$tschema" ] || bad "TEMP: the session did not report its temp schema"
psql -X -At -v VERBOSITY=verbose -d tenant -c "SELECT count(*) FROM $tschema.actividad" >/dev/null 2>"$work/t5.err"
wait "$tpid"
tout=$(grep '^{' "$work/t4.out")
check "TEMP with recreated views, FK to a TEMP user table and pg_temp first: the oracle's placements, inserted rows and DML" eq \
  "$(jq -cS '{placements, inserted, dml}' <<<"$tout")" "$(jq -cS '{placements, inserted, dml}' <<<"$or_out")"
check "TEMP: another connection (even a superuser) cannot read the job's output (0A000)" eq "$(sqlstate_of "$work/t5.err")" 0A000
echo "TIMING TEMP workspace in one session (DDL, input, run) ${t_temp} ms; its rows vanish with the session"

# ============================================================ 8. measurements
echo "# 8. measurements"
t0=$(now); "$repo/dev/sql/install.sh" twodb_engine >/dev/null 2>&1; t_two=$(ms "$t0" "$(now)")
t0=$(now); new_job 50; t_ws=$(ms "$t0" "$(now)")
echo "TIMING two-database layout: one engine database (dev/sql/install.sh) ${t_two} ms; single database: engine install into the tenant ${t_install} ms once, then ${t_ws} ms per workspace"
echo "METRIC sizes: empty engine database $(q postgres "SELECT pg_database_size('twodb_engine')") bytes; empty workspace tables $(q tenant "SELECT sum(pg_total_relation_size(c.oid)) FROM pg_class c WHERE c.relnamespace = 'scheduler_job_50'::regnamespace AND c.relkind = 'r'") bytes; tenant database with $(q tenant "SELECT count(*) FROM pg_namespace WHERE nspname LIKE 'scheduler_job_%'") workspaces $(q postgres "SELECT pg_database_size('tenant')") bytes"
echo "METRIC catalog: $(q tenant "SELECT count(*) FROM pg_class c WHERE c.relnamespace = 'scheduler_job_50'::regnamespace") relations per workspace"

echo "# summary: $pass pass, $fail fail"
[ "$fail" = 0 ]
