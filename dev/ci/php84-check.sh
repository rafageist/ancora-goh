#!/usr/bin/env bash
# PHP 8 compatibility static check (issue #13): runs the checker's self-test,
# scans every git-tracked *.php / *.tpl file, then runs the constructor
# smoke test (dev/ci/php84-constructor-smoke.php). See
# dev/ci/php84-static-check.php for the rules and their limits.
#
# Usage:  dev/ci/php84-check.sh          (PHP=/usr/bin/php8.3 to override)
set -euo pipefail

PHP="${PHP:-php}"
cd "$(git rev-parse --show-toplevel)"

# Allow-list (rule:path-prefix). Justified in
# docs/modernization/php84-compatibility.md.
ALLOW=(
  # Alternative MySQL driver kept only because dev/cogephp.common.php
  # includes it; the application uses PostgresDatabaseImpl. mysql_* calls
  # fail only if the class is actually used.
  --allow=removed-fn:lib/enterprise/gendb/MySQLDatabaseImpl.php
)

"$PHP" dev/ci/php84-static-check.php --self-test
# single invocation, so constants defined in one file are known in all
# dev/codegen/ (Divengine Codegen, #39) is excluded: it is modern PHP 8.4
# (declare(strict_types=1), named arguments, attributes) that this legacy
# token checker misreads as undefined constants; it is checked by php -l,
# PSR-12 (phpcs) and PHPUnit in the divengine-codegen CI job instead. Its
# Div templates (*.tpl) are not PHP.
mapfile -d '' FILES < <(git ls-files -z -- '*.php' '*.tpl' ':!:dev/codegen/**')
"$PHP" dev/ci/php84-static-check.php "${ALLOW[@]}" "${FILES[@]}"

# Constructors of the generated data layer really run (no database needed).
"$PHP" dev/ci/php84-constructor-smoke.php
