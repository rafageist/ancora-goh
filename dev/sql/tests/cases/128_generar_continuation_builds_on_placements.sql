-- expect: pass
-- refs: generador/generar.sql:1004-1010 (esta_colocada), and_mpi.sql:74, valor_restriccion_generica.sql:58 (occupancy);
--       legacy-parity-inventory.md PAR-085, PAR-087, D7, D8; docs/modernization/engine-dependency-closure.md section 5
-- Continuation (legacy): generar() keeps every existing placement and builds on it.
-- 1. A partial run (cohort B2 restricted everywhere) places only G1's demands.
-- 2. After the restriction is lifted, a second run keeps the first run's rows
--    unchanged (same ids, same cells) and places only the missing demands.
-- 3. A placement that exists before the run in a cell the run would not choose
--    (T1/G1 at day 2 slot 3) is kept; the other demands are placed around it,
--    and it anchors the day range of the later templates of its breakdown.
--    Deleting the scope's placements first (what the Drupal worker does today,
--    D7) gives a different timetable.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
INSERT INTO restriccion_de_brigada (id, brigada, periodo, dia, turno, valor, por_actividad)
     SELECT 'RB2' || d || t, 'B2', 'PER', d, t, 1, false FROM generate_series(1, 2) d, generate_series(1, 3) t;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check((SELECT string_agg(format('%s/%s@%s.%s', plantilla, grupo, dia, turno), ' ' ORDER BY plantilla, grupo) FROM actividad)
                 = 'T1/G1@1.1 T2/G1@2.1', 'partial run: only G1 is placed');
CREATE TEMP TABLE run1 AS SELECT id, plantilla, grupo, profesor, lugar, dia, turno, momento_de_colocacion FROM actividad;
DELETE FROM restriccion_de_brigada WHERE id LIKE 'RB2%';
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check(NOT EXISTS (SELECT * FROM run1 EXCEPT SELECT id, plantilla, grupo, profesor, lugar, dia, turno, momento_de_colocacion FROM actividad),
                 'the first run''s rows are kept unchanged (ids, cells, placement moment)');
SELECT tst.check((SELECT count(*) FROM actividad) = 4 AND (SELECT count(*) FROM tst.collision) = 0, 'the second run places the missing demands without collisions');
SELECT tst.check((SELECT string_agg(format('%s/%s@%s.%s', plantilla, grupo, dia, turno), ' ' ORDER BY plantilla, grupo) FROM actividad)
                 = 'T1/G1@1.1 T1/G2@1.2 T2/G1@2.1 T2/G2@2.2', 'HISTORICAL cells of the continued timetable');

-- 3. a placement that exists before the run
DELETE FROM actividad;
SELECT agrega_actividad('T1', 'TA', 'L1', 2, 3, 'G1', '{}'::varchar[], 0, 'u1');
CREATE TEMP TABLE pre AS SELECT id, dia, turno FROM actividad;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check((SELECT count(*) FROM actividad a JOIN pre p USING (id, dia, turno)) = 1, 'the existing placement is kept in its cell');
SELECT tst.check((SELECT string_agg(format('%s/%s@%s.%s', plantilla, grupo, dia, turno), ' ' ORDER BY plantilla, grupo) FROM actividad)
                 = 'T1/G1@2.3 T1/G2@2.1' AND (SELECT count(*) FROM tst.collision) = 0,
                 'HISTORICAL: the run builds around it; with respetar_orden, T2 must come at least min_dias after T1 (rango.sql:41-52), so T2 stays unplaced for both groups');
CREATE TEMP TABLE continued AS SELECT plantilla, grupo, dia, turno FROM actividad;
-- what deleting the scope's placements before the run gives instead (D7)
DELETE FROM actividad;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check(EXISTS (SELECT * FROM continued EXCEPT SELECT plantilla, grupo, dia, turno FROM actividad)
                 AND (SELECT count(*) FROM actividad) = 4,
                 'delete-then-run (D7) gives a different timetable than continuation (4 placements instead of 2)');
