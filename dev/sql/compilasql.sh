#!/bin/sh
# Writes the schema bundle to ./result.sql (git-ignored). Add --with-data for the default data seed.
cd "$(dirname "$0")" && php ./compilasql.php --out ./result.sql "$@"
