-- expect: xfail
-- sqlstate: P0001
-- refs: generador/posible_inicio.sql:155-183 (j starts at 0); assessment 6.2
-- Intended: the only eligible place is restricted, so the start is not possible.
\ir ../fixtures/base.sql
INSERT INTO restriccion_de_lugar (id, lugar, periodo, dia, turno, valor, por_actividad) VALUES ('RL1', 'L1', 'PER', 1, 3, 1, false);
SELECT tst.check(NOT posible_inicio('T1', 1, 3, 'B1', NULL, NULL), 'restricted only place blocks the start');
