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
# The checker targets the legacy PHP 4/5 code. The modern Composer projects
# (portal/, dev/codegen/) are PHP 8.4+ by design and have their own php -l,
# PSR-12 and PHPUnit jobs; their syntax (union types, enums, ...) is out of
# this checker's scope (the Div templates in dev/codegen/templates are not PHP).
mapfile -d '' FILES < <(git ls-files -z -- '*.php' '*.tpl' ':!portal/' ':!dev/codegen/')
"$PHP" dev/ci/php84-static-check.php "${ALLOW[@]}" "${FILES[@]}"

# Constructors of the generated data layer really run (no database needed).
"$PHP" dev/ci/php84-constructor-smoke.php
