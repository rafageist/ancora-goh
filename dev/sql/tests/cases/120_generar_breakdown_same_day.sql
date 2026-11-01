-- expect: pass
-- refs: generador/generar.sql (rdesglose.respetar_orden = FALSE AND rdesglose.mismo_dia = FALSE block, ~l.1132-1140);
--       generador/rango.sql; legacy-parity-inventory.md PAR-034, quirk Q-04
-- Breakdown options in generar(), subject A with two templates T1, T2:
-- * respetar_orden = false, mismo_dia = true: all 4 demands are placed;
-- * HISTORICAL: respetar_orden = false, mismo_dia = false: only the first
--   template is placed per group. The "same day" check selects any activity
--   of the same breakdown and group, without filtering by day, so once T1 is
--   placed T2 is refused on every day. Nothing records the unplaced demands.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
UPDATE desglose SET respetar_orden = false, mismo_dia = true;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check((SELECT count(*) FROM tst.placed) = 4, 'mismo_dia = true: 4 placements');
DELETE FROM actividad;
UPDATE desglose SET respetar_orden = false, mismo_dia = false;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check((SELECT string_agg(plantilla || '/' || grupo, ' ' ORDER BY plantilla, grupo) FROM actividad) = 'T1/G1 T1/G2',
                 'HISTORICAL: mismo_dia = false places one template per group, on any day');
