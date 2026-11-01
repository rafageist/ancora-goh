-- expect: pass
-- refs: organizacion/dame_grupos.sql (no planificacion filter); generador/generar.sql (grupos := dame_grupos(...));
--       legacy-parity-inventory.md PAR-017, quirk Q-02
-- HISTORICAL: generar(specialty, level, period) takes the groups of every
-- plan that match (specialty, level, classification). A group GX of plan P2
-- is scheduled into period PER of plan P.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
INSERT INTO planificacion (id, descripcion, fecha_inicial, cant_dias, creador) VALUES ('P2', 'Plan 2', 100, 30, 'u1');
INSERT INTO brigada (id, descripcion, especialidad, nivel, matricula, creador) VALUES ('B3', 'Cohort 3', 'E', 'N', 20, 'u1');
INSERT INTO grupo (id, descripcion, clasificacion, especialidad, planificacion, nivel) VALUES ('GX', 'Group of plan 2', 'C1', 'E', 'P2', 'N');
INSERT INTO brigada_de_grupo (brigada, grupo) VALUES ('B3', 'GX');
INSERT INTO profesor_para_grupo (profesor_para_actividad, grupo) VALUES ('PPA1', 'GX'), ('PPA2', 'GX');
SELECT tst.check(dame_grupos('E', 'N', 'C1') = ARRAY['G1', 'G2', 'GX']::varchar[], 'HISTORICAL: dame_grupos ignores the plan');
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check((SELECT count(*) FROM actividad WHERE grupo = 'GX') = 2, 'HISTORICAL: the other plan''s group is placed in PER');
SELECT tst.check((SELECT count(*) FROM actividad) = 6 AND (SELECT count(*) FROM tst.collision) = 0, '6 placements, no collisions');
