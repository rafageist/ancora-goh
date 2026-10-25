#!/usr/bin/env bash
# Compile every maintained SQL source under dev/sql/ into a fresh database,
# without relying on deploy.priority, and classify what does not compile.
#
#   dev/sql/tools/check-sources.sh [--keep] [--report FILE]
#
# Connection comes from the standard libpq environment (PGHOST, PGPORT, PGUSER);
# use dev/sql/tools/with-ephemeral-pg.sh to provide a throwaway cluster. A new
# database "agoh_srccheck_<pid>" is created and dropped at the end (--keep keeps it).
#
# Method: "fixpoint" loading. Each file runs in its own transaction with
# check_function_bodies = on and ON_ERROR_STOP. Files that fail are retried in
# the next pass, because they may depend on objects defined later in the list.
# Loading stops when a pass makes no progress. What is left is classified by
# SQLSTATE:
#   SYNTAX     the file itself cannot be parsed/compiled (42601, 42P13, 0A000, 42804, 42P02 ...)
#   DEPENDENCY it references an object that no source file defines (42P01, 42883, 42704, 3F000)
#   OTHER      anything else
#   KNOWN      listed in known-broken.txt: kept as in the 2010 dump pending a
#              decision, loaded with check_function_bodies = off (not a failure)
# The exit status is non-zero when any file is left over.
#
# This is a compile check, not the installer (issue #9): it proves that each
# object can be created once its dependencies exist, not that deploy order,
# data seeds or behavior are right. PL/pgSQL bodies are syntax-checked at
# CREATE time, but names inside them are only resolved at run time.
set -uo pipefail

keep=0; report=""
while [ $# -gt 0 ]; do
  case "$1" in
    --keep) keep=1 ;;
    --report) report=$2; shift ;;
    *) echo "unknown option: $1" >&2; exit 2 ;;
  esac
  shift
done

sqldir=$(cd "$(dirname "$0")/.." && pwd)
db="agoh_srccheck_$$"
work=$(mktemp -d "${TMPDIR:-/tmp}/agoh-src.XXXXXX")
trap 'rm -rf "$work"; [ $keep = 1 ] || dropdb --if-exists "$db" >/dev/null 2>&1' EXIT

createdb "$db" || exit 2

# Objects only. Excluded on purpose:
#   trash/, generador/trash.sql  superseded objects and an ad-hoc scratch script
#   result.sql, tabla.sql        generated bundles (stale; see docs/modernization/sql-reconciliation.md)
#   datos/                       data seed, not object definitions (installer concern, #9)
#   tools/, tests/               tooling and the characterization suite
( cd "$sqldir" && find . -name '*.sql' \
    -not -path './trash/*' -not -path './tools/*' -not -path './tests/*' -not -path './datos/*' \
    -not -name result.sql -not -name tabla.sql -not -path './generador/trash.sql' \
  | sed 's|^\./||' | LC_ALL=C sort ) > "$work/pending"

known=$(grep -v '^#' "$sqldir/tools/known-broken.txt" | cut -f1)
is_known() { grep -qxF "$1" <<<"$known"; }

run_file() { # $1 = relative path; prints psql stderr on failure
  local bodies=on
  is_known "$1" && bodies=off
  PGOPTIONS="-c check_function_bodies=$bodies -c client_min_messages=warning" \
  psql -X -q -v ON_ERROR_STOP=1 -v VERBOSITY=verbose --single-transaction \
       -d "$db" -f "$sqldir/$1" >/dev/null 2>"$work/err"
}

pass=0; : > "$work/loaded"
while :; do
  pass=$((pass + 1)); progress=0; : > "$work/next"
  while IFS= read -r f; do
    if run_file "$f"; then
      echo "$pass	$f" >> "$work/loaded"; progress=1
    else
      echo "$f" >> "$work/next"
    fi
  done < "$work/pending"
  mv "$work/next" "$work/pending"
  [ $progress = 1 ] && [ -s "$work/pending" ] || break
done

classify() {
  case "$1" in
    42601|42P13|0A000|42804|42P02|42702|42P18|42725|42P16|42611) echo SYNTAX ;;
    42P01|42883|42704|3F000) echo DEPENDENCY ;;
    *) echo OTHER ;;
  esac
}

{
  echo "# check-sources: $(wc -l < "$work/loaded") loaded in $pass pass(es), $(wc -l < "$work/pending") failed"
  echo "# server: $(psql -X -Atc 'show server_version' -d "$db")"
  while IFS='	' read -r pass_no f; do
    is_known "$f" && printf 'KNOWN\t%s\t%s\n' "$(grep -F "$f	" "$sqldir/tools/known-broken.txt" | cut -f2)" "$f"
  done < "$work/loaded"
  while IFS= read -r f; do
    run_file "$f"
    line=$(grep -m1 -E '^(psql:.*)?ERROR:' "$work/err")
    code=$(sed -nE 's/.*ERROR: +([0-9A-Z]{5}):.*/\1/p' <<<"$line")
    msg=$(sed -E 's/.*ERROR: +[0-9A-Z]{5}: +//' <<<"$line")
    echo "FAIL	$(classify "$code")	$code	$f	$msg"
  done < "$work/pending"
} | tee "${report:-/dev/null}"

[ $keep = 1 ] && echo "# kept database: $db"
[ -s "$work/pending" ] && exit 1
exit 0
