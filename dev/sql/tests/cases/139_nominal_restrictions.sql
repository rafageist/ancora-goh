-- expect: pass
-- refs: tiempo/restriccion/nominal/restriccion_de_*.sql; tiempo/restriccion/valor_restriccion_generica.sql:29-63;
--       generador/posible_inicio.sql:70-180; tiempo/restriccion/real/actualiza_restriccion_real.sql;
--       legacy-parity-inventory.md PAR-070..072, D9a; engine-dependency-closure.md section 4.4
-- Manual (nominal) restrictions and generar(). Base fixture: 2 days x 3
-- slots, groups G1 and G2, template T1, teacher TA, room L1; without
-- restrictions T1 goes to G1@1.1 and G2@1.3.
-- * a restricted cell of the period, the activity type, the subject, the
--   brigade, the teacher or the room is not used; a whole restricted day
--   moves the activities to day 2;
-- * the valor does not matter (5 restricts as 1);
-- * a specialty restriction is stored and never read;
-- * a teacher restriction in another period, or with a NULL period, has no
--   effect (the real-date branch is inert, case 129);
-- * the id has no default (the application supplies it); the key is
--   UNIQUE (object, period, day, slot), so a manual row cannot be added on a
--   cell an occupancy row of a placement holds (23505);
-- * every nominal row gets one real-date row per periodicity of its period.
\ir ../fixtures/base.sql
INSERT INTO periodo (id, descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion) VALUES ('PER2', 'Week 2', 2, 3, 8, 1, 'P');
CREATE FUNCTION pg_temp.cells(setup text) RETURNS text LANGUAGE plpgsql AS $$
BEGIN
  DELETE FROM actividad;
  DELETE FROM restriccion_de_periodo; DELETE FROM restriccion_de_clasif_act; DELETE FROM restriccion_de_asignatura; DELETE FROM restriccion_de_especialidad;
  DELETE FROM restriccion_de_profesor WHERE NOT por_actividad; DELETE FROM restriccion_de_lugar WHERE NOT por_actividad; DELETE FROM restriccion_de_brigada WHERE NOT por_actividad;
  IF setup <> '' THEN EXECUTE setup; END IF;
  PERFORM generar('E', 'N', 'PER', 1, false, 0, false, false);
  RETURN coalesce((SELECT string_agg(format('%s/%s@%s.%s', plantilla, grupo, dia, turno), ' ' ORDER BY plantilla, grupo) FROM actividad), '-');
END $$;
SELECT tst.check(pg_temp.cells('') = 'T1/G1@1.1 T1/G2@1.3', 'no restriction');
SELECT tst.check(pg_temp.cells($q$INSERT INTO restriccion_de_periodo (id, periodo, dia, turno) VALUES ('r1', 'PER', 1, 1)$q$) = 'T1/G1@1.2 T1/G2@1.3', 'period cell');
SELECT tst.check(pg_temp.cells($q$INSERT INTO restriccion_de_periodo (id, periodo, dia, turno) SELECT 'r' || t, 'PER', 1, t FROM generate_series(1, 3) t$q$) = 'T1/G1@2.1 T1/G2@2.3', 'period day');
SELECT tst.check(pg_temp.cells($q$INSERT INTO restriccion_de_periodo (id, periodo, dia, turno, valor) VALUES ('r1', 'PER', 1, 1, 5)$q$) = 'T1/G1@1.2 T1/G2@1.3', 'valor 5 restricts as 1');
SELECT tst.check(pg_temp.cells($q$INSERT INTO restriccion_de_clasif_act (id, clasificacion, periodo, dia, turno) VALUES ('r1', 'C1', 'PER', 1, 1)$q$) = 'T1/G1@1.2 T1/G2@1.3', 'activity type cell');
SELECT tst.check(pg_temp.cells($q$INSERT INTO restriccion_de_asignatura (id, asignatura, periodo, dia, turno) VALUES ('r1', 'A', 'PER', 1, 1)$q$) = 'T1/G1@1.2 T1/G2@1.3', 'subject cell');
SELECT tst.check(pg_temp.cells($q$INSERT INTO restriccion_de_brigada (id, brigada, periodo, dia, turno) VALUES ('r1', 'B1', 'PER', 1, 1)$q$) = 'T1/G1@1.2 T1/G2@1.3', 'brigade cell');
SELECT tst.check(pg_temp.cells($q$INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno) VALUES ('r1', 'TA', 'PER', 1, 1)$q$) = 'T1/G1@1.2 T1/G2@1.3', 'teacher cell');
SELECT tst.check(pg_temp.cells($q$INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno) SELECT 'r' || t, 'TA', 'PER', 1, t FROM generate_series(1, 3) t$q$) = 'T1/G1@2.1 T1/G2@2.3', 'teacher day');
SELECT tst.check(pg_temp.cells($q$INSERT INTO restriccion_de_lugar (id, lugar, periodo, dia, turno) VALUES ('r1', 'L1', 'PER', 1, 1)$q$) = 'T1/G1@1.2 T1/G2@1.3', 'room cell');
SELECT tst.check(pg_temp.cells($q$INSERT INTO restriccion_de_especialidad (id, especialidad, periodo, dia, turno) SELECT 'r' || t, 'E', 'PER', 1, t FROM generate_series(1, 3) t$q$) = 'T1/G1@1.1 T1/G2@1.3', 'a specialty restriction is never read');
SELECT tst.check(pg_temp.cells($q$INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno) SELECT 'r' || t, 'TA', 'PER2', 1, t FROM generate_series(1, 3) t$q$) = 'T1/G1@1.1 T1/G2@1.3', 'a teacher restriction of another period has no effect');
SELECT tst.check(pg_temp.cells($q$INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno) SELECT 'r' || t, 'TA', NULL, 1, t FROM generate_series(1, 3) t$q$) = 'T1/G1@1.1 T1/G2@1.3', 'a restriction without a period has no effect');
SELECT tst.metrics('E', 'N', 'PER');

-- keys and derived rows
DO $$ BEGIN
  INSERT INTO restriccion_de_periodo (periodo, dia, turno) VALUES ('PER', 2, 2);
  RAISE EXCEPTION 'a restriction without id was accepted';
EXCEPTION WHEN not_null_violation THEN NULL;
END $$;
SELECT pg_temp.cells('');
DO $$ BEGIN
  INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno) VALUES ('rx', 'TA', 'PER', 1, 1);
  RAISE EXCEPTION 'a restriction on an occupied cell was accepted';
EXCEPTION WHEN unique_violation THEN NULL;
END $$;
INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno) VALUES ('ry', 'TA', 'PER', 2, 2);
SELECT tst.check((SELECT count(*) FROM restriccion_real_de_profesor WHERE restriccion_nominal = 'ry') = (SELECT count(*) FROM periodicidad WHERE periodo = 'PER'),
                 'one real-date row per periodicity of the period');
DELETE FROM restriccion_de_profesor WHERE id = 'ry';
SELECT tst.check((SELECT count(*) FROM restriccion_real_de_profesor WHERE restriccion_nominal = 'ry') = 0, 'the real-date rows go with the nominal row');
