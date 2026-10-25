-- expect: pass
-- refs: generador/posible_inicio.sql; assessment 6.2, 6.3, 6.17
-- Historical behavior baseline of posible_inicio(template, day, slot, brigade,
-- places, teachers), including documented defects. Each defect also has an
-- intended-behavior xfail case (021-025). If a fix lands, update both.
\ir ../fixtures/base.sql
-- correct behavior
SELECT tst.check(posible_inicio('T1', 1, 1, 'B1', NULL, NULL), 'free slot is a possible start');
INSERT INTO restriccion_de_brigada (id, brigada, periodo, dia, turno, valor, por_actividad) VALUES ('RB1', 'B1', 'PER', 1, 1, 1, false);
SELECT tst.check(NOT posible_inicio('T1', 1, 1, 'B1', NULL, NULL), 'restricted brigade blocks the start');
SELECT tst.check(posible_inicio('T1', 1, 1, 'B2', NULL, NULL), 'other brigade unaffected');
-- 6.2: loops start at index 0 of 1-based arrays, so the only eligible teacher
-- and the only eligible place are never checked
INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad) VALUES ('RP1', 'TA', 'PER', 1, 2, 1, false);
INSERT INTO restriccion_de_lugar    (id, lugar,    periodo, dia, turno, valor, por_actividad) VALUES ('RL1', 'L1', 'PER', 1, 3, 1, false);
SELECT tst.check(posible_inicio('T1', 1, 2, 'B2', NULL, NULL), 'HISTORICAL: restricted only teacher still accepted');
SELECT tst.check(posible_inicio('T1', 1, 3, 'B2', NULL, NULL), 'HISTORICAL: restricted only place still accepted');
-- with two eligible teachers, both restricted, still accepted
INSERT INTO profesor_para_actividad (id, plantilla, profesor, prioridad) VALUES ('PPA2', 'T1', 'TB', 2);
INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad) VALUES ('RP2', 'TB', 'PER', 1, 2, 1, false);
SELECT tst.check(posible_inicio('T1', 1, 2, 'B2', NULL, NULL), 'HISTORICAL: both restricted teachers still accepted');
-- 6.17: a start whose duration overflows the day is accepted
UPDATE clasificacion_de_actividad SET duracion = 2 WHERE id = 'C1';
SELECT tst.check(posible_inicio('T1', 2, 3, 'B1', NULL, NULL), 'HISTORICAL: 2-slot activity accepted at the last slot');
UPDATE clasificacion_de_actividad SET duracion = 1 WHERE id = 'C1';
-- 6.3: with exige_profesor = true, k = 1 is carried into the essential-resource
-- loop, so restricted essential resources are not checked
\ir ../fixtures/essential_resources.sql
SELECT tst.check(posible_inicio('T1', 1, 1, 'B2', NULL, NULL), 'HISTORICAL: restricted essential resources accepted');
