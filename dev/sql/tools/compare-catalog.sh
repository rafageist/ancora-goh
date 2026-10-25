#!/usr/bin/env bash
# Compare the object inventories of two databases on the same server.
#
#   dev/sql/tools/compare-catalog.sh [--all] <reference-db> <candidate-db>
#
# Prints one line per object that is missing on either side or whose normalized
# definition differs (kind, identity, status), then a per-kind summary.
# --all also lists identical objects (status "identical").
# Connection from the libpq environment. Read-only.
set -euo pipefail
all=0; [ "${1:-}" = --all ] && { all=1; shift; }
ref=$1; cand=$2
here=$(cd "$(dirname "$0")" && pwd)
work=$(mktemp -d "${TMPDIR:-/tmp}/agoh-cmp.XXXXXX"); trap 'rm -rf "$work"' EXIT
for side in ref cand; do
  db=${!side}
  psql -X -At -F $'\t' -v ON_ERROR_STOP=1 -d "$db" -f "$here/catalog-inventory.sql" \
    | awk -F'\t' '{print $1 "|" $2 "\t" $3}' | LC_ALL=C sort > "$work/$side"
done
LC_ALL=C join -t $'\t' -a1 -a2 -e MISSING -o 0,1.2,2.2 "$work/ref" "$work/cand" \
  | awk -F'\t' -v r="$ref" -v c="$cand" -v all=$all '
      { split($1, k, "|"); kind = k[1]; total[kind]++
        if ($2 == "MISSING")      { st = "only-in-" c; only_c[kind]++ }
        else if ($3 == "MISSING") { st = "only-in-" r; only_r[kind]++ }
        else if ($2 != $3)        { st = "different";  diff[kind]++ }
        else                      { same[kind]++; if (!all) next; st = "identical" }
        print kind "\t" substr($1, length(kind) + 2) "\t" st }
      END { printf "# summary (kind: identical / different / only-in-%s / only-in-%s)\n", r, c
            for (t in total) printf "# %s: %d / %d / %d / %d\n", t, same[t], diff[t], only_r[t], only_c[t] }' 
