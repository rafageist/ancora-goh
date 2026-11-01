-- expect: pass
-- refs: disparadores/nuevo_desglose_cuando_nueva_asignatura.sql, nuevo_desglose_cuando_nuevo_periodo.sql;
--       actividad/desglose.sql (defaults); actividad/dame_lista_id_clasif.sql; tiempo/configuracion/periodo.sql;
--       docs/modernization/legacy-parity-inventory.md PAR-001, PAR-003, PAR-020, PAR-031
-- Catalogue scope is the creator (creador), not the plan:
-- * a new subject gets one breakdown (desglose) per period of EVERY plan of
--   its creator, with the column defaults 1, 5, true, false; a new period gets
--   one per subject of its plan's creator; other creators are not touched;
-- * a period without explicit values gets 5 days x 6 slots and NULL hours;
-- * HISTORICAL: the generator's classification list is global (all creators,
--   ORDER BY prioridad), not per creator.
\ir ../fixtures/base.sql
INSERT INTO planificacion (id, descripcion, fecha_inicial, cant_dias, creador) VALUES ('P2', 'Plan 2', 100, 30, 'u1');
INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena)
     VALUES ('u2', 'Other planner', '-', 'other@example.org', 'other', 'not-a-real-hash');
INSERT INTO planificacion (id, descripcion, fecha_inicial, cant_dias, creador) VALUES ('Q', 'Plan Q', 0, 30, 'u2');
INSERT INTO periodo (id, descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion)
     VALUES ('PER2', 'Week 2', 2, 3, 8, 1, 'P2');
INSERT INTO periodo (id, descripcion, planificacion) VALUES ('PERQ', 'Week Q', 'Q');
SELECT tst.check((SELECT cant_dias = 5 AND cant_turnos = 6 AND hora_inicial IS NULL AND duracion_del_turno IS NULL
                    FROM periodo WHERE id = 'PERQ'), 'period defaults 5 x 6, hours NULL');
-- the new period PER2 (plan P2 of u1) got a breakdown for u1's subject A
SELECT tst.check((SELECT string_agg(asignatura || '@' || periodo, ' ' ORDER BY asignatura, periodo) FROM desglose) = 'A@PER A@PER2',
                 'new period: one breakdown per subject of the creator');
INSERT INTO asignatura (id, descripcion, especialidad, nivel, creador) VALUES ('A2', 'Subject 2', 'E', 'N', 'u1');
SELECT tst.check((SELECT string_agg(periodo, ' ' ORDER BY periodo) FROM desglose WHERE asignatura = 'A2') = 'PER PER2',
                 'new subject: one breakdown per period of every plan of the creator, none for other creators');
SELECT tst.check((SELECT bool_and(min_dias = 1 AND max_dias = 5 AND respetar_orden AND NOT mismo_dia) FROM desglose),
                 'breakdown defaults min_dias 1, max_dias 5, respetar_orden true, mismo_dia false');
-- HISTORICAL: classification list used by generar() spans all creators
INSERT INTO clasificacion_de_actividad (id, descripcion, creador, prioridad) VALUES ('CQ', 'Other', 'u2', 1), ('C2', 'Lab', 'u1', 2);
SELECT tst.check(dame_lista_id_clasif() @> ARRAY['C1', 'C2', 'CQ']::varchar[] AND array_length(dame_lista_id_clasif(), 1) = 3,
                 'HISTORICAL: dame_lista_id_clasif() is not filtered by creator');
