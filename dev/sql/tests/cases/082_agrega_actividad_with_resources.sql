-- expect: xfail
-- sqlstate: 23502
-- refs: generador/agrega_actividad.sql:22 (no creador); assessment 6.19
-- Intended: agrega_actividad() places an activity that uses other resources.
-- It inserts into otros_recursos_usados without the NOT NULL "creador".
\ir ../fixtures/base.sql
INSERT INTO otro_recurso (id, nombre, creador) VALUES ('R1', 'Projector 1', 'u1');
SELECT agrega_actividad('T1', 'TA', 'L1', 1, 1, 'G1', ARRAY['R1']::varchar[], 0, 'u1');
SELECT tst.check((SELECT count(*) FROM otros_recursos_usados) = 1, 'resource usage recorded');
