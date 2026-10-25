-- expect: xfail
-- sqlstate: P0001
-- refs: generador/posible_inicio.sql:200-254 (k carried over, i/j start at 0); assessment 6.3, 6.13
-- Intended: both essential groups need one resource and both are restricted at
-- (1,1), so the start is not possible.
\ir ../fixtures/base.sql
\ir ../fixtures/essential_resources.sql
SELECT tst.check(NOT posible_inicio('T1', 1, 1, 'B1', NULL, NULL), 'restricted essential resources block the start');
