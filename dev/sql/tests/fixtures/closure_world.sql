-- Synthetic multi-scope fixture for the dependency-closure cases (issue #92).
-- No historical data: every name is invented. Explicit ids keep tests readable.
-- Load after helpers.sql (the runner installs it in the template database).
--
-- Alice (u1) owns:
--   plan P  (fecha_inicial 100, 30 days): period PER  (3 days x 4 slots, 8h, 1h slots), periodicity day 1
--                                         period PER2 (3 days x 4 slots, 8h, 1h slots), periodicity day 8
--   plan P2 (fecha_inicial 100, 30 days): period QER  (3 days x 4 slots, 8h, 1h slots), periodicity day 1
--   activity classes C1 (priority 1), C2 (priority 2), duration 1
--   specialties E and F, level N
--   brigades B1, B2 (E, N); B4 (E, N, cohort of plan P2's group); B3 (F, N)
--   groups of plan P: G1 {B1} C1, G2 {B2} C1, GL {B1, B2} C2, H1 {B3} C1;
--   group of plan P2: GQ {B4} C1
--   subjects A (E, N): template T1 (C1, order 1) in PER, TQ (C1) in QER
--            A2 (E, N): template T3 (C2, order 1) in PER, TQ3 (C2) in QER
--   (every subject of a scope needs a template in the period: generar.sql:250-254)
--            S  (F, N): template U1 (C1, order 1) in PER
--   teachers TA (T1, TQ, U1: shared by specialties E and F), TB (T3, TQ3), TC (spare)
--   rooms L1, L2 (both priority 1 for T1), L3 (T3, TQ3), L4 (U1); TQ uses L2 only
--   zone value 2 for C2 in PER at day 3 slot 4 (stored because it is not 1)
-- Bob (u2) owns plan Q (fecha_inicial 100, the same dates) with period QB
--   (3 x 4, periodicity day 1), classes CB1 (priority 1) and CB2 (priority 2),
--   specialty EB, level NB, brigade BB, group GB, subject SB with template TBB,
--   teacher TX and room LX.
--
-- Scopes: (E, N, PER) is the scope under test. (F, N, PER) shares teacher TA
-- in the same period. (E, N, QER) is the same specialty and level in another
-- plan of the same creator. (EB, NB, QB) is another creator.
INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena) VALUES
  ('u1', 'Alice', '-', 'alice@example.org', 'alice', 'not-a-real-hash'),
  ('u2', 'Bob', '-', 'bob@example.org', 'bob', 'not-a-real-hash');

INSERT INTO planificacion (id, descripcion, fecha_inicial, cant_dias, creador) VALUES
  ('P', 'Plan', 100, 30, 'u1'), ('P2', 'Plan 2', 100, 30, 'u1'), ('Q', 'Bob plan', 100, 30, 'u2');
INSERT INTO periodo (id, descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion) VALUES
  ('PER', 'Week 1', 3, 4, 8, 1, 'P'), ('PER2', 'Week 2', 3, 4, 8, 1, 'P'),
  ('QER', 'Plan 2 week', 3, 4, 8, 1, 'P2'), ('QB', 'Bob week', 3, 4, 8, 1, 'Q');
INSERT INTO periodicidad (id, periodo, dia) VALUES
  ('PD1', 'PER', 1), ('PD8', 'PER2', 8), ('QD1', 'QER', 1), ('BD1', 'QB', 1);

INSERT INTO clasificacion_de_actividad (id, descripcion, duracion, creador, prioridad, gasto) VALUES
  ('C1', 'Lecture', 1, 'u1', 1, 1), ('C2', 'Lab', 1, 'u1', 2, 1),
  ('CB1', 'Bob lecture', 1, 'u2', 1, 1), ('CB2', 'Bob lab', 1, 'u2', 2, 1);
INSERT INTO especialidad (id, descripcion, creador) VALUES ('E', 'Program E', 'u1'), ('F', 'Program F', 'u1'), ('EB', 'Bob program', 'u2');
INSERT INTO nivel (id, descripcion, creador) VALUES ('N', 'Year 1', 'u1'), ('NB', 'Bob year', 'u2');
INSERT INTO brigada (id, descripcion, especialidad, nivel, matricula, creador) VALUES
  ('B1', 'Cohort 1', 'E', 'N', 20, 'u1'), ('B2', 'Cohort 2', 'E', 'N', 20, 'u1'),
  ('B3', 'Cohort F', 'F', 'N', 20, 'u1'), ('B4', 'Cohort plan 2', 'E', 'N', 20, 'u1'),
  ('BB', 'Bob cohort', 'EB', 'NB', 20, 'u2');
INSERT INTO grupo (id, descripcion, clasificacion, especialidad, planificacion, nivel) VALUES
  ('G1', 'Group 1', 'C1', 'E', 'P', 'N'), ('G2', 'Group 2', 'C1', 'E', 'P', 'N'),
  ('GL', 'Lab group', 'C2', 'E', 'P', 'N'), ('H1', 'Group F', 'C1', 'F', 'P', 'N'),
  ('GQ', 'Group of plan 2', 'C1', 'E', 'P2', 'N'), ('GB', 'Bob group', 'CB1', 'EB', 'Q', 'NB');
INSERT INTO brigada_de_grupo (brigada, grupo) VALUES
  ('B1', 'G1'), ('B2', 'G2'), ('B1', 'GL'), ('B2', 'GL'), ('B3', 'H1'), ('B4', 'GQ'), ('BB', 'GB');

-- The breakdown triggers create one desglose per subject and period of the creator's plans.
INSERT INTO asignatura (id, descripcion, especialidad, nivel, creador) VALUES
  ('A', 'Subject A', 'E', 'N', 'u1'), ('A2', 'Subject A2', 'E', 'N', 'u1'),
  ('S', 'Subject S', 'F', 'N', 'u1'), ('SB', 'Bob subject', 'EB', 'NB', 'u2');
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion)
  SELECT v.id, d.id, 1, v.clasif FROM (VALUES ('T1', 'A', 'PER', 'C1'), ('T3', 'A2', 'PER', 'C2'), ('U1', 'S', 'PER', 'C1'),
                                              ('TQ', 'A', 'QER', 'C1'), ('TQ3', 'A2', 'QER', 'C2'), ('TBB', 'SB', 'QB', 'CB1')) v(id, asig, per, clasif)
  JOIN desglose d ON d.asignatura = v.asig AND d.periodo = v.per;

INSERT INTO profesor (id, nombre, creador) VALUES
  ('TA', 'Teacher A', 'u1'), ('TB', 'Teacher B', 'u1'), ('TC', 'Teacher C', 'u1'), ('TX', 'Bob teacher', 'u2');
INSERT INTO profesor_para_actividad (id, plantilla, profesor, prioridad) VALUES
  ('PT1', 'T1', 'TA', 1), ('PT3', 'T3', 'TB', 1), ('PU1', 'U1', 'TA', 1), ('PTQ', 'TQ', 'TA', 1), ('PTQ3', 'TQ3', 'TB', 1), ('PBB', 'TBB', 'TX', 1);
INSERT INTO profesor_para_grupo (profesor_para_actividad, grupo) VALUES
  ('PT1', 'G1'), ('PT1', 'G2'), ('PT1', 'GQ'), ('PT3', 'GL'), ('PU1', 'H1'),
  ('PTQ', 'G1'), ('PTQ', 'G2'), ('PTQ', 'GQ'), ('PTQ3', 'GL'), ('PBB', 'GB');
INSERT INTO lugar (id, nombre, capacidad, creador) VALUES
  ('L1', 'Room 1', 0, 'u1'), ('L2', 'Room 2', 0, 'u1'), ('L3', 'Lab', 0, 'u1'), ('L4', 'Room F', 0, 'u1'),
  ('LX', 'Bob room', 0, 'u2');
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad) VALUES
  ('T1', 'L1', 1), ('T1', 'L2', 1), ('T3', 'L3', 1), ('U1', 'L4', 1), ('TQ', 'L2', 1), ('TQ3', 'L3', 1), ('TBB', 'LX', 1);

INSERT INTO zpriori (clasificacion, periodo, dia, turno, valor) VALUES ('C2', 'PER', 3, 4, 2);
