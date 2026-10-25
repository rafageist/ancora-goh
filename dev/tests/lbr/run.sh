#!/usr/bin/env bash
# Tests for the LBR tool (dev/lbr/lbr.php, issue #38).
#
# Works in a throwaway clone (the working tree is never written): the frozen
# baseline must reproduce; tampering with the baseline or the rules must be
# detected; and a retirement is credited only when it is verified, merged,
# and the old file is gone from HEAD by path and by content. Deleting or
# moving a file without a record, or with an incomplete record, credits
# nothing.
set -uo pipefail

repo=$(cd "$(dirname "$0")/../../.." && pwd)
PHP=${PHP:-php}
work=$(mktemp -d "${TMPDIR:-/tmp}/agoh-lbr.XXXXXX")
trap 'rm -rf "$work"' EXIT
pass=0; fail=0
ok()  { echo "PASS  $1"; pass=$((pass + 1)); }
bad() { echo "FAIL  $1"; fail=$((fail + 1)); }
expect() { # description, expected exit status, command...
  local d=$1 want=$2; shift 2
  "$@" >"$work/out" 2>&1; local got=$?
  if [ "$got" = "$want" ]; then ok "$d"; else bad "$d (exit $got, expected $want)"; sed 's/^/      /' "$work/out" | head -5; fi
}

git clone -q --no-hardlinks "$repo" "$work/r"
cd "$work/r" || exit 1
git config user.email lbr-test@example.org; git config user.name lbr-test
# the clone starts from the committed state of the current branch
git checkout -q "$(git -C "$repo" rev-parse HEAD)" 2>/dev/null || true
cp "$repo"/dev/lbr/*.php "$repo"/dev/lbr/*.tsv dev/lbr/
git add -A dev/lbr && git commit -qm "test: working-tree LBR files" --allow-empty
lbr() { "$PHP" dev/lbr/lbr.php "$@"; }

expect "verify reproduces the frozen baseline" 0 lbr verify
expect "check accepts the shipped records" 0 lbr check
B=$(lbr report --json | "$PHP" -r '$d=json_decode(stream_get_contents(STDIN),true); echo $d["B"], " ", $d["C"];')
[ "${B% *}" -gt 0 ] && ok "B > 0 ($B)" || bad "B > 0"
expect "freeze refuses to overwrite the frozen baseline" 1 lbr freeze

cp dev/lbr/baseline.tsv "$work/baseline.tsv"
sed -i '0,/^dev\//s/\t\([0-9]*\)\t\([0-9a-f]*\)$/\t1\t\2/' dev/lbr/baseline.tsv
expect "a tampered byte count is detected" 1 lbr verify
cp "$work/baseline.tsv" dev/lbr/baseline.tsv
cp dev/lbr/rules.tsv "$work/rules.tsv"
sed -i 's#^www/images/\*\*\texclude#www/images/**\tinclude\tlegacy-ui\tui.assets#' dev/lbr/rules.tsv
expect "a silent perimeter change (rules) is detected" 1 lbr verify
printf 'zz/**\tinclude\tlegacy-ui\n' >> dev/lbr/rules.tsv
expect "a malformed rule is rejected" 1 lbr verify
cp "$work/rules.tsv" dev/lbr/rules.tsv

victim=$(awk -F'\t' '$1 !~ /^#/ && $2=="legacy-ui" {print $1; exit}' dev/lbr/baseline.tsv)
vbytes=$(awk -F'\t' -v p="$victim" '$1==p {print $4}' dev/lbr/baseline.tsv)
rec() { printf '%s\n' "$1" >> dev/lbr/retirements.tsv; }
reset_rec() { git checkout -q -- dev/lbr/retirements.tsv; }
C() { lbr report --json | "$PHP" -r '$d=json_decode(stream_get_contents(STDIN),true); echo $d["C"];'; }
C0=$(C)

# deletion without a record: nothing credited, reported as missing
git rm -q "$victim" && git commit -qm "delete without record"
[ "$(C)" = "$C0" ] && ok "a deletion without a record credits nothing" || bad "deletion credited"
lbr report | grep -qF "$victim" && ok "the deleted file is listed as missing without retirement" || bad "missing file not listed"
head=$(git rev-parse HEAD)

rec "LBR-001	$victim	verified	r	eliminated: test	tests	#1	$head	-"
expect "a verified record needs a reviewer" 1 lbr check; reset_rec
rec "LBR-001	not/in/baseline.php	verified	r	x	tests	#1	$head	maint"
expect "a path outside the baseline is rejected" 1 lbr check; reset_rec
rec "LBR-001	$victim	verified	r	x	tests	#1	0000000000000000000000000000000000000000	maint"
expect "an unknown or unmerged commit is rejected" 1 lbr check; reset_rec
rec "LBR-001	$victim	verified	r	x	tests	PR1	$head	maint"
expect "a malformed PR reference is rejected" 1 lbr check; reset_rec
rec "LBR-001	$victim	in-transition	r	x	-	-	-	-"
expect "an in-transition record is valid" 0 lbr check
[ "$(C)" = "$C0" ] && ok "in-transition bytes stay in C" || bad "in-transition credited"; reset_rec
rec "LBR-001	$victim	verified	responsibility	eliminated: test	unit tests	#1	$head	maint"
rec "LBR-002	$victim	verified	responsibility	eliminated: test	unit tests	#1	$head	maint"
expect "double counting the same file is rejected" 1 lbr check; reset_rec
rec "LBR-001	$victim	verified	responsibility	eliminated: test	unit tests	#1	$head	maint"
expect "a complete verified record of a removed file is accepted" 0 lbr check
[ "$(C)" = "$((C0 - vbytes))" ] && ok "C drops by exactly the retired bytes ($vbytes)" || bad "C=$(C), expected $((C0 - vbytes))"

# a move: same content under another path is not a retirement
git show "HEAD~1:$victim" > moved-copy.txt && git add moved-copy.txt && git commit -qm "move"
expect "a moved file (same content elsewhere) is rejected" 1 lbr check
[ "$(C)" = "$C0" ] && ok "an invalid record set credits nothing" || bad "credited despite invalid records"
git rm -q moved-copy.txt && git commit -qm "unmove"; reset_rec
rec "LBR-001	$victim	verified	responsibility	eliminated: test	unit tests	#1	$head	maint"
git checkout -q HEAD~3 -- "$victim" && git commit -qm "restore"
expect "a record whose file is back at HEAD is rejected" 1 lbr check
reset_rec

echo "# lbr tests: $pass passed, $fail failed"
[ "$fail" = 0 ]
