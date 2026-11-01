-- expect: pass
-- refs: ON DELETE CASCADE foreign keys of organizacion/{brigada,grupo,brigada_de_grupo}.sql, actividad/{asignatura,desglose,
--       plantilla_de_actividad,actividad}.sql, recurso/asignacion/{profesor_para_actividad,profesor_para_grupo,
--       lugar_para_actividad}.sql, tiempo/configuracion/{periodo,periodicidad}.sql; control/data/del/*.php (a direct
--       DELETE by id); legacy-parity-inventory.md PAR-100..PAR-103, D5
-- Completes case 114: what every delete of the data entry screens removes.
-- Every foreign key cascades, so a delete never fails because of a
-- reference. Link tables lose only the link: deleting a group keeps the
-- teacher assignment (without that group); deleting a brigade keeps the
-- group. Placements go with their template, group, teacher or room, and
-- their occupancy rows with them.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
CREATE FUNCTION pg_temp.counts() RETURNS text LANGUAGE sql AS $$
  SELECT format('grupo=%s brigada_de_grupo=%s asignatura=%s desglose=%s plantilla=%s ppa=%s ppg=%s lpa=%s actividad=%s occ_brigada=%s occ_profesor=%s occ_lugar=%s periodo=%s periodicidad=%s',
    (SELECT count(*) FROM grupo), (SELECT count(*) FROM brigada_de_grupo), (SELECT count(*) FROM asignatura),
    (SELECT count(*) FROM desglose), (SELECT count(*) FROM plantilla_de_actividad), (SELECT count(*) FROM profesor_para_actividad),
    (SELECT count(*) FROM profesor_para_grupo), (SELECT count(*) FROM lugar_para_actividad), (SELECT count(*) FROM actividad),
    (SELECT count(*) FROM restriccion_de_brigada WHERE por_actividad), (SELECT count(*) FROM restriccion_de_profesor WHERE por_actividad),
    (SELECT count(*) FROM restriccion_de_lugar WHERE por_actividad), (SELECT count(*) FROM periodo), (SELECT count(*) FROM periodicidad))
$$;
CREATE FUNCTION pg_temp.expect(want text, msg text) RETURNS void LANGUAGE plpgsql AS $$
DECLARE got text := pg_temp.counts();
BEGIN
  PERFORM tst.check(got = want, msg || ': got ' || got);
END $$;
CREATE FUNCTION pg_temp.fresh() RETURNS void LANGUAGE plpgsql AS $$
BEGIN
  DELETE FROM actividad;
  PERFORM generar('E', 'N', 'PER', 1, false, 0, false, false);
END $$;
SELECT pg_temp.fresh();
SELECT pg_temp.expect('grupo=2 brigada_de_grupo=2 asignatura=1 desglose=1 plantilla=2 ppa=2 ppg=4 lpa=2 actividad=4 occ_brigada=4 occ_profesor=4 occ_lugar=4 periodo=1 periodicidad=1',
                      'baseline: 4 placements with their occupancy');
-- a template: its assignments, placements and occupancy
DELETE FROM plantilla_de_actividad WHERE id = 'T2';
SELECT pg_temp.expect('grupo=2 brigada_de_grupo=2 asignatura=1 desglose=1 plantilla=1 ppa=1 ppg=2 lpa=1 actividad=2 occ_brigada=2 occ_profesor=2 occ_lugar=2 periodo=1 periodicidad=1',
                      'template delete');
-- a group: its memberships, its teacher-group links (the assignment stays), its placements
DELETE FROM grupo WHERE id = 'G2';
SELECT pg_temp.expect('grupo=1 brigada_de_grupo=1 asignatura=1 desglose=1 plantilla=1 ppa=1 ppg=1 lpa=1 actividad=1 occ_brigada=1 occ_profesor=1 occ_lugar=1 periodo=1 periodicidad=1',
                      'group delete keeps the teacher assignment');
-- a brigade: its memberships and its occupancy rows; the placement stays
DELETE FROM brigada WHERE id = 'B1';
SELECT pg_temp.expect('grupo=1 brigada_de_grupo=0 asignatura=1 desglose=1 plantilla=1 ppa=1 ppg=1 lpa=1 actividad=1 occ_brigada=0 occ_profesor=1 occ_lugar=1 periodo=1 periodicidad=1',
                      'brigade delete keeps the group and the placement, not the brigade''s occupancy rows');
-- a subject: its breakdown, templates, assignments, placements
DELETE FROM asignatura WHERE id = 'A';
SELECT pg_temp.expect('grupo=1 brigada_de_grupo=0 asignatura=0 desglose=0 plantilla=0 ppa=0 ppg=0 lpa=0 actividad=0 occ_brigada=0 occ_profesor=0 occ_lugar=0 periodo=1 periodicidad=1',
                      'subject delete');
-- a period: its periodicity and breakdowns
INSERT INTO asignatura (id, descripcion, especialidad, nivel, creador) VALUES ('A2', 'Subject A2', 'E', 'N', 'u1');
DELETE FROM periodo WHERE id = 'PER';
SELECT pg_temp.expect('grupo=1 brigada_de_grupo=0 asignatura=1 desglose=0 plantilla=0 ppa=0 ppg=0 lpa=0 actividad=0 occ_brigada=0 occ_profesor=0 occ_lugar=0 periodo=0 periodicidad=0',
                      'period delete keeps subjects and groups');
-- a specialty: its brigades, groups and subjects; a level the same
INSERT INTO brigada (id, descripcion, especialidad, nivel, matricula, creador) VALUES ('B9', 'Cohort 9', 'E', 'N', 20, 'u1');
DELETE FROM especialidad WHERE id = 'E';
SELECT tst.check((SELECT count(*) FROM brigada) = 0 AND (SELECT count(*) FROM grupo) = 0 AND (SELECT count(*) FROM asignatura) = 0
                 AND (SELECT count(*) FROM nivel) = 1, 'specialty delete removes its brigades, groups and subjects, keeps the level');
INSERT INTO asignatura (id, descripcion, especialidad, nivel, creador) VALUES ('A3', 'No specialty', NULL, 'N', 'u1');
DELETE FROM nivel WHERE id = 'N';
SELECT tst.check((SELECT count(*) FROM asignatura) = 0, 'level delete removes a subject without specialty too');
