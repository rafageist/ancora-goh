#!/usr/bin/env bash
# Smoke checks for a freshly installed Áncora GOH database (issue #9).
#
#   dev/sql/tools/smoke-check.sh <db>            compare with expected-objects.tsv
#   dev/sql/tools/smoke-check.sh --update <db>   rewrite expected-objects.tsv
#
# Checks that the object inventory (kind + identity, no definitions) equals the
# reviewed baseline, and that the generator (MPI), Prioriza, cancellation and
# occupancy objects exist. (Function bodies were already compiled by the
# installer with check_function_bodies = on.) Read-only apart from --update.
# Exit status non-zero on any mismatch.
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
update=0; [ "${1:-}" = --update ] && { update=1; shift; }
db=$1
expected=$here/expected-objects.tsv

actual=$(psql -X -At -F $'\t' -v ON_ERROR_STOP=1 -d "$db" -f "$here/catalog-inventory.sql" | cut -f1,2)
if [ $update = 1 ]; then
  { grep '^#' "$expected"; printf '%s\n' "$actual"; } > "$expected.new" && mv "$expected.new" "$expected"
  echo "updated $expected"; exit 0
fi

fail=0
if ! diff <(grep -v '^#' "$expected") <(printf '%s\n' "$actual") > /dev/null; then
  echo "FAIL inventory differs from expected-objects.tsv (< expected, > actual):"
  diff <(grep -v '^#' "$expected") <(printf '%s\n' "$actual") | grep '^[<>]' | head -50 || true
  fail=1
fi

printf '%s\n' "$actual" | cut -f1 | sort | uniq -c | awk '{printf "  %-14s %s\n", $2, $1}'

# Critical objects: generator (MPI), Prioriza, cancellation, occupancy rules.
missing=$(psql -X -At -v ON_ERROR_STOP=1 -d "$db" <<'SQL'
SELECT 'missing ' || x FROM unnest(ARRAY[
  'public.generar','public.posible_inicio','public.and_mpi','public.agrega_actividad',
  'public.get_max_colocation','public.valor_restriccion_generica','public.valor_restriccion_heredada',
  'public.valor_restriccion_otro_recurso','public.restringe_segun_actividad',
  'prioriza.nivelar','prioriza.nivelar_matrix','generador.fue_cancelado']) x
WHERE NOT EXISTS (SELECT 1 FROM pg_proc p JOIN pg_namespace n ON n.oid = p.pronamespace
                  WHERE n.nspname || '.' || p.proname = x)
UNION ALL
SELECT 'missing table generador.cancelar' WHERE to_regclass('generador.cancelar') IS NULL
UNION ALL
SELECT 'missing occupancy rule on ' || t FROM unnest(ARRAY['actividad','otros_recursos_usados']) t
WHERE NOT EXISTS (SELECT 1 FROM pg_rewrite r WHERE r.ev_class = to_regclass('public.' || t) AND r.ev_type = '3');
SQL
) || fail=1
if [ -n "$missing" ]; then sed 's/^/FAIL /' <<<"$missing"; fail=1; fi

[ $fail = 0 ] && echo "smoke-check: OK ($db)"
exit $fail
