-- expect: xfail
-- sqlstate: P0001
-- refs: generador/posible_inicio.sql:117-145 (j starts at 0); assessment 6.2
-- Intended: the only eligible teacher is restricted, so the start is not possible.
\ir ../fixtures/base.sql
INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad) VALUES ('RP1', 'TA', 'PER', 1, 2, 1, false);
SELECT tst.check(NOT posible_inicio('T1', 1, 2, 'B1', NULL, NULL), 'restricted only teacher blocks the start');
