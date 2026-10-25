#!/usr/bin/env bash
# Self-test for compilasql.php (issue #9): the real manifest builds, two builds
# are byte-identical, and broken manifests fail. Works on a temporary copy of
# dev/sql/; nothing in the tree is modified.
set -euo pipefail
here=$(cd "$(dirname "$0")/.." && pwd)
php=${PHP:-php}
work=$(mktemp -d "${TMPDIR:-/tmp}/agoh-bt.XXXXXX"); trap 'rm -rf "$work"' EXIT
fail=0

"$php" "$here/compilasql.php" --out "$work/a.sql" 2>/dev/null
"$php" "$here/compilasql.php" --out "$work/b.sql" 2>/dev/null
if cmp -s "$work/a.sql" "$work/b.sql"; then echo "ok   deterministic ($(sha256sum < "$work/a.sql" | cut -c1-16))"
else echo "FAIL two builds differ"; fail=1; fi

expect_fail() { # $1 = name, $2 = sed expression applied to the manifest copy, $3 = expected message
  rm -rf "$work/sql"; cp -r "$here" "$work/sql"
  [ -n "$2" ] && sed -i "$2" "$work/sql/deploy.priority"
  [ "$1" = unlisted-file ] && echo "SELECT 1;" > "$work/sql/miscelanea/zz_unlisted.sql"
  if [ "$1" = ambiguous-dependency ]; then   # a second entry whose last segment is "iif"
    echo "SELECT 1;" > "$work/sql/matrix/iif.sql"
    echo "function: matrix.iif @ second iif @" >> "$work/sql/deploy.priority"
    echo "function: matrix.zz_uses_iif @ test @ iif" >> "$work/sql/deploy.priority"
    echo "SELECT 1;" > "$work/sql/matrix/zz_uses_iif.sql"
  fi
  if out=$("$php" "$work/sql/compilasql.php" 2>&1 >/dev/null); then
    echo "FAIL $1: build succeeded"; fail=1
  elif grep -q "$3" <<<"$out"; then echo "ok   $1 rejected"
  else echo "FAIL $1: unexpected message: $out"; fail=1; fi
}
expect_fail unknown-dependency 's/@ crear_cadena_aleatoria$/@ crear_cadena_aleatoriaX/' "unknown"
expect_fail ambiguous-dependency '' "ambiguous"
expect_fail unmatched-pattern 's/tiempo\.restriccion\.real\.restriccion_real_de_\*/tiempo.nada_*/' "matches no entry"
expect_fail cycle 's/^\(function: miscelanea\.crear_cadena_aleatoria @ [^@]*@\).*/\1 crear_id_de_replica/' "cycle"
expect_fail missing-file 's/^\(function: miscelanea\.iif\) /\1x /' "has no file"
expect_fail duplicate-entry '0,/^function: miscelanea\.iif .*/s//&\nfunction: miscelanea.iif @ dup @/' "listed twice"
expect_fail unlisted-file '' "not listed"
exit $fail
