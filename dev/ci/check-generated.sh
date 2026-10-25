#!/usr/bin/env bash
# Generated code must match its generator (Divengine Codegen, dev/codegen/;
# formerly cogePHP).
#
# Runs `divengine-codegen check`: the generator renders every output family
# from the committed JSON model (dev/src/*.json, dev/services/*.json; no
# database needed) into memory and compares each file with the working tree,
# ignoring the "@updated:" timestamp lines. A difference means a generated
# file was edited by hand without changing its template (or the reverse):
# fix the template in dev/codegen/templates/ (or the data preparation in
# dev/codegen/src/), regenerate (dev/ci/regenerate.sh) and commit both. The
# working tree is never written.
#
# Needs the generator's Composer dependencies; they are installed into
# dev/codegen/vendor/ when missing.
#
# Usage:  dev/ci/check-generated.sh       (uses `php` from PATH)
# Exit status: 0 if the outputs match, 1 otherwise.
set -euo pipefail

PHP="${PHP:-php}"
cd "$(git rev-parse --show-toplevel)"

if [ ! -f dev/codegen/vendor/autoload.php ]; then
  composer --working-dir=dev/codegen install --no-interaction --no-progress --quiet
fi

exec "$PHP" dev/codegen/bin/divengine-codegen check
