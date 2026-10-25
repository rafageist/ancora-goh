#!/usr/bin/env bash
# Negative test for install.sh (issue #9): a source with a SQL error must make
# the installation fail non-zero and leave no database behind. Uses a temporary
# copy of dev/sql/ and the libpq environment (run under with-ephemeral-pg.sh).
set -uo pipefail
here=$(cd "$(dirname "$0")/.." && pwd)
work=$(mktemp -d "${TMPDIR:-/tmp}/agoh-it.XXXXXX"); trap 'rm -rf "$work"' EXIT
cp -r "$here" "$work/sql"
printf '\nCREATE TABLE broken_on_purpose (;\n' >> "$work/sql/organizacion/nivel.sql"
if "$work/sql/install.sh" agoh_must_fail >/dev/null 2>&1; then
  echo "FAIL installer succeeded with a broken source"; exit 1
fi
if [ -n "$(psql -X -Atc "select 1 from pg_database where datname = 'agoh_must_fail'")" ]; then
  echo "FAIL failed installation left database agoh_must_fail behind"; exit 1
fi
echo "ok   installer fails on SQL error and removes the database"
