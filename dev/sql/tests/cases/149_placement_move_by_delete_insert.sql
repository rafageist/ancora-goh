-- expect: pass
-- refs: actividad/actividad.sql; ancoragoh.sql:126-134 (ON INSERT RULEs); tiempo/restriccion/nominal/restringe_segun_actividad.sql,
--       restringe_por_uso_de_otro_recurso.sql (#24, D-05); tiempo/restriccion/nominal/restriccion_de_{profesor,lugar,brigada,
--       otro_recurso}.sql (unique (object, periodo, dia, turno)); generar.sql:1004-1012 (continuation, D7); issue #123
-- Characterization only (legacy SQL unchanged): a placed activity MOVED by
-- DELETE + INSERT in one transaction, the only way the legacy rules rebuild
-- the occupancy.
-- * DELETE frees everything (ON DELETE CASCADE: nominal and real-date rows,
--   the other resources used); INSERT books the new cell for the teacher, the
--   room and every brigade of the group, for every slot of the duration, with
--   its real-date rows; the other resources must be inserted again
--   (otros_recursos_usados), which books them too.
-- * The unique keys of the occupancy tables refuse a collision with another
--   activity or a MANUAL restriction of the same teacher, room, brigade or
--   resource (23505), and the whole move rolls back: nothing is half done.
-- * They do NOT check period, activity-type or subject restrictions, the
--   period's slot count (a 2-slot activity starting in the last slot books
--   slot 4 of 3), the groups' enrolment against the room's capacity, or the
--   hygiene limits: those are checked only by generar() before it places.
-- * unique (plantilla, grupo) refuses a second placement of a demand.
-- * Continuation (D7): the moved row counts as placed; generar() keeps it.
\ir ../fixtures/base.sql
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
INSERT INTO otro_recurso (id, nombre, creador) VALUES ('R1', 'Projector', 'u1');
INSERT INTO otros_recursos_usados (otro_recurso, actividad, creador) SELECT 'R1', id, 'u1' FROM actividad WHERE grupo = 'G1';

CREATE TEMP VIEW occ AS
  SELECT 'teacher' AS kind, id, dia, turno, actividad FROM restriccion_de_profesor WHERE por_actividad
  UNION ALL SELECT 'room', id, dia, turno, actividad FROM restriccion_de_lugar WHERE por_actividad
  UNION ALL SELECT 'brigade', id, dia, turno, actividad FROM restriccion_de_brigada WHERE por_actividad
  UNION ALL SELECT 'resource', id, dia, turno, actividad FROM restriccion_de_otro_recurso WHERE por_actividad;
CREATE TEMP VIEW occ_real AS
  SELECT r.restriccion_nominal FROM restriccion_real_de_profesor r JOIN restriccion_de_profesor n ON n.id = r.restriccion_nominal WHERE n.por_actividad
  UNION ALL SELECT r.restriccion_nominal FROM restriccion_real_de_lugar r JOIN restriccion_de_lugar n ON n.id = r.restriccion_nominal WHERE n.por_actividad
  UNION ALL SELECT r.restriccion_nominal FROM restriccion_real_de_brigada r JOIN restriccion_de_brigada n ON n.id = r.restriccion_nominal WHERE n.por_actividad
  UNION ALL SELECT r.restriccion_nominal FROM restriccion_real_de_otro_recurso r JOIN restriccion_de_otro_recurso n ON n.id = r.restriccion_nominal WHERE n.por_actividad;
CREATE TEMP TABLE a AS SELECT * FROM actividad WHERE grupo = 'G1';
CREATE TEMP TABLE b AS SELECT * FROM actividad WHERE grupo = 'G2';
CREATE TEMP TABLE counts AS SELECT (SELECT count(*) FROM occ) AS occ, (SELECT count(*) FROM occ_real) AS reals;
SELECT tst.check((SELECT count(*) FROM occ WHERE actividad = (SELECT id FROM a)) = 4, 'G1: teacher, room, brigade and resource rows');

-- A move: DELETE + INSERT (same id, plantilla, grupo, teacher, room) + the resource again, in one statement list.
CREATE FUNCTION pg_temp.move(pid varchar, pdia int, pturno int) RETURNS text LANGUAGE plpgsql AS $$
DECLARE r record;
BEGIN
  SELECT * INTO r FROM a WHERE id = pid;
  DELETE FROM actividad WHERE id = pid;
  INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, momento_de_colocacion, creador)
       VALUES (r.id, r.plantilla, r.grupo, r.profesor, r.lugar, pdia, pturno, r.momento_de_colocacion, r.creador);
  INSERT INTO otros_recursos_usados (otro_recurso, actividad, creador) VALUES ('R1', r.id, 'u1');
  RETURN 'moved';
EXCEPTION WHEN others THEN
  RETURN SQLSTATE;  -- the block is a subtransaction: everything of it is undone
END $$;

-- 1. A collision with G2's activity (same teacher TA and room L1): refused, nothing changes.
SELECT tst.check(pg_temp.move((SELECT id FROM a), (SELECT dia FROM b), (SELECT turno FROM b)) = '23505', 'a collision is refused by the unique keys (23505)');
SELECT tst.check((SELECT (dia, turno) = (SELECT dia, turno FROM a) FROM actividad WHERE id = (SELECT id FROM a)), 'the refused move left the activity where it was');
SELECT tst.check((SELECT occ FROM counts) = (SELECT count(*) FROM occ) AND (SELECT reals FROM counts) = (SELECT count(*) FROM occ_real), 'and its occupancy intact');
SELECT tst.check((SELECT count(*) FROM otros_recursos_usados) = 1, 'and its resource use');

-- 2. A manual restriction of the teacher at a free cell: refused too.
CREATE TEMP TABLE free AS
  SELECT d AS dia, t AS turno FROM generate_series(1, 2) d, generate_series(1, 3) t
   WHERE (d, t) NOT IN (SELECT dia, turno FROM actividad) ORDER BY d, t;
INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad)
  SELECT 'MANUAL', 'TA', 'PER', dia, turno, 1, false FROM free ORDER BY dia, turno LIMIT 1;
SELECT tst.check(pg_temp.move((SELECT id FROM a), (SELECT dia FROM restriccion_de_profesor WHERE id = 'MANUAL'), (SELECT turno FROM restriccion_de_profesor WHERE id = 'MANUAL')) = '23505',
                 'a cell the teacher is restricted at is refused (the same unique key)');

-- 3. A PERIOD restriction is not checked: the move succeeds although generar() would never place there.
CREATE TEMP TABLE target AS SELECT dia, turno FROM free ORDER BY dia, turno OFFSET 1 LIMIT 1;
INSERT INTO restriccion_de_periodo (id, periodo, dia, turno, valor) SELECT 'PR', 'PER', dia, turno, 1 FROM target;
SELECT tst.check(valor_restriccion_periodo('PER', (SELECT dia FROM target), (SELECT turno FROM target)) > 0, 'the period is restricted at the target');
SELECT tst.check(pg_temp.move((SELECT id FROM a), (SELECT dia FROM target), (SELECT turno FROM target)) = 'moved', 'the move onto a period-restricted cell succeeds');
SELECT tst.check((SELECT count(*) FROM occ o, target t WHERE o.actividad = (SELECT id FROM a) AND o.dia = t.dia AND o.turno = t.turno) = 4,
                 'teacher, room, brigade and resource are booked at the new cell');
SELECT tst.check((SELECT count(*) FROM occ o, a WHERE o.actividad = a.id AND o.dia = a.dia AND o.turno = a.turno) = 0, 'nothing is left at the old cell');
SELECT tst.check((SELECT occ FROM counts) = (SELECT count(*) FROM occ) AND (SELECT reals FROM counts) = (SELECT count(*) FROM occ_real),
                 'as many occupancy and real-date rows as before');

-- 4. A second placement of the same demand is refused.
DO $$ BEGIN
  INSERT INTO actividad (plantilla, grupo, profesor, lugar, dia, turno, creador) SELECT plantilla, grupo, 'TB', 'L2', 1, 1, creador FROM a;
  RAISE EXCEPTION 'a second placement of T1/G1 was accepted';
EXCEPTION WHEN unique_violation THEN NULL;
END $$;

-- 5. Duration is trusted: a 2-slot activity starting in the last slot books slot 4 of 3.
INSERT INTO clasificacion_de_actividad (id, descripcion, duracion, creador, prioridad, gasto) VALUES ('C2', 'Block', 2, 'u1', 2, 1);
INSERT INTO brigada (id, descripcion, especialidad, nivel, matricula, creador) VALUES ('B3', 'Cohort 3', 'E', 'N', 99, 'u1');
INSERT INTO grupo (id, descripcion, clasificacion, especialidad, planificacion, nivel) VALUES ('GB', 'Block group', 'C2', 'E', 'P', 'N');
INSERT INTO brigada_de_grupo (brigada, grupo) VALUES ('B3', 'GB');
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion) SELECT 'TBK', id, 2, 'C2' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER';
UPDATE lugar SET capacidad = 10 WHERE id = 'L2';
INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES ('ABK', 'TBK', 'GB', 'TB', 'L2', 2, 3, 'u1');
SELECT tst.check((SELECT count(*) FROM occ WHERE actividad = 'ABK' AND turno = 4) = 3, 'slot 4 of a 3-slot period is booked (teacher, room, brigade)');
SELECT tst.check((SELECT count(*) FROM tst.placed p JOIN periodo pe ON pe.id = p.periodo WHERE p.turno_fin > pe.cant_turnos) = 1, 'the overflow is accepted');
SELECT tst.check(dame_matricula_total(dame_lista_id_brigadas_de_grupo('GB')) > dame_capacidad('L2'), 'and so is a group of 99 in a room of 10 (capacity is checked by generar() only)');

-- 6. Continuation (D7): the moved activity counts as placed and stays.
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check((SELECT (dia, turno) = (SELECT dia, turno FROM target) FROM actividad WHERE id = (SELECT id FROM a)), 'generar() keeps the moved activity');
SELECT tst.check((SELECT count(*) FROM actividad WHERE plantilla = 'T1') = 2, 'and places nothing more for T1');
