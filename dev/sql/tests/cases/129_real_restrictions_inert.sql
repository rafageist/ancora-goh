-- expect: pass
-- refs: tiempo/restriccion/valor_restriccion_generica.sql:8 (#variable_conflict use_variable), :73 (WHERE id = idperiodo),
--       :75-160 (real-date branch); assessment 6.10/6.11; sql-variable-conflict.md finding 1;
--       legacy-parity-inventory.md PAR-072, quirk Q-01; docs/modernization/engine-dependency-closure.md section 4.4
-- HISTORICAL: the real-date branch of restriction evaluation does not take
-- part in the result. Its period lookup compares the parameter "id" (the
-- restricted object) with the period id, so it finds no period and every date
-- and hour is NULL. Teacher TA's occupancy writes real-date rows; another plan
-- on the same dates and hours has a periodicity on the same day; teacher TB is
-- still free in the same cell and the next one (Q-01 does not happen).
-- The branch runs only for an object whose id equals the period id: then the
-- other teacher's real-date row blocks it.
\ir ../fixtures/base.sql
INSERT INTO planificacion (id, descripcion, fecha_inicial, cant_dias, creador) VALUES ('P2', 'Plan 2', 0, 30, 'u1');
INSERT INTO periodo (id, descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion)
     VALUES ('PER2', 'Week of plan 2', 2, 3, 8, 1, 'P2');
INSERT INTO periodicidad (id, periodo, dia) VALUES ('PD2', 'PER2', 1);
SELECT agrega_actividad('T1', 'TA', 'L1', 1, 1, 'G1', '{}'::varchar[], 0, 'u1');
SELECT tst.check((SELECT count(*) FROM restriccion_real_de_profesor WHERE objeto = 'TA' AND fecha = 0 AND hora_inicial = 8 AND hora_final = 9) = 1,
                 'the occupancy wrote its real-date row (date 0, 8-9)');
SELECT tst.check(valor_restriccion_profesor('TB', 'PER', 1, 1) = 0 AND valor_restriccion_profesor('TB', 'PER', 1, 2) = 0,
                 'HISTORICAL: another teacher is not blocked by the real-date row');
SELECT tst.check(valor_restriccion_profesor('TB', 'PER2', 1, 1) = 0, 'HISTORICAL: nor in the other plan''s period on the same date and hour');
INSERT INTO profesor (id, nombre, creador) VALUES ('PER', 'Teacher whose id is the period id', 'u1');
SELECT tst.check(valor_restriccion_profesor('PER', 'PER', 1, 1) = 1,
                 'HISTORICAL: an object whose id equals the period id activates the branch and is blocked by any real-date row');
