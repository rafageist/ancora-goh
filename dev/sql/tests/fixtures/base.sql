-- Synthetic base fixture for the characterization suite (issue #10).
-- No historical data: every name is invented. Explicit ids keep tests readable.
--
-- 1 plan P; 1 period PER of 2 days x 3 slots; 1 activity class C1 (duration 1);
-- specialty E, level N; brigades B1, B2 in groups G1, G2; subject A
-- (the insert trigger creates its desglose for PER); template T1 (order 1)
-- requiring a teacher and a place; teacher TA eligible for T1 (both groups);
-- room L1 for T1; spare teacher TB and room L2 with no assignment.
INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena)
     VALUES ('u1', 'Planner', '-', 'planner@example.org', 'planner', 'not-a-real-hash');
INSERT INTO planificacion (id, descripcion, fecha_inicial, cant_dias, creador) VALUES ('P', 'Plan', 0, 30, 'u1');
INSERT INTO periodo (id, descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion)
     VALUES ('PER', 'Week', 2, 3, 8, 1, 'P');
INSERT INTO periodicidad (id, periodo, dia) VALUES ('PD1', 'PER', 1);
INSERT INTO clasificacion_de_actividad (id, descripcion, duracion, creador, prioridad, gasto) VALUES ('C1', 'Lecture', 1, 'u1', 1, 1);
INSERT INTO especialidad (id, descripcion, creador) VALUES ('E', 'Program', 'u1');
INSERT INTO nivel (id, descripcion, creador) VALUES ('N', 'Year 1', 'u1');
INSERT INTO brigada (id, descripcion, especialidad, nivel, matricula, creador)
     VALUES ('B1', 'Cohort 1', 'E', 'N', 20, 'u1'), ('B2', 'Cohort 2', 'E', 'N', 20, 'u1');
INSERT INTO grupo (id, descripcion, clasificacion, especialidad, planificacion, nivel)
     VALUES ('G1', 'Group 1', 'C1', 'E', 'P', 'N'), ('G2', 'Group 2', 'C1', 'E', 'P', 'N');
INSERT INTO brigada_de_grupo (brigada, grupo) VALUES ('B1', 'G1'), ('B2', 'G2');
INSERT INTO asignatura (id, descripcion, especialidad, nivel, creador) VALUES ('A', 'Subject A', 'E', 'N', 'u1');
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion)
     SELECT 'T1', id, 1, 'C1' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER';
INSERT INTO profesor (id, nombre, creador) VALUES ('TA', 'Teacher A', 'u1'), ('TB', 'Teacher B', 'u1');
INSERT INTO profesor_para_actividad (id, plantilla, profesor, prioridad) VALUES ('PPA1', 'T1', 'TA', 1);
INSERT INTO profesor_para_grupo (profesor_para_actividad, grupo) VALUES ('PPA1', 'G1'), ('PPA1', 'G2');
INSERT INTO lugar (id, nombre, capacidad, creador) VALUES ('L1', 'Room 1', 50, 'u1'), ('L2', 'Room 2', 50, 'u1');
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad) VALUES ('T1', 'L1', 1);
