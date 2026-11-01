-- expect: pass
-- refs: disparadores/actualiza_orden_act_{before,after}_{insert,update}.sql, actualiza_orden_act_after_delete.sql;
--       actividad/actualiza_orden_plantillas.sql, sube_orden_de_plantilla.sql; legacy-parity-inventory.md PAR-035
-- Template order (plantilla_de_actividad.orden) within a breakdown:
-- * an insert with an order already taken gets max(orden) + 1;
-- * a delete renumbers the remaining templates 1..n;
-- * sube_orden_de_plantilla swaps with the previous template;
-- * an update to an order already taken in the same breakdown keeps the old order;
-- * an update to a free order renumbers the breakdown 1..n (it cannot leave gaps);
-- * an insert without an order keeps NULL (generar() rejects it, case 065).
\ir ../fixtures/base.sql
CREATE TEMP VIEW ord AS SELECT string_agg(id || '=' || coalesce(orden::text, 'NULL'), ' ' ORDER BY orden NULLS LAST, id) AS s FROM plantilla_de_actividad;
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion) SELECT 'T2', id, 1, 'C1' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER';
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion) SELECT 'T3', id, 7, 'C1' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER';
SELECT tst.check((SELECT s FROM ord) = 'T1=1 T2=2 T3=3', 'colliding order becomes max+1; a free order 7 is renumbered to 3');
SELECT tst.check((SELECT exige_profesor AND exige_lugar AND NOT actualizacion_interna FROM plantilla_de_actividad WHERE id = 'T2'),
                 'templates require teacher and place by default');
DELETE FROM plantilla_de_actividad WHERE id = 'T1';
SELECT tst.check((SELECT s FROM ord) = 'T2=1 T3=2', 'delete renumbers 1..n');
SELECT sube_orden_de_plantilla('T3');
SELECT tst.check((SELECT s FROM ord) = 'T3=1 T2=2', 'move up swaps with the previous template');
UPDATE plantilla_de_actividad SET orden = 2 WHERE id = 'T3';
SELECT tst.check((SELECT s FROM ord) = 'T3=1 T2=2', 'update to a taken order keeps the old order');
UPDATE plantilla_de_actividad SET orden = 9 WHERE id = 'T3';
SELECT tst.check((SELECT s FROM ord) = 'T2=1 T3=2', 'update to a free order renumbers 1..n');
INSERT INTO plantilla_de_actividad (id, desglose, clasificacion) SELECT 'T4', id, 'C1' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER';
SELECT tst.check((SELECT s FROM ord) = 'T2=1 T3=2 T4=NULL', 'insert without order keeps NULL');
