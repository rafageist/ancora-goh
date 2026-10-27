-- expect: pass
-- refs: actividad/clasificacion_de_actividad.sql (gasto INTEGER default 1, no CHECK);
--       view/custom/form_clasif_act.tpl:35-36 (free text); actividad/higiene/es_higienico_profesor.sql;
--       legacy-parity-inventory.md PAR-019, D6
-- The classification cost takes any integer: 0 and negative values are
-- stored, and generar() completes with them and places the same cells as
-- with the default 1 (the cost only feeds the hygiene limits, PAR-087).
-- A duration above the slots of a day is not refused either (below).
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
SELECT tst.check((SELECT gasto FROM clasificacion_de_actividad WHERE id = 'C1') = 1, 'the fixture cost is 1');
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
CREATE TEMP TABLE base_cells AS SELECT plantilla, grupo, dia, turno FROM actividad;
DELETE FROM actividad;
UPDATE clasificacion_de_actividad SET gasto = 0;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check(NOT EXISTS (SELECT plantilla, grupo, dia, turno FROM actividad EXCEPT SELECT * FROM base_cells)
                 AND (SELECT count(*) FROM actividad) = (SELECT count(*) FROM base_cells), 'cost 0: same placements');
DELETE FROM actividad;
UPDATE clasificacion_de_actividad SET gasto = -3;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check(NOT EXISTS (SELECT plantilla, grupo, dia, turno FROM actividad EXCEPT SELECT * FROM base_cells)
                 AND (SELECT count(*) FROM actividad) = (SELECT count(*) FROM base_cells), 'cost -3: stored, same placements');
SELECT tst.metrics('E', 'N', 'PER');
-- The duration has no upper bound either: one longer than the day (60 slots
-- on a day of 3) completes, and the placements overflow the day (HISTORICAL).
DELETE FROM actividad;
UPDATE clasificacion_de_actividad SET gasto = 1, duracion = 60;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check((SELECT count(*) FROM tst.placed WHERE turno_fin > 3) = (SELECT count(*) FROM actividad) AND (SELECT count(*) FROM actividad) > 0,
                 'HISTORICAL: a 60-slot duration is placed and overflows the 3-slot day');
