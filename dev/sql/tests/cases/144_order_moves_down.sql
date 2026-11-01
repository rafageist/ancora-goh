-- expect: pass
-- refs: actividad/{baja_prioridad_clasif,sube_prioridad_clasif,dame_cant_clasif,arregla_prioridades_clasif}.sql;
--       actividad/{baja_orden_de_plantilla,sube_orden_de_plantilla,actualiza_orden_plantillas}.sql;
--       control/custom/{get_form_new_clasifact,get_form_new_plantilla}.php (count + 1);
--       legacy-parity-inventory.md PAR-019, PAR-020, PAR-035, Q-34, D13
-- The moves the legacy screens offer, completing cases 111 and 112:
-- * baja_prioridad_clasif swaps a classification with the next one of its
--   creator; on the creator's last one it does nothing when it is also the
--   last of the whole table, and FAILS (23502, priority set to NULL) when
--   another creator has more classifications: its bound is the global
--   count (dame_cant_clasif), its neighbour lookup the creator's (Q-34);
-- * sube_prioridad_clasif on priority 1 does nothing;
-- * baja_orden_de_plantilla swaps a template with the next one; on the last
--   one, and sube on the first, nothing changes;
-- * a delete renumbers by the current order (ties by physical order).
\ir ../fixtures/base.sql
INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena)
     VALUES ('u2', 'Other planner', '-', 'other@example.org', 'other', 'not-a-real-hash');
CREATE FUNCTION pg_temp.prio(pcreador varchar) RETURNS text LANGUAGE sql AS $$
  SELECT string_agg(id || '=' || prioridad, ' ' ORDER BY prioridad, id) FROM clasificacion_de_actividad WHERE creador = pcreador
$$;
CREATE FUNCTION pg_temp.state(stmt text) RETURNS text LANGUAGE plpgsql AS $$
BEGIN
  EXECUTE stmt;
  RETURN '00000';
EXCEPTION WHEN OTHERS THEN
  RETURN SQLSTATE;
END $$;
-- u1: C1 (fixture) = 1, C2 = 2, C3 = 3, as the form posts them (count + 1)
INSERT INTO clasificacion_de_actividad (id, descripcion, creador, prioridad) VALUES ('C2', 'Lab', 'u1', 2), ('C3', 'Seminar', 'u1', 3);
SELECT baja_prioridad_clasif('C1');
SELECT tst.check(pg_temp.prio('u1') = 'C2=1 C1=2 C3=3', 'move down swaps with the next one of the creator: ' || pg_temp.prio('u1'));
SELECT sube_prioridad_clasif('C2');
SELECT tst.check(pg_temp.prio('u1') = 'C2=1 C1=2 C3=3', 'move up of the first does nothing');
SELECT baja_prioridad_clasif('C3');
SELECT tst.check(pg_temp.prio('u1') = 'C2=1 C1=2 C3=3', 'move down of the last of the whole table does nothing');
-- another creator with four classifications: u1's last (3) is below the global count (7)
INSERT INTO clasificacion_de_actividad (id, descripcion, creador, prioridad) VALUES ('Q1', 'a', 'u2', 1), ('Q2', 'b', 'u2', 2), ('Q3', 'c', 'u2', 3), ('Q4', 'd', 'u2', 4);
SELECT tst.check(pg_temp.state($q$SELECT baja_prioridad_clasif('C3')$q$) = '23502', 'HISTORICAL: move down of the creator''s last fails (Q-34)');
SELECT tst.check(pg_temp.prio('u1') = 'C2=1 C1=2 C3=3', 'and changes nothing');
SELECT baja_prioridad_clasif('Q3');
SELECT tst.check(pg_temp.prio('u2') = 'Q1=1 Q2=2 Q4=3 Q3=4', 'the other creator''s move down still works');

-- templates of A in PER: T1 (fixture), T2, T3 appended as the form posts them
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion) SELECT 'T2', id, 2, 'C1' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER';
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion) SELECT 'T3', id, 3, 'C1' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER';
CREATE TEMP VIEW ord AS SELECT string_agg(id || '=' || orden, ' ' ORDER BY orden) AS s FROM plantilla_de_actividad;
SELECT baja_orden_de_plantilla('T1');
SELECT tst.check((SELECT s FROM ord) = 'T2=1 T1=2 T3=3', 'move down swaps with the next template');
SELECT baja_orden_de_plantilla('T3');
SELECT tst.check((SELECT s FROM ord) = 'T2=1 T1=2 T3=3', 'move down of the last does nothing');
SELECT sube_orden_de_plantilla('T2');
SELECT tst.check((SELECT s FROM ord) = 'T2=1 T1=2 T3=3', 'move up of the first does nothing');
DELETE FROM plantilla_de_actividad WHERE id = 'T2';
SELECT tst.check((SELECT s FROM ord) = 'T1=1 T3=2', 'delete renumbers in the current order');
DELETE FROM clasificacion_de_actividad WHERE id = 'C2';
SELECT tst.check(pg_temp.prio('u1') = 'C1=1 C3=2', 'classification delete renumbers in the current order');
