#!/usr/bin/env bash
# Tests for the LBR tool (dev/lbr/lbr.php, issues #38, #67, #77).
#
# Works in a throwaway clone (the working tree is never written):
# - every frozen set reproduces; tampering with a baseline or its rules is detected;
# - a retirement is credited only when it is verified, merged, and the old file is
#   gone from HEAD by path, by relocated path and by content;
# - deleting, moving or relocating a file without a record credits nothing;
# - an unfrozen set reports no metric and accepts no records; sets are never combined.
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
git checkout -q "$(git -C "$repo" rev-parse HEAD)" 2>/dev/null || true
# test the working-tree version of the tool and its sets
rm -rf dev/lbr && cp -r "$repo/dev/lbr" dev/lbr
git add -A dev/lbr && git commit -qm "test: working-tree LBR files" --allow-empty
lbr() { "$PHP" dev/lbr/lbr.php "$@"; }
S=historic-2010
sd=dev/lbr/sets/$S
json() { lbr report --set=$S --json | "$PHP" -r '$d=json_decode(stream_get_contents(STDIN),true); echo $d["'"$1"'"];'; }

expect "verify reproduces every frozen set" 0 lbr verify
expect "check accepts the shipped records" 0 lbr check
[ "$(json B)" -gt 0 ] && ok "B > 0 ($(json B))" || bad "B > 0"
expect "freeze refuses to overwrite a frozen set" 1 lbr freeze --set=$S --at=HEAD
expect "freeze needs a set and a commit" 2 lbr freeze --set=$S
expect "an unknown set is refused" 1 lbr report --set=nope

cp $sd/baseline.tsv "$work/baseline.tsv"
sed -i '0,/^[a-z]/s/\t\([0-9]*\)\t\([0-9a-f]*\)$/\t1\t\2/' $sd/baseline.tsv
expect "a tampered byte count is detected" 1 lbr verify --set=$S
cp "$work/baseline.tsv" $sd/baseline.tsv
cp $sd/rules.tsv "$work/rules.tsv"
sed -i 's#^www/images/\*\*\texclude#www/images/**\tinclude\tlegacy-ui\tui.assets#' $sd/rules.tsv
expect "a silent perimeter change (rules) is detected" 1 lbr verify --set=$S
printf 'zz/**\tinclude\tlegacy-ui\n' >> $sd/rules.tsv
expect "a malformed rule is rejected" 1 lbr verify --set=$S
cp "$work/rules.tsv" $sd/rules.tsv
expect "baseline restored" 0 lbr verify --set=$S

# an unfrozen set: preview works, no metric, no records allowed
mkdir -p dev/lbr/sets/test-unfrozen
printf '{"description": "test", "baseline_commit": null, "origin_commit": null}\n' > dev/lbr/sets/test-unfrozen/set.json
printf '**\tinclude\tall\tall\teverything\n' > dev/lbr/sets/test-unfrozen/rules.tsv
expect "preview of an unfrozen set" 0 lbr preview --set=test-unfrozen --at=HEAD
lbr report | grep -q "Not frozen yet" && ok "an unfrozen set reports no metric" || bad "unfrozen set reported a metric"
printf 'LBR-001\tx\tin-transition\tr\tx\t-\t-\t-\t-\n' > dev/lbr/sets/test-unfrozen/retirements.tsv
expect "records for an unfrozen set are refused" 1 lbr check
rm -rf dev/lbr/sets/test-unfrozen
[ "$(lbr report --json | "$PHP" -r 'echo count(json_decode(stream_get_contents(STDIN), true));')" = "$(ls dev/lbr/sets | wc -l)" ] \
  && ok "report lists every set separately" || bad "report does not list sets separately"

victim=$(awk -F'\t' '$1 !~ /^#/ && $2=="legacy-ui" {print $1; exit}' $sd/baseline.tsv)
vbytes=$(awk -F'\t' -v p="$victim" '$1==p {print $4}' $sd/baseline.tsv)
rec() { printf '%s\n' "$1" >> $sd/retirements.tsv; }
reset_rec() { git checkout -q -- $sd/retirements.tsv; }
C0=$(json C)

# relocation (as in the move into legacy/, #68): still counted, not missing, not retirable
mkdir -p "legacy/$(dirname "$victim")" && git mv "$victim" "legacy/$victim"
printf '%s\tlegacy/%s\n' "$(dirname "$victim")/" "$(dirname "$victim")/" > dev/lbr/relocations.tsv
git add -A && git commit -qm "relocate"
[ "$(json C)" = "$C0" ] && ok "a relocated file stays in C" || bad "relocation credited"
[ "$(json relocated_files)" -ge 1 ] && ok "relocated files are reported" || bad "relocated files not reported"
if lbr report --set=$S | grep -qF -- "- \`$victim\`"; then bad "a relocated file is reported missing"; else ok "a relocated file is not reported missing"; fi
echo "// edited after the move" >> "legacy/$victim"; git commit -qam "edit relocated"
head=$(git rev-parse HEAD)
rec "LBR-001	$victim	verified	responsibility	eliminated: test	unit tests	#1	$head	maint"
expect "a retirement of a file still present at its relocated path is rejected" 1 lbr check --set=$S; reset_rec
git rm -q "legacy/$victim" && git rm -q dev/lbr/relocations.tsv && git commit -qm "really remove"

# deletion without a record: nothing credited, reported as missing
[ "$(json C)" = "$C0" ] && ok "a deletion without a record credits nothing" || bad "deletion credited"
lbr report --set=$S | grep -qF "$victim" && ok "the deleted file is listed as missing without retirement" || bad "missing file not listed"
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
[ "$(json C)" = "$C0" ] && ok "in-transition bytes stay in C" || bad "in-transition credited"; reset_rec
rec "LBR-001	$victim	verified	responsibility	eliminated: test	unit tests	#1	$head	maint"
rec "LBR-002	$victim	verified	responsibility	eliminated: test	unit tests	#1	$head	maint"
expect "double counting the same file is rejected" 1 lbr check; reset_rec
rec "LBR-001	$victim	verified	responsibility	eliminated: test	unit tests	#1	$head	maint"
expect "a complete verified record of a removed file is accepted" 0 lbr check
[ "$(json C)" = "$((C0 - vbytes))" ] && ok "C drops by exactly the retired bytes ($vbytes)" || bad "C=$(json C), expected $((C0 - vbytes))"

# a copy elsewhere (same content as the baseline) is not a retirement
git show "eca8aa7:$victim" > moved-copy.txt && git add moved-copy.txt && git commit -qm "copy"
expect "a copied file (same content elsewhere) is rejected" 1 lbr check
[ "$(json C)" = "$C0" ] && ok "an invalid record set credits nothing" || bad "credited despite invalid records"
git rm -q moved-copy.txt && git commit -qm "uncopy"
git checkout -q eca8aa7 -- "$victim" && git commit -qm "restore"
expect "a record whose file is back at HEAD is rejected" 1 lbr check
reset_rec

echo "# lbr tests: $pass passed, $fail failed"
[ "$fail" = 0 ]
