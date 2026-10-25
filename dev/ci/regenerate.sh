#!/usr/bin/env bash
# Regenerate the generated code from the committed JSON model (#35, #39).
#
# Runs Divengine Codegen (dev/codegen/bin/divengine-codegen all; formerly
# the cogePHP generators) into a temporary directory and copies back only
# the files whose content changed apart from the "@updated:" timestamp
# lines. In a copied file each "@updated:" line keeps the timestamp of the
# committed file, so a change to a template shows up as its semantic diff
# only. New generated files are copied as they are. Nothing is deleted.
#
# Usage:  dev/ci/regenerate.sh        (uses `php` from PATH)
# Then:   dev/ci/check-generated.sh   must pass.
set -euo pipefail

PHP="${PHP:-php}"
cd "$(git rev-parse --show-toplevel)"
ROOT="$PWD"
WORK="$(mktemp -d "${TMPDIR:-/tmp}/ancora-regen.XXXXXX")"
trap 'rm -rf "$WORK"' EXIT

if [ ! -f dev/codegen/vendor/autoload.php ]; then
  composer --working-dir=dev/codegen install --no-interaction --no-progress --quiet
fi
if ! "$PHP" dev/codegen/bin/divengine-codegen all --output "$WORK" >"$WORK/.gen.out" 2>&1; then
  echo "[FAIL] divengine-codegen exited non-zero:" >&2
  tail -n 20 "$WORK/.gen.out" >&2
  exit 1
fi
rm -f "$WORK/.gen.out"

changed=0
added=0
while IFS= read -r line; do
  case "$line" in
    "Files $ROOT/"*" and $WORK/"*" differ")
      rel=${line#"Files $ROOT/"}; rel=${rel%%" and $WORK/"*}
      if ! diff -q -I '@updated:' "$ROOT/$rel" "$WORK/$rel" >/dev/null; then
        # keep the committed timestamps, in order, when the counts match
        "$PHP" -r '
          $old = file($argv[1]); $new = file($argv[2]); $ts = array();
          foreach ($old as $l) if (strpos($l, "@updated:") !== false) $ts[] = $l;
          $n = 0; foreach ($new as $l) if (strpos($l, "@updated:") !== false) $n++;
          $k = 0;
          foreach ($new as $i => $l)
            if (strpos($l, "@updated:") !== false && count($ts) > 0)
              $new[$i] = $ts[$n == count($ts) ? $k++ : 0];
          file_put_contents($argv[3], implode("", $new));
        ' "$ROOT/$rel" "$WORK/$rel" "$ROOT/$rel"
        echo "updated  $rel"
        changed=$((changed + 1))
      fi
      ;;
    "Only in $WORK"*)
      dir=${line#"Only in "}; dir=${dir%%: *}; name=${line##*: }
      rel=${dir#"$WORK"}; rel=${rel#/}; rel=${rel:+$rel/}$name
      mkdir -p "$(dirname "$ROOT/$rel")"
      cp -r "$WORK/$rel" "$ROOT/$rel"
      echo "added    $rel"
      added=$((added + 1))
      ;;
  esac
done < <(diff -r -q -x .git "$ROOT" "$WORK" 2>/dev/null || true)

echo "regenerate: $changed file(s) updated, $added added"
