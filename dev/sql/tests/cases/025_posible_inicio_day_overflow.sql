-- expect: xfail
-- sqlstate: P0001
-- refs: generador/posible_inicio.sql (no bound check against cant_turnos); assessment 6.17
-- Intended: a 2-slot activity cannot start at the last of 3 slots.
\ir ../fixtures/base.sql
UPDATE clasificacion_de_actividad SET duracion = 2 WHERE id = 'C1';
SELECT tst.check(NOT posible_inicio('T1', 1, 3, 'B1', NULL, NULL), 'start overflowing the day rejected');
