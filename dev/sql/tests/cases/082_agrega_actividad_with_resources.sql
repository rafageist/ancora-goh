-- expect: pass
-- refs: generador/agrega_actividad.sql (creador); assessment 6.19; #24
-- agrega_actividad() places an activity that uses other resources. Until #24
-- it inserted into otros_recursos_usados without the NOT NULL "creador" (23502).
-- The usage now belongs to the activity's creator.
\ir ../fixtures/base.sql
INSERT INTO otro_recurso (id, nombre, creador) VALUES ('R1', 'Projector 1', 'u1');
SELECT agrega_actividad('T1', 'TA', 'L1', 1, 1, 'G1', ARRAY['R1']::varchar[], 0, 'u1');
SELECT tst.check((SELECT count(*) FROM otros_recursos_usados) = 1, 'resource usage recorded');
SELECT tst.check((SELECT creador FROM otros_recursos_usados) = 'u1', 'usage owned by the activity creator');
SELECT tst.check(valor_restriccion_otro_recurso('R1', 'PER', 1, 1) = 1, 'resource occupied at the placed slot');
SELECT tst.check(valor_restriccion_otro_recurso('R1', 'PER', 1, 2) = 0, 'and only there');
