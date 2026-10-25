#!/usr/bin/env bash
# PHP syntax lint for the Áncora GOH legacy code base (issue #13).
#
# Runs `php -l` on every git-tracked *.php and *.tpl file (the .tpl files
# are PHP-embedded templates included by the application, so they are
# parsed exactly like .php files). `php -l` checks SYNTAX ONLY: it does not
# execute code, resolve includes, classes, functions or constants, so a
# clean lint does not prove runtime compatibility. The companion
# dev/ci/php84-static-check.php covers the PHP 8 runtime breakages fixed
# in #13 (PHP 4 constructors, undefined constants, removed functions).
#
# Usage:  dev/ci/php-lint.sh            (uses `php` from PATH)
#         PHP=/usr/bin/php8.3 dev/ci/php-lint.sh
# Exit status: 0 if every checked file parses, 1 otherwise.
set -euo pipefail

PHP="${PHP:-php}"
JOBS="${JOBS:-$(getconf _NPROCESSORS_ONLN 2>/dev/null || echo 2)}"

# Explicit exclusion list (path prefixes, relative to the repository root).
# Keep it minimal and justify every entry in
# docs/modernization/php84-compatibility.md. Currently empty: the only
# files that failed on PHP 8 (NuSOAP, Services_JSON) were removed or fixed.
EXCLUDES=(
)

cd "$(git rev-parse --show-toplevel)"

"$PHP" -r 'echo "php-lint: ", PHP_VERSION, " (", PHP_BINARY, ")\n";'

list_files() {
  git ls-files -z -- '*.php' '*.tpl' | while IFS= read -r -d '' f; do
    for ex in "${EXCLUDES[@]+"${EXCLUDES[@]}"}"; do
      [[ "$f" == "$ex"* ]] && continue 2
    done
    printf '%s\0' "$f"
  done
}

total=$(list_files | tr -cd '\0' | wc -c)

# One php process per file; never abort the batch on the first failure.
failures=$(list_files | xargs -0 -n 1 -P "$JOBS" sh -c '
  out=$("$0" -d display_errors=1 -d log_errors=0 -d error_reporting=-1 -l "$1" 2>&1) || { printf "%s\n" "$out" >&2; echo "$1"; }
' "$PHP" | sort)

failed=0
[[ -n "$failures" ]] && failed=$(printf '%s\n' "$failures" | wc -l)

echo "php-lint: $total file(s) checked, $((total - failed)) ok, $failed failed, ${#EXCLUDES[@]} exclusion(s)"
if [[ "$failed" -ne 0 ]]; then
  printf '%s\n' "$failures" | sed 's/^/FAILED: /'
  exit 1
fi
