-- expect: pass
-- refs: tiempo/restriccion/nominal/restringe_por_uso_de_otro_recurso.sql; restriccion_de_otro_recurso.sql
--       (unique otro_recurso, periodo, dia, turno); generador/agrega_actividad.sql; #24
-- Other-resource bookings: multi-slot occupancy, competing and repeated
-- bookings, a booking over a manual restriction, rollback, and removal with
-- the activity. A conflicting booking fails with 23505 (unique_violation)
-- and leaves nothing behind, the same way double-booking a teacher or place does.
\ir ../fixtures/base.sql
INSERT INTO clasificacion_de_actividad (id, descripcion, duracion, creador, prioridad, gasto) VALUES ('C2', 'Lab', 2, 'u1', 1, 1);
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion)
     SELECT 'T3', id, 2, 'C2' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER';
INSERT INTO otro_recurso (id, nombre, creador) VALUES ('R1', 'Projector 1', 'u1'), ('R2', 'Projector 2', 'u1');
-- essential group for T1 (R1), non-essential group for T3 (R2); the booking itself does not depend on it
INSERT INTO otro_grupo_de_recursos_para_actividad (id, plantilla, prioridad, necesarios, imprescindibles, creador)
     VALUES ('GR1', 'T1', 1, 1, true, 'u1'), ('GR3', 'T3', 1, 1, false, 'u1');
INSERT INTO otro_recurso_para_actividad (grupo_de_recursos, prioridad, otro_recurso) VALUES ('GR1', 1, 'R1'), ('GR3', 1, 'R2');

CREATE FUNCTION tst.sqlstate_of(stmt text) RETURNS text LANGUAGE plpgsql AS $$
BEGIN
  EXECUTE stmt;
  RETURN '00000';
EXCEPTION WHEN OTHERS THEN
  RETURN SQLSTATE;
END $$;
CREATE VIEW tst.r1_occupancy AS
  SELECT dia, turno, actividad FROM restriccion_de_otro_recurso WHERE otro_recurso = 'R1' ORDER BY dia, turno;

-- multi-slot: a 2-slot activity books R1 at (1,1) and (1,2)
SELECT agrega_actividad('T3', 'TA', 'L1', 1, 1, 'G1', ARRAY['R1']::varchar[], 0, 'u1');
SELECT tst.check((SELECT count(*) FROM tst.r1_occupancy) = 2, 'two slots booked');
SELECT tst.check(valor_restriccion_otro_recurso('R1', 'PER', 1, 1) = 1 AND valor_restriccion_otro_recurso('R1', 'PER', 1, 2) = 1
                 AND valor_restriccion_otro_recurso('R1', 'PER', 1, 3) = 0, 'occupied exactly for the duration');

-- competing booking (overlaps slot 2): the whole call fails and keeps nothing
SELECT tst.check(tst.sqlstate_of($q$SELECT agrega_actividad('T1', 'TB', 'L2', 1, 2, 'G2', ARRAY['R1']::varchar[], 0, 'u1')$q$) = '23505',
                 'competing booking rejected with unique_violation');
SELECT tst.check((SELECT count(*) FROM actividad) = 1 AND (SELECT count(*) FROM otros_recursos_usados) = 1
                 AND (SELECT count(*) FROM tst.r1_occupancy) = 2, 'nothing of the rejected call was kept');

-- the free slot after it can be booked by another activity
SELECT agrega_actividad('T1', 'TB', 'L2', 1, 3, 'G2', ARRAY['R1']::varchar[], 0, 'u1');
SELECT tst.check((SELECT count(*) FROM tst.r1_occupancy) = 3, 'adjacent booking accepted');

-- repeated booking of the same resource for the same activity
SELECT tst.check(tst.sqlstate_of($q$INSERT INTO otros_recursos_usados (otro_recurso, actividad, creador)
                                     SELECT 'R1', id, 'u1' FROM actividad WHERE plantilla = 'T3'$q$) = '23505',
                 'repeated booking rejected');
SELECT tst.check((SELECT count(*) FROM tst.r1_occupancy) = 3, 'occupancy unchanged by the repeated booking');

-- a manual (non activity) restriction blocks a booking at that slot
INSERT INTO restriccion_de_otro_recurso (id, otro_recurso, periodo, dia, turno, valor, por_actividad) VALUES ('RO2', 'R2', 'PER', 2, 1, 1, false);
SELECT tst.check(tst.sqlstate_of($q$SELECT agrega_actividad('T1', 'TA', 'L1', 2, 1, 'G1', ARRAY['R2']::varchar[], 0, 'u1')$q$) = '23505',
                 'booking over a manual restriction rejected');
SELECT tst.check((SELECT count(*) FROM restriccion_de_otro_recurso WHERE otro_recurso = 'R2') = 1, 'manual restriction untouched');

-- rollback: a booking undone by its (sub)transaction leaves no occupancy
DO $$
BEGIN
  BEGIN
    PERFORM agrega_actividad('T1', 'TA', 'L1', 2, 2, 'G1', ARRAY['R2']::varchar[], 0, 'u1');
    RAISE EXCEPTION 'undo';
  EXCEPTION WHEN raise_exception THEN NULL;
  END;
END $$;
SELECT tst.check((SELECT count(*) FROM restriccion_de_otro_recurso WHERE otro_recurso = 'R2') = 1
                 AND (SELECT count(*) FROM actividad WHERE dia = 2) = 0, 'rolled-back booking left nothing');

-- deleting the activity frees the resource
DELETE FROM actividad WHERE plantilla = 'T3';
SELECT tst.check((SELECT count(*) FROM tst.r1_occupancy WHERE dia = 1 AND turno IN (1, 2)) = 0, 'occupancy removed with the activity');
SELECT tst.check(valor_restriccion_otro_recurso('R1', 'PER', 1, 1) = 0, 'resource free again');
SELECT tst.check((SELECT count(*) FROM tst.r1_occupancy) = 1, 'the other booking stays');
SELECT tst.check((SELECT count(*) FROM restriccion_por_actividad_de_otro_recurso) = 1, 'links follow the restrictions');
