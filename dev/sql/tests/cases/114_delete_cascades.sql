-- expect: pass
-- refs: ON DELETE CASCADE foreign keys of recurso/*.sql, organizacion/*.sql, actividad/*.sql, tiempo/configuracion/*.sql;
--       legacy-parity-inventory.md PAR-100..PAR-104
-- Deletion cascades (no handler-level refusal in SQL):
-- * deleting a teacher or a place deletes its assignments AND its placed
--   activities (with their occupancy rows);
-- * deleting a brigade keeps its groups; deleting a classification deletes
--   its groups and templates but keeps the breakdowns and subjects;
-- * deleting a plan deletes its periods, groups and breakdowns but keeps the
--   creator's catalogues (subjects, specialties, teachers, rooms);
-- * deleting the user deletes everything they created.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check((SELECT count(*) FROM actividad) = 4, '4 placements');
DELETE FROM profesor WHERE id = 'TA';
SELECT tst.check((SELECT count(*) FROM actividad) = 2 AND (SELECT count(*) FROM actividad WHERE profesor = 'TA') = 0, 'teacher delete removes its placements');
SELECT tst.check((SELECT count(*) FROM profesor_para_actividad) = 1 AND (SELECT count(*) FROM restriccion_de_brigada) = 2, 'and its assignments and occupancy');
DELETE FROM lugar WHERE id = 'L2';
SELECT tst.check((SELECT count(*) FROM actividad) = 0 AND (SELECT count(*) FROM lugar_para_actividad) = 1, 'place delete removes its placements and assignments');
DELETE FROM brigada WHERE id = 'B1';
SELECT tst.check((SELECT count(*) FROM grupo) = 2 AND (SELECT count(*) FROM brigada_de_grupo) = 1, 'brigade delete keeps the groups');
DELETE FROM clasificacion_de_actividad WHERE id = 'C1';
SELECT tst.check((SELECT count(*) FROM grupo) = 0 AND (SELECT count(*) FROM plantilla_de_actividad) = 0
                 AND (SELECT count(*) FROM desglose) = 1 AND (SELECT count(*) FROM asignatura) = 1,
                 'classification delete removes groups and templates, keeps breakdown and subject');
DELETE FROM planificacion WHERE id = 'P';
SELECT tst.check((SELECT count(*) FROM periodo) = 0 AND (SELECT count(*) FROM desglose) = 0
                 AND (SELECT count(*) FROM asignatura) = 1 AND (SELECT count(*) FROM especialidad) = 1 AND (SELECT count(*) FROM profesor) = 1,
                 'plan delete keeps the creator''s catalogues');
DELETE FROM seguridad.usuario WHERE id = 'u1';
SELECT tst.check((SELECT count(*) FROM asignatura) + (SELECT count(*) FROM especialidad) + (SELECT count(*) FROM profesor) + (SELECT count(*) FROM brigada) = 0,
                 'user delete removes everything they created');
