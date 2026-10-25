#!/usr/bin/env bash
# Characterization / regression suite for the historical database logic (issue #10).
#
#   dev/sql/tests/run.sh [case-name-substring ...]
#
# Connection from the libpq environment; run it under
# dev/sql/tools/with-ephemeral-pg.sh for a throwaway server. The runner installs
# the schema from sources once (dev/sql/install.sh), adds tests/fixtures/helpers.sql,
# and runs every tests/cases/*.sql in its own copy of that template database
# (CREATE DATABASE ... TEMPLATE), so cases are isolated and repeatable.
#
# Case header (first comment lines):
#   -- expect: pass | xfail      xfail = documented defect; the case asserts the
#                                intended behavior and is expected to fail
#   -- sqlstate: XXXXX           xfail only: the failure must have this SQLSTATE
#                                (57014 = timeout, P0001 = assertion, ...)
#   -- timeout: 5s               statement_timeout (default 20s); a wall-clock
#                                guard of 3x this value also applies
#
# Every case runs with the server's default settings. In particular there is no
# plpgsql.variable_conflict override: functions that need it carry their own
# "#variable_conflict use_variable" directive (#22). A case that sets the
# option itself, or a PGOPTIONS that does, is refused.
#   -- refs: ...                 source lines / assessment defect ids
#
# Result per case: PASS, XFAIL (failed as documented), FAIL, XPASS (an expected
# failure now passes: behavior changed, update the case), or XWRONG (failed, but
# not with the documented SQLSTATE). Lines printed as "METRIC ..." are reported.
# Exit status is non-zero on any FAIL, XPASS or XWRONG.
set -uo pipefail
here=$(cd "$(dirname "$0")" && pwd)
. "$here/../tools/disposable-guard.sh"
agoh_require_disposable || exit 3
work=$(mktemp -d "${TMPDIR:-/tmp}/agoh-tests.XXXXXX")
tpl="agoh_test_tpl_$$"
cleanup() { dropdb --if-exists "$tpl" >/dev/null 2>&1; rm -rf "$work"; }
trap cleanup EXIT

"$here/../install.sh" "$tpl" >"$work/install.log" 2>&1 || { cat "$work/install.log"; exit 2; }
psql -X -q -v ON_ERROR_STOP=1 -d "$tpl" -f "$here/fixtures/helpers.sql" >/dev/null || exit 2

case "${PGOPTIONS:-}" in
  *variable_conflict*) echo "[REFUSED] PGOPTIONS overrides plpgsql.variable_conflict; the suite runs with default settings" >&2; exit 2 ;;
esac

hdr() { sed -n "s/^-- $1: *//p" "$2" | head -1; }
declare -A count=([PASS]=0 [XFAIL]=0 [FAIL]=0 [XPASS]=0 [XWRONG]=0)
printf '%-7s %-6s %-46s %s\n' RESULT TIME CASE DETAIL

for f in "$here"/cases/*.sql; do
  name=$(basename "$f" .sql)
  if [ $# -gt 0 ]; then
    match=0; for s in "$@"; do [[ $name == *"$s"* ]] && match=1; done
    [ $match = 1 ] || continue
  fi
  expect=$(hdr expect "$f"); expect=${expect:-pass}
  want=$(hdr sqlstate "$f")
  limit=$(hdr timeout "$f"); limit=${limit:-20s}
  if grep -v '^[[:space:]]*--' "$f" | grep -qiE '(\bset\b|set_config).*variable_conflict'; then
    echo "[REFUSED] $name sets plpgsql.variable_conflict; cases must run with default settings" >&2; exit 2
  fi
  secs=${limit%s}
  opts="-c statement_timeout=${secs}s -c client_min_messages=warning"

  db="agoh_case_$$"
  createdb -T "$tpl" "$db" || exit 2
  start=$(date +%s.%N)
  PGOPTIONS="$opts" timeout --kill-after=5 $((secs * 3)) \
    psql -X -q -At -v ON_ERROR_STOP=1 -v VERBOSITY=verbose -d "$db" -f "$f" \
    >"$work/out" 2>"$work/err"
  rc=$?
  took=$(awk -v s="$start" -v e="$(date +%s.%N)" 'BEGIN { printf "%.1fs", e - s }')
  dropdb --force "$db" >/dev/null 2>&1

  state=$(sed -nE 's/.*ERROR: +([0-9A-Z]{5}):.*/\1/p' "$work/err" | head -1)
  [ $rc = 124 ] || [ $rc = 137 ] && state=WALLCLOCK
  err=$(grep -m1 -E 'ERROR:' "$work/err" | sed -E 's/.*ERROR: +[0-9A-Z]{5}: +//' | cut -c1-90)
  if [ "$expect" = pass ]; then
    if [ $rc = 0 ]; then res=PASS; detail=""; else res=FAIL; detail="$state $err"; fi
  else
    if [ $rc = 0 ]; then res=XPASS; detail="expected failure did not happen"
    elif [ -z "$want" ] || [ "$state" = "$want" ]; then res=XFAIL; detail="$state"
    else res=XWRONG; detail="expected $want, got $state $err"; fi
  fi
  count[$res]=$((count[$res] + 1))
  printf '%-7s %-6s %-46s %s\n' "$res" "$took" "$name" "$detail"
  grep '^METRIC ' "$work/out" | sed 's/^/        /'
done

echo "# summary: ${count[PASS]} pass, ${count[XFAIL]} xfail, ${count[FAIL]} fail, ${count[XPASS]} xpass, ${count[XWRONG]} xwrong"
[ $((count[FAIL] + count[XPASS] + count[XWRONG])) = 0 ]
