#!/usr/bin/env bash
# Generated code must match its generator (cogePHP, dev/).
#
# Copies the git-tracked files to a temporary directory, runs the three
# generators there on the committed JSON model (dev/src/*.json,
# dev/services/*.json; no database needed), and compares every file with
# the working tree, ignoring the "@updated:" timestamp lines. A difference
# means a generated file was edited by hand without changing its generator
# (or the reverse): fix the emit code in dev/cogephp*.php, regenerate, and
# commit both. The working tree is never written.
#
# The dev/*.sh wrappers are not used: they delete the output folders first.
# dev/crea_modelo_pg.php (database -> JSON) is not run.
#
# Usage:  dev/ci/check-generated.sh       (uses `php` from PATH)
# Exit status: 0 if the outputs match, 1 otherwise.
set -euo pipefail

PHP="${PHP:-php}"
cd "$(git rev-parse --show-toplevel)"
ROOT="$PWD"
WORK="$(mktemp -d "${TMPDIR:-/tmp}/ancora-regen.XXXXXX")"
trap 'rm -rf "$WORK"' EXIT

git ls-files -z | tar --null -T - -cf - | tar -xf - -C "$WORK"

for gen in cogephp.php cogephpface.php cogephpservice.php; do
  if ! (cd "$WORK/dev" && "$PHP" "$gen" >"$WORK/.gen.out" 2>&1); then
    echo "[FAIL] dev/$gen exited non-zero:" >&2
    tail -n 20 "$WORK/.gen.out" >&2
    exit 1
  fi
done
rm -f "$WORK/.gen.out"

status=0
diffs="$(diff -r -q -I '@updated:' -x .git "$ROOT" "$WORK" 2>/dev/null | grep -v '^Only in '"$ROOT" || true)"
if [ -n "$diffs" ]; then
  echo "[FAIL] generated files differ from the generator output (ignoring @updated):" >&2
  printf '%s\n' "$diffs" | sed "s#$WORK#<regenerated>#g" >&2
  status=1
fi
n="$(diff -r -q -x .git "$ROOT" "$WORK" 2>/dev/null | grep -c ' differ$' || true)"
if [ $status = 0 ]; then
  echo "check-generated: $n regenerated file(s) match the committed ones (only @updated differs)"
else
  echo "check-generated: $n regenerated file(s) compared; differences listed above"
fi
exit $status
