-- expect: pass
-- refs: recurso/asignacion/dame_lista_id_lugares_asignados.sql:13 (no ORDER BY); generador/generar.sql:1314-1328
--       ("el primero": lugs[1]); legacy-parity-harness.md D18; docs/modernization/engine-dependency-closure.md section 6
-- HISTORICAL: with two eligible rooms of the same priority and no usage
-- anywhere (buscalugar false), the room is the first row of
-- lugar_para_actividad in physical (heap) order, not in id order. Re-entering
-- the two assignment rows in the other order changes the room of every
-- placement. A workspace or sync must load the rows in legacy entry order (D18).
\ir ../fixtures/base.sql
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad) VALUES ('T1', 'L2', 1);
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check((SELECT string_agg(DISTINCT lugar, ',') FROM actividad) = 'L1', 'L1 was entered first: every placement uses L1');
DELETE FROM actividad;
DELETE FROM lugar_para_actividad WHERE plantilla = 'T1';
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad) VALUES ('T1', 'L2', 1), ('T1', 'L1', 1);
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check((SELECT string_agg(DISTINCT lugar, ',') FROM actividad) = 'L2', 'HISTORICAL: the same rows entered in the other order give L2');
