-- expect: xfail
-- sqlstate: 42702
-- compat: off
-- refs: tiempo/restriccion/valor_restriccion_generica.sql:26; assessment 6.1; sql-reconciliation.md §6
-- Restriction evaluation on PostgreSQL >= 9.0 without the per-session
-- compatibility setting: parameter "id" collides with column "id"
-- (column reference "id" is ambiguous). Intended: evaluates to 0 or 1.
\ir ../fixtures/base.sql
SELECT tst.check(valor_restriccion_profesor('TA', 'PER', 1, 1) IN (0, 1), 'teacher restriction evaluates');
