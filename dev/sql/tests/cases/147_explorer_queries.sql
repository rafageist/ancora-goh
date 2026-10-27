-- expect: pass
-- refs: control/custom/get_minibrowser_horarios.php:41-59 (the four activity queries, the last posted id wins);
--       control/custom/custom_getui_ExploradorHorarios.php:15 (the user's periods), view/custom/tree_brigadas.tpl:18-34,
--       control/custom/custom_getui_ExploradorList{Profesores,Lugares}.php:7 (getAll), ..OtrosRecursos.php:7 (creador);
--       organizacion/dame_lista_id_brigadas_de_grupo.sql, actividad/dame_lista_id_otros_recursos_usados.sql;
--       legacy-parity-inventory.md PAR-091, D14, Q-23, Q-38; issue #119
-- The timetable explorer's activity queries, run as the handler runs them
-- (same SQL text), on two plans of one creator and a second creator:
-- * a brigade shows the activities of EVERY group it is in now (here a
--   lecture group and a lab group of plan P), and only those of the posted
--   period: in plan P2's period it shows its group G3 and also G1 of plan P,
--   which generar() scheduled there (dame_grupos ignores the plan, Q-02);
-- * a teacher, a room and an other resource show their activities of the
--   period;
-- * another creator's teacher (listed by the explorer, Q-23) shows nothing
--   in this creator's period;
-- * the queries never read the session user: the posted period alone
--   decides whose activities are read (the forged-id hole, like Q-12).
\ir ../fixtures/base.sql
INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena)
     VALUES ('u2', 'Other planner', '-', 'other@example.org', 'other', 'not-a-real-hash');
INSERT INTO profesor (id, nombre, creador) VALUES ('TZ', 'Teacher Z', 'u2');
-- a lab class and group: B1 is in G1 (lecture) and GL (lab)
INSERT INTO clasificacion_de_actividad (id, descripcion, duracion, creador, prioridad, gasto) VALUES ('C2', 'Lab', 1, 'u1', 2, 1);
INSERT INTO grupo (id, descripcion, clasificacion, especialidad, planificacion, nivel) VALUES ('GL', 'Lab group', 'C2', 'E', 'P', 'N');
INSERT INTO brigada_de_grupo (brigada, grupo) VALUES ('B1', 'GL');
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion) SELECT 'TL', id, 2, 'C2' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER';
INSERT INTO profesor_para_actividad (id, plantilla, profesor, prioridad) VALUES ('PPL', 'TL', 'TB', 1);
INSERT INTO profesor_para_grupo (profesor_para_actividad, grupo) VALUES ('PPL', 'GL');
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad) VALUES ('TL', 'L2', 1);
-- a second plan of u1 with its own period and a group of B1
INSERT INTO planificacion (id, descripcion, fecha_inicial, cant_dias, creador) VALUES ('P2', 'Plan 2', 0, 30, 'u1');
INSERT INTO periodo (id, descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion) VALUES ('PER2', 'Week 2', 2, 3, 8, 1, 'P2');
INSERT INTO periodicidad (id, periodo, dia) VALUES ('PD2', 'PER2', 1);
INSERT INTO grupo (id, descripcion, clasificacion, especialidad, planificacion, nivel) VALUES ('G3', 'Group 3', 'C1', 'E', 'P2', 'N');
INSERT INTO brigada_de_grupo (brigada, grupo) VALUES ('B1', 'G3');
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion) SELECT 'T9', id, 1, 'C1' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER2';
INSERT INTO profesor_para_actividad (id, plantilla, profesor, prioridad) VALUES ('PP9', 'T9', 'TA', 1);
INSERT INTO profesor_para_grupo (profesor_para_actividad, grupo) VALUES ('PP9', 'G1'), ('PP9', 'G2'), ('PP9', 'G3');
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad) VALUES ('T9', 'L1', 1);
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT generar('E', 'N', 'PER2', 1, false, 0, false, false);
INSERT INTO otro_recurso (id, nombre, creador) VALUES ('R1', 'Projector', 'u1');
INSERT INTO otros_recursos_usados (otro_recurso, actividad, creador) SELECT 'R1', id, 'u1' FROM actividad WHERE plantilla = 'TL';

-- the handler's queries (get_minibrowser_horarios.php:43, 48, 53, 58), as cells
CREATE FUNCTION pg_temp.cells(cond text, p1 varchar, p2 varchar) RETURNS text LANGUAGE plpgsql AS $$
DECLARE out text;
BEGIN
  EXECUTE format('SELECT coalesce(string_agg(plantilla || ''/'' || grupo || ''@'' || dia || ''.'' || turno, '' '' ORDER BY dia, turno, plantilla, grupo), ''-'')
                    FROM (SELECT *, dame_clasif_plantilla_de_actividad(plantilla) as clasificacion, dame_asignatura_desglose(dame_desglose_de_plantilla(plantilla)) as asignatura
                            FROM actividad WHERE %s) q', cond) INTO out USING p1, p2;
  RETURN out;
END $$;
CREATE FUNCTION pg_temp.expect(got text, want text, msg text) RETURNS void LANGUAGE plpgsql AS $$
BEGIN
  PERFORM tst.check(got = want, msg || ': got ' || got);
END $$;
CREATE TEMP VIEW brg AS SELECT 'matrix.get_pos_of(dame_lista_id_brigadas_de_grupo(grupo), $1)>-1 AND dame_periodo_plantilla_de_actividad(plantilla) = $2'::text AS c;
SELECT pg_temp.expect(pg_temp.cells((SELECT c FROM brg), 'B1', 'PER'),
  (SELECT string_agg(plantilla || '/' || grupo || '@' || dia || '.' || turno, ' ' ORDER BY dia, turno, plantilla, grupo) FROM actividad WHERE grupo IN ('G1', 'GL') AND dame_periodo_plantilla_de_actividad(plantilla) = 'PER'),
  'brigade B1 in PER: the activities of G1 and of the lab group GL');
SELECT tst.check(pg_temp.cells((SELECT c FROM brg), 'B1', 'PER') ~ 'TL/GL' AND pg_temp.cells((SELECT c FROM brg), 'B1', 'PER') ~ 'T1/G1'
                 AND pg_temp.cells((SELECT c FROM brg), 'B1', 'PER') !~ 'G3', 'both groups of the brigade, not the other plan''s group');
SELECT pg_temp.expect(pg_temp.cells((SELECT c FROM brg), 'B1', 'PER2'),
  (SELECT string_agg(plantilla || '/' || grupo || '@' || dia || '.' || turno, ' ' ORDER BY dia, turno, plantilla, grupo) FROM actividad WHERE grupo IN ('G1', 'GL', 'G3') AND dame_periodo_plantilla_de_actividad(plantilla) = 'PER2'),
  'brigade B1 in PER2: its groups'' activities of that period, G1 of plan P included (generar() schedules it there, Q-02)');
SELECT pg_temp.expect(pg_temp.cells('profesor = $1 AND dame_periodo_plantilla_de_actividad(plantilla) = $2', 'TA', 'PER'),
  (SELECT string_agg(plantilla || '/' || grupo || '@' || dia || '.' || turno, ' ' ORDER BY dia, turno, plantilla, grupo) FROM actividad WHERE profesor = 'TA' AND plantilla = 'T1'),
  'teacher TA in PER');
SELECT pg_temp.expect(pg_temp.cells('lugar = $1 AND dame_periodo_plantilla_de_actividad(plantilla) = $2', 'L2', 'PER'),
  (SELECT string_agg(plantilla || '/' || grupo || '@' || dia || '.' || turno, ' ' ORDER BY dia, turno, plantilla, grupo) FROM actividad WHERE lugar = 'L2' AND plantilla = 'TL'),
  'room L2 in PER');
SELECT pg_temp.expect(pg_temp.cells('matrix.get_pos_of(dame_lista_id_otros_recursos_usados(id), $1)>-1 AND dame_periodo_plantilla_de_actividad(plantilla) = $2', 'R1', 'PER'),
  (SELECT string_agg(plantilla || '/' || grupo || '@' || dia || '.' || turno, ' ' ORDER BY dia, turno, plantilla, grupo) FROM actividad WHERE plantilla = 'TL'),
  'other resource R1 in PER');
SELECT pg_temp.expect(pg_temp.cells('profesor = $1 AND dame_periodo_plantilla_de_actividad(plantilla) = $2', 'TZ', 'PER'), '-',
  'another creator''s teacher: nothing in this creator''s period');
SELECT tst.check((SELECT count(*) FROM actividad WHERE plantilla = 'TL') = 1 AND (SELECT count(*) FROM actividad WHERE grupo = 'G3') = 1 AND (SELECT count(*) FROM actividad WHERE grupo = 'G1' AND plantilla = 'T9') = 1, 'fixture sanity: one lab activity; T9 placed for G3 and for G1 of the other plan');
