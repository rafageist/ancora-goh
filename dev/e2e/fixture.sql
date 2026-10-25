-- Synthetic organization for the end-to-end harness (issue #25).
-- No historical data: every name is invented. Loaded after the installer's
-- seed (datos/datos_por_defecto.sql), whose administrator 'admin-superuser'
-- owns the records. Teachers for subject B and their assignments are NOT
-- here: the harness enters them through the web application.
--
-- 1 plan P; period PER of 5 days x 4 slots; activity class C1 (duration 1);
-- specialty E, level N; brigades B1, B2 in lecture groups G1, G2;
-- subject A (templates A1, A2) taught by teacher TA; subject B (templates
-- B1, B2) whose teacher is added over HTTP; rooms L1, L2 for every template.
-- Demand: 4 templates x 2 groups = 8 activities.
INSERT INTO planificacion (id, descripcion, fecha_inicial, cant_dias, creador) VALUES ('P', 'Plan', 0, 30, 'admin-superuser');
INSERT INTO periodo (id, descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion)
     VALUES ('PER', 'Week', 5, 4, 8, 1, 'P');
INSERT INTO periodicidad (id, periodo, dia) VALUES ('PD1', 'PER', 1);
INSERT INTO clasificacion_de_actividad (id, descripcion, duracion, creador, prioridad, gasto) VALUES ('C1', 'Lecture', 1, 'admin-superuser', 1, 1);
INSERT INTO especialidad (id, descripcion, creador) VALUES ('E', 'Program', 'admin-superuser');
INSERT INTO nivel (id, descripcion, creador) VALUES ('N', 'Year 1', 'admin-superuser');
INSERT INTO brigada (id, descripcion, especialidad, nivel, matricula, creador)
     VALUES ('B1', 'Cohort 1', 'E', 'N', 20, 'admin-superuser'), ('B2', 'Cohort 2', 'E', 'N', 20, 'admin-superuser');
INSERT INTO grupo (id, descripcion, clasificacion, especialidad, planificacion, nivel)
     VALUES ('G1', 'Group 1', 'C1', 'E', 'P', 'N'), ('G2', 'Group 2', 'C1', 'E', 'P', 'N');
INSERT INTO brigada_de_grupo (brigada, grupo) VALUES ('B1', 'G1'), ('B2', 'G2');
INSERT INTO asignatura (id, descripcion, especialidad, nivel, creador)
     VALUES ('A', 'Subject Alpha', 'E', 'N', 'admin-superuser'), ('B', 'Subject Beta', 'E', 'N', 'admin-superuser');
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion)
     SELECT a.id || o, d.id, o, 'C1'
       FROM asignatura a JOIN desglose d ON d.asignatura = a.id AND d.periodo = 'PER', generate_series(1, 2) o;
INSERT INTO profesor (id, nombre, creador) VALUES ('TA', 'Teacher Alpha', 'admin-superuser');
INSERT INTO profesor_para_actividad (id, plantilla, profesor, prioridad) VALUES ('PPA1', 'A1', 'TA', 1), ('PPA2', 'A2', 'TA', 1);
INSERT INTO profesor_para_grupo (profesor_para_actividad, grupo) VALUES ('PPA1', 'G1'), ('PPA1', 'G2'), ('PPA2', 'G1'), ('PPA2', 'G2');
INSERT INTO lugar (id, nombre, capacidad, creador) VALUES ('L1', 'Room 1', 50, 'admin-superuser'), ('L2', 'Room 2', 50, 'admin-superuser');
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad)
     SELECT p.id, l.id, CASE l.id WHEN 'L1' THEN 1 ELSE 2 END FROM plantilla_de_actividad p, lugar l;
