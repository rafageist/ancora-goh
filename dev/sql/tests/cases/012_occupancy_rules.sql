-- expect: pass
-- refs: ancoragoh.sql:126-129 (RULE actualiza_restricciones_por_actividad); tiempo/restriccion/nominal/restringe_segun_actividad.sql
-- Placing an activity records occupancy as per-activity restrictions of its
-- brigades, teacher and place; deleting it removes them (ON DELETE CASCADE).
\ir ../fixtures/base.sql
INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES ('ACT1', 'T1', 'G1', 'TA', 'L1', 1, 2, 'u1');
SELECT tst.check((SELECT count(*) FROM restriccion_de_brigada  WHERE actividad = 'ACT1' AND brigada = 'B1' AND dia = 1 AND turno = 2 AND por_actividad) = 1, 'brigade occupied');
SELECT tst.check((SELECT count(*) FROM restriccion_de_profesor WHERE actividad = 'ACT1' AND profesor = 'TA' AND dia = 1 AND turno = 2 AND por_actividad) = 1, 'teacher occupied');
SELECT tst.check((SELECT count(*) FROM restriccion_de_lugar    WHERE actividad = 'ACT1' AND lugar = 'L1' AND dia = 1 AND turno = 2 AND por_actividad) = 1, 'place occupied');
SELECT tst.check(valor_restriccion_profesor('TA', 'PER', 1, 2) = 1, 'occupancy is visible to the evaluator');
DELETE FROM actividad WHERE id = 'ACT1';
SELECT tst.check((SELECT count(*) FROM restriccion_de_brigada WHERE actividad = 'ACT1')
               + (SELECT count(*) FROM restriccion_de_profesor WHERE actividad = 'ACT1')
               + (SELECT count(*) FROM restriccion_de_lugar WHERE actividad = 'ACT1') = 0, 'occupancy removed with the activity');
