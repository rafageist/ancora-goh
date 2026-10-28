-- expect: pass
-- refs: actividad/actividad.sql (unique (plantilla, grupo); no fixed flag); ancoragoh.sql:126-134 (the only RULEs on
--       actividad and otros_recursos_usados are ON INSERT); tiempo/restriccion/nominal/restringe_segun_actividad.sql;
--       tiempo/restriccion/real/restriccion_real_de_profesor.sql (ON DELETE CASCADE from the nominal row);
--       generar.sql:1004-1012 (esta_colocada: continuation, D7); functional-inventory.md F32; issue #123
-- Characterization only (legacy SQL unchanged): what the database does when a
-- placed activity is MOVED with an UPDATE of dia/turno, as the generated
-- set_Actividad handler would do (no screen of the PHP application posts it).
-- * No rule fires on UPDATE: the activity's occupancy rows (teacher, room,
--   brigade; and their real-date rows) stay at the OLD cell. The new cell is
--   not booked, and the old one stays blocked for anyone else (a ghost).
-- * Nothing is validated: a move onto a cell where the same teacher and room
--   already teach is accepted (a collision), and so is a start outside the
--   period (turno 9 of 3).
-- * Continuation (D7) still counts the activity as placed: the next generar()
--   places nothing for it.
-- * Deleting the moved activity removes its stale occupancy (ON DELETE CASCADE).
\ir ../fixtures/base.sql
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check((SELECT count(*) FROM actividad) = 2, 'fixture: T1 placed for G1 and G2 (teacher TA, room L1, 1 slot)');

CREATE TEMP VIEW occ AS
  SELECT 'teacher' AS kind, profesor AS obj, dia, turno, actividad FROM restriccion_de_profesor WHERE por_actividad
  UNION ALL SELECT 'room', lugar, dia, turno, actividad FROM restriccion_de_lugar WHERE por_actividad
  UNION ALL SELECT 'brigade', brigada, dia, turno, actividad FROM restriccion_de_brigada WHERE por_actividad;
CREATE TEMP TABLE a AS SELECT id, dia, turno FROM actividad WHERE grupo = 'G1';
CREATE TEMP TABLE b AS SELECT id, dia, turno FROM actividad WHERE grupo = 'G2';
-- a free cell of the 2 x 3 period
CREATE TEMP TABLE free AS
  SELECT d AS dia, t AS turno FROM generate_series(1, 2) d, generate_series(1, 3) t
   WHERE (d, t) NOT IN (SELECT dia, turno FROM actividad) ORDER BY d, t LIMIT 1;
CREATE TEMP TABLE real_before AS
  SELECT count(*) AS n FROM restriccion_real_de_profesor r JOIN restriccion_de_profesor p ON p.id = r.restriccion_nominal
   WHERE p.actividad = (SELECT id FROM a);
SELECT tst.check((SELECT count(*) FROM occ WHERE actividad = (SELECT id FROM a)) = 3, 'one teacher, one room and one brigade row for the G1 activity');
SELECT tst.check((SELECT n FROM real_before) = 1, 'one real-date row of the teacher (one periodicity)');

-- The move.
UPDATE actividad SET dia = (SELECT dia FROM free), turno = (SELECT turno FROM free) WHERE id = (SELECT id FROM a);
SELECT tst.check((SELECT count(*) FROM occ o, a WHERE o.actividad = a.id AND o.dia = a.dia AND o.turno = a.turno) = 3,
                 'the occupancy rows stay at the old cell');
SELECT tst.check((SELECT count(*) FROM occ o, free f WHERE o.dia = f.dia AND o.turno = f.turno) = 0,
                 'the new cell is not booked for the teacher, the room or the brigade');
SELECT tst.check((SELECT count(*) FROM restriccion_real_de_profesor r JOIN restriccion_de_profesor p ON p.id = r.restriccion_nominal
                   WHERE p.actividad = (SELECT id FROM a) AND p.dia = (SELECT dia FROM a) AND p.turno = (SELECT turno FROM a)) = 1,
                 'the real-date row stays with the old cell');
SELECT tst.check(valor_restriccion_profesor('TA', 'PER', (SELECT dia FROM a), (SELECT turno FROM a)) > 0,
                 'the old cell still reads as busy for TA (a ghost)');
SELECT tst.check(valor_restriccion_profesor('TA', 'PER', (SELECT dia FROM free), (SELECT turno FROM free)) = 0,
                 'the new cell reads as free for TA although TA teaches there now');

-- No validation: onto the cell of G2's activity (same teacher and room), and outside the period.
UPDATE actividad SET dia = (SELECT dia FROM b), turno = (SELECT turno FROM b) WHERE id = (SELECT id FROM a);
SELECT tst.check((SELECT count(*) FROM tst.collision) = 1, 'the collision (same teacher at the same time) is accepted');
UPDATE actividad SET turno = 9 WHERE id = (SELECT id FROM a);
SELECT tst.check((SELECT turno FROM actividad WHERE id = (SELECT id FROM a)) = 9, 'a start outside the period (turno 9 of 3) is accepted');

-- Continuation (D7): the activity still counts as placed.
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check((SELECT count(*) FROM actividad) = 2, 'the next generar() places nothing new (esta_colocada by plantilla and grupo)');
SELECT tst.check((SELECT turno FROM actividad WHERE id = (SELECT id FROM a)) = 9, 'and keeps the moved row as it is');

-- Deleting the moved activity removes its stale occupancy and real-date rows.
DELETE FROM actividad WHERE id = (SELECT id FROM a);
SELECT tst.check((SELECT count(*) FROM occ WHERE actividad = (SELECT id FROM a)) = 0, 'the stale occupancy goes with the activity');
SELECT tst.check(valor_restriccion_profesor('TA', 'PER', (SELECT dia FROM a), (SELECT turno FROM a)) = 0, 'the old cell is free again');
