#!/usr/bin/env bash
# Mid-flight cancellation of generar() across two connections (issue #71).
#
#   dev/sql/tools/with-ephemeral-pg.sh dev/sql/tests/concurrency/cancel_midflight.sh
#
# Case 062 only shows that a generador.cancelar row that already exists when
# generar() starts stops it. This script proves what happens when the request
# arrives from ANOTHER connection WHILE generar() runs, in the transaction
# model of the engine worker (ancora_engine): one autocommit statement
# "SELECT generar(...)", isolation READ COMMITTED (the server default), a
# statement_timeout.
#
# Connection A runs generar() on the E2E organization (dev/e2e/fixture.sql
# plus fixture-http-steps.sql: 8 demands). A test-only trigger makes every
# placement slow (pg_sleep) and counts it in a sequence, which other sessions
# can read while A's transaction is still open (sequences are not
# transactional). Connection B waits on that sequence and then acts.
#
# Scenarios, each in a fresh copy of the template database:
#   full        no cancellation: 8/8 placed, the E2E digest, the full duration
#   soft-N      B INSERTs + COMMITs the generador.cancelar row after N
#               placements started (N = 1, 3, 5): generar() returns normally,
#               early, keeps the partial placements (committed), consumes the row
#   repeatable-read  the same soft cancel under REPEATABLE READ is never seen
#               (one snapshot for the whole statement): the worker must use
#               READ COMMITTED
#   cancel      B calls pg_cancel_backend(A): SQLSTATE 57014, nothing kept
#   terminate   B calls pg_terminate_backend(A): connection lost, nothing kept
#   timeout     statement_timeout expires mid-run: SQLSTATE 57014, nothing kept
#   late-row    a cancel row committed after generar() finished is NOT consumed
#               and stops the NEXT run at its first check (the worker clears
#               the scope's row before every run)
#   timeout-row a cancel row consumed by a run that then times out comes back
#               (the DELETE is rolled back with the run)
#
# Output: one PASS/FAIL line per check and "TIMING" lines. Exit 0 only if
# every check passes.
set -uo pipefail
here=$(cd "$(dirname "$0")" && pwd)
repo=$(cd "$here/../../../.." && pwd)
. "$repo/dev/sql/tools/disposable-guard.sh"
agoh_require_disposable || exit 3

DELAY=${CANCEL_TEST_DELAY:-0.5}   # seconds of pg_sleep per placement
SCOPE="'E', 'N', 'PER'"
GENERAR="SELECT generar($SCOPE, 1, false, 0, false, false)"
tpl="agoh_cc_tpl_$$"
work=$(mktemp -d "${TMPDIR:-/tmp}/agoh-cc.XXXXXX")
dbs=()
cleanup() {
  for d in "${dbs[@]}" "$tpl"; do dropdb --force --if-exists "$d" >/dev/null 2>&1; done
  rm -rf "$work"
}
trap cleanup EXIT

pass=0; fail=0
ok()  { pass=$((pass + 1)); printf 'PASS  %s\n' "$1"; }
bad() { fail=$((fail + 1)); printf 'FAIL  %s\n' "$1"; }
check() { local d=$1; shift; if "$@"; then ok "$d"; else bad "$d"; fi; }
q() { psql -X -At -v ON_ERROR_STOP=1 -d "$1" -c "$2"; }
now() { date +%s.%N; }
secs() { awk -v s="$1" -v e="$2" 'BEGIN { printf "%.2f", e - s }'; }
lt() { awk -v a="$1" -v b="$2" 'BEGIN { exit !(a < b) }'; }

echo "# template: schema + seed, E2E organization, slow-placement trigger (${DELAY}s)"
"$repo/dev/sql/install.sh" --with-data "$tpl" >"$work/install.log" 2>&1 || { cat "$work/install.log"; exit 2; }
for f in dev/e2e/fixture.sql dev/e2e/fixture-http-steps.sql; do
  psql -X -q -v ON_ERROR_STOP=1 -d "$tpl" -f "$repo/$f" >/dev/null || exit 2
done
psql -X -q -v ON_ERROR_STOP=1 -d "$tpl" >/dev/null <<SQL || exit 2
CREATE SCHEMA tst_cc;
CREATE SEQUENCE tst_cc.placements_started;
CREATE FUNCTION tst_cc.slow_placement() RETURNS trigger LANGUAGE plpgsql AS \$\$
BEGIN
  PERFORM nextval('tst_cc.placements_started');
  PERFORM pg_sleep($DELAY);
  RETURN NULL;
END \$\$;
CREATE TRIGGER tst_cc_slow_placement AFTER INSERT ON actividad FOR EACH ROW EXECUTE FUNCTION tst_cc.slow_placement();
SQL
check "server default isolation is READ COMMITTED (the worker's model)" \
  test "$(q "$tpl" 'SHOW default_transaction_isolation')" = "read committed"

fresh() { # name -> creates a copy of the template
  local d="agoh_cc_${1//-/_}_$$"
  createdb -T "$tpl" "$d" || exit 2
  dbs+=("$d"); echo "$d"
}
started() { q "$1" "SELECT CASE WHEN is_called THEN last_value ELSE 0 END FROM tst_cc.placements_started"; }
placed() { q "$1" "SELECT count(*) FROM actividad"; }
cancel_rows() { q "$1" "SELECT count(*) FROM generador.cancelar"; }
digest() {
  q "$1" "SELECT string_agg(a.plantilla || '/' || a.grupo || '/' || p.nombre || '/' || a.lugar || '/' || a.dia || '/' || a.turno, ',' ORDER BY a.plantilla, a.grupo) FROM actividad a JOIN profesor p ON p.id = a.profesor" \
    | tr -d '\n' | md5sum | cut -d' ' -f1
}
expected=$(grep -v '^#' "$repo/dev/e2e/expected-placements.md5" | head -1)

# Connection A: one autocommit statement, like the worker. Sets a_pid (psql),
# a_start; the result goes to $work/<tag>.out/.err/.rc.
start_a() { # db, tag, statement_timeout [, extra PGOPTIONS]
  a_start=$(now)
  ( PGAPPNAME="agoh_cc_A_$2" PGOPTIONS="-c statement_timeout=$3 -c client_min_messages=warning ${4:-}" \
      psql -X -q -At -v ON_ERROR_STOP=1 -v VERBOSITY=verbose -d "$1" -c "$GENERAR" \
      >"$work/$2.out" 2>"$work/$2.err"; echo $? >"$work/$2.rc" ) &
  a_pid=$!
}
wait_a() { # tag; sets a_rc, a_took
  wait "$a_pid"; a_rc=$(cat "$work/$1.rc"); a_took=$(secs "$a_start" "$(now)")
}
wait_started() { # db, n: until n placements started (30 s deadline)
  local deadline=$((SECONDS + 30))
  while [ "$(started "$1")" -lt "$2" ]; do
    [ $SECONDS -lt $deadline ] || return 1
    sleep 0.02
  done
}
backend_of() { # db, tag: backend pid of connection A
  q "$1" "SELECT pid FROM pg_stat_activity WHERE datname = current_database() AND application_name = 'agoh_cc_A_$2'"
}
sqlstate() { sed -nE 's/.*(ERROR|FATAL): +([0-9A-Z]{5}):.*/\2/p' "$work/$1.err" | head -1; }

echo "# full: no cancellation"
db=$(fresh full)
start_a "$db" full 120s; wait_a full
full_took=$a_took
echo "TIMING full run ${full_took}s"
check "full run returns normally" test "$a_rc" = 0
check "full run places 8/8 activities" test "$(placed "$db")" = 8
check "full run gives the E2E placement digest ($expected)" test "$(digest "$db")" = "$expected"

for n in 1 3 5; do
  echo "# soft-$n: generador.cancelar row committed by B after $n placements started"
  db=$(fresh "soft-$n")
  start_a "$db" "soft$n" 120s
  if ! wait_started "$db" "$n"; then bad "soft-$n: placement $n never started"; wait_a "soft$n"; continue; fi
  check "soft-$n: A is still running when B cancels" test -z "$(cat "$work/soft$n.rc" 2>/dev/null)"
  c_at=$(now)
  q "$db" "INSERT INTO generador.cancelar (especialidad, nivel, periodo) VALUES ($SCOPE)" >/dev/null
  wait_a "soft$n"
  latency=$(secs "$c_at" "$(now)")
  got=$(placed "$db")
  echo "TIMING soft-$n run ${a_took}s (full ${full_took}s), cancel-to-return ${latency}s, placed $got"
  check "soft-$n: generar() returns normally (exit 0, no error)" test "$a_rc" = 0
  check "soft-$n: returns well before the full run (${a_took}s < 0.8 x ${full_took}s)" lt "$a_took" "$(awk -v f="$full_took" 'BEGIN { print f * 0.8 }')"
  check "soft-$n: partial placements are kept and visible to B ($got, between $n and 7)" \
    bash -c "[ $got -ge $n ] && [ $got -lt 8 ]"
  check "soft-$n: the cancel row was consumed" test "$(cancel_rows "$db")" = 0
done

echo "# repeatable-read: the same soft cancel under REPEATABLE READ (NOT the worker's model)"
db=$(fresh rr)
start_a "$db" rr 120s '-c default_transaction_isolation=repeatable\ read'
wait_started "$db" 1 || bad "repeatable-read: placement never started"
q "$db" "INSERT INTO generador.cancelar (especialidad, nivel, periodo) VALUES ($SCOPE)" >/dev/null
wait_a rr
echo "TIMING repeatable-read run ${a_took}s, placed $(placed "$db")"
check "repeatable-read: the row is NOT seen (one snapshot): all 8 placed" test "$(placed "$db")" = 8
check "repeatable-read: the row is left behind" test "$(cancel_rows "$db")" = 1

echo "# cancel: pg_cancel_backend from B"
db=$(fresh cancel)
start_a "$db" cancel 120s
wait_started "$db" 2 || bad "cancel: placement never started"
pid=$(backend_of "$db" cancel)
check "cancel: B finds A's backend" test -n "$pid"
q "$db" "SELECT pg_cancel_backend($pid)" >/dev/null
wait_a cancel
echo "TIMING cancel run ${a_took}s"
check "cancel: generar() fails with SQLSTATE 57014 (query_canceled)" bash -c "[ $a_rc != 0 ] && [ '$(sqlstate cancel)' = 57014 ]"
check "cancel: nothing is kept (rollback)" test "$(placed "$db")" = 0
check "cancel: placement had started (sequence >= 2)" test "$(started "$db")" -ge 2

echo "# terminate: pg_terminate_backend from B"
db=$(fresh terminate)
start_a "$db" terminate 120s
wait_started "$db" 2 || bad "terminate: placement never started"
pid=$(backend_of "$db" terminate)
q "$db" "SELECT pg_terminate_backend($pid)" >/dev/null
wait_a terminate
echo "TIMING terminate run ${a_took}s"
check "terminate: A's statement fails (connection terminated)" test "$a_rc" != 0
check "terminate: nothing is kept (rollback)" test "$(placed "$db")" = 0

echo "# timeout: statement_timeout mid-run"
db=$(fresh timeout)
start_a "$db" timeout 1500ms; wait_a timeout
echo "TIMING timeout run ${a_took}s"
check "timeout: generar() fails with SQLSTATE 57014" bash -c "[ $a_rc != 0 ] && [ '$(sqlstate timeout)' = 57014 ]"
check "timeout: placement had started" test "$(started "$db")" -ge 1
check "timeout: nothing is kept (rollback)" test "$(placed "$db")" = 0

echo "# late-row: cancel row committed after the run finished"
db=$(fresh late-row)
q "$db" "DROP TRIGGER tst_cc_slow_placement ON actividad" >/dev/null
q "$db" "$GENERAR" >/dev/null 2>&1
q "$db" "INSERT INTO generador.cancelar (especialidad, nivel, periodo) VALUES ($SCOPE)" >/dev/null
q "$db" "DELETE FROM actividad" >/dev/null
check "late-row: the row survives the finished run" test "$(cancel_rows "$db")" = 1
PGOPTIONS="-c client_min_messages=warning" q "$db" "$GENERAR" >/dev/null
check "late-row: the next run stops at its first check and places nothing" test "$(placed "$db")" = 0
check "late-row: that run consumed the row" test "$(cancel_rows "$db")" = 0

echo "# timeout-row: a consumed cancel row comes back when the run rolls back"
# generar() deletes the row inside its own transaction (fue_cancelado). When
# that transaction is then rolled back (timeout, pg_cancel_backend, lost
# connection), the DELETE is rolled back too: the row stays and would stop
# the next run. Shown on fue_cancelado() itself, deterministically.
db=$(fresh timeout-row)
q "$db" "INSERT INTO generador.cancelar (especialidad, nivel, periodo) VALUES ($SCOPE)" >/dev/null
PGOPTIONS="-c statement_timeout=1500ms -c client_min_messages=warning" \
  psql -X -q -At -d "$db" -c "BEGIN" -c "SELECT generador.fue_cancelado($SCOPE)" -c "SELECT pg_sleep(5)" -c "COMMIT" \
  >"$work/trow.out" 2>"$work/trow.err"
check "timeout-row: fue_cancelado() saw the row inside the transaction" grep -qx t "$work/trow.out"
check "timeout-row: after the timeout the row is still there" test "$(cancel_rows "$db")" = 1

echo "# cancel_midflight: $pass passed, $fail failed"
[ "$fail" = 0 ]
