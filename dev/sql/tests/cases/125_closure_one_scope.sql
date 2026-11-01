-- expect: pass
-- timeout: 60s
-- refs: docs/modernization/engine-dependency-closure.md (sections 4 and 7); tests/fixtures/closure.sql (tst.cl_keep);
--       generador/generar.sql:869 (teacher load in the period), :1309-1330 (room choice), busca_lugar_mas_utilizado_por.sql:19,
--       dia_minimo_impacto.sql:58, valor_restriccion_generica.sql:73 (real-date branch inert)
-- POSITIVE closure, one scope. In a database with other scopes already placed
-- (the same specialty and level in another plan of the creator, another
-- specialty sharing teacher TA in the same period, another creator),
-- generar('E', 'N', 'PER', ...) gives the same placements and the same DML
-- side effects on the full database and on the database pruned to the closure
-- of the request, for the options Drupal sends today (compactar 0,
-- buscalugar false) and for the options the legacy window posts by default
-- (compactar 1, buscalugar true; PAR-082/083, D11).
\ir ../fixtures/closure_world.sql
\ir ../fixtures/closure.sql
SELECT generar('EB', 'NB', 'QB', 1, false, 0, false, false);
SELECT generar('E', 'N', 'QER', 1, false, 0, false, false);
SELECT generar('F', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.row_count() AS rows_full \gset

-- Options as Drupal sends them (JobOptions defaults).
BEGIN;
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS full_d \gset
SELECT tst.cl_scanned() AS reads_d \gset
ROLLBACK;
BEGIN;
SELECT tst.cl_keep('E', 'N', 'PER', false) AS kept_d \gset
SELECT tst.cl_prune() AS pruned_d \gset
SELECT tst.check((SELECT count(*) FROM planificacion WHERE id = 'Q') = 0, 'the other creator''s plan is outside the closure');
SELECT tst.check((SELECT count(*) FROM restriccion_real) = 0, 'no real-date restriction row is in the closure');
SELECT tst.check((SELECT count(*) FROM restriccion_de_profesor WHERE periodo = 'QER') = 0, 'teacher occupancy of another plan is outside the closure');
SELECT tst.check((SELECT count(*) FROM actividad WHERE plantilla = 'TQ' AND lugar = 'L2') = 3, 'room usage of another plan is inside the closure (buscalugar false)');
SELECT tst.check((SELECT count(*) FROM actividad WHERE plantilla = 'U1') = 1, 'the shared teacher''s placement of another specialty in the period is inside the closure');
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS closure_d \gset
ROLLBACK;

-- Options as the legacy generator window posts them by default.
BEGIN;
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 1, false, true) AS full_l \gset
ROLLBACK;
BEGIN;
SELECT tst.cl_keep('E', 'N', 'PER', true) AS kept_l \gset
SELECT tst.cl_prune() AS pruned_l \gset
-- With buscalugar true the room usage of other periods is not read; the QER
-- placements stay only as parents (FK) of the brigade rows of dia_minimo_impacto.
SELECT tst.check((SELECT count(*) FROM restriccion_de_profesor WHERE periodo = 'QER') = 0, 'teacher occupancy of another plan is outside the closure');
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 1, false, true) AS closure_l \gset
ROLLBACK;

SELECT format('METRIC rows_full=%s closure_drupal_options=%s closure_legacy_options=%s', :rows_full, :kept_d, :kept_l);
SELECT format('METRIC full_drupal_options=%s', :'full_d');
SELECT format('METRIC full_legacy_options=%s', :'full_l');
SELECT format('METRIC scanned=%s', :'reads_d');
SELECT tst.check(:'full_d' LIKE 'placements=[T1/G1/TA/L2@1.2 T1/G2/TA/L2@1.4 T1/GQ/TA/L2@1.3 T3/GL/TB/L3@1.1]%', 'historical cells, Drupal options');
SELECT tst.check(:'full_l' LIKE 'placements=[T1/G1/TA/L1@1.2 T1/G2/TA/L1@1.4 T1/GQ/TA/L1@1.3 T3/GL/TB/L3@1.1]%', 'historical cells, legacy window options');
SELECT tst.check(:'full_d' = :'closure_d', 'closure, Drupal options: same placements, inserted rows and DML counters');
SELECT tst.check(:'full_l' = :'closure_l', 'closure, legacy window options: same placements, inserted rows and DML counters');
SELECT tst.check(:kept_d < :rows_full AND :kept_l <= :kept_d, 'the closures are smaller than the database');
