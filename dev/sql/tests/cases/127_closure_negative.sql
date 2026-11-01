-- expect: pass
-- timeout: 120s
-- refs: docs/modernization/engine-dependency-closure.md (sections 4 and 7); tests/fixtures/closure.sql;
--       generador/generar.sql:869, :1004, :250-254; busca_lugar_mas_utilizado_por.sql:19; dia_minimo_impacto.sql:58-60;
--       tiempo/restriccion/valor_restriccion_generica.sql:73 (rperiodo always NULL: real-date branch inert);
--       tiempo/restriccion/condicional/valor_restriccion_condicional.sql:12-34
-- NEGATIVE closure. On the world of case 125 (other scopes placed), the
-- outcome of generar('E', 'N', 'PER', ...) CHANGES when a row that the closure
-- requires is removed, and does NOT change when a row outside the closure is
-- removed. Three rows that the closure requires make the run fail; the pruned
-- database fails the same way, and removing the row makes the run succeed.
\ir ../fixtures/closure_world.sql
\ir ../fixtures/closure.sql
SELECT generar('EB', 'NB', 'QB', 1, false, 0, false, false);
SELECT generar('E', 'N', 'QER', 1, false, 0, false, false);
SELECT generar('F', 'N', 'PER', 1, false, 0, false, false);
BEGIN; SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS base_d \gset
ROLLBACK;
BEGIN; SELECT tst.cl_run('E', 'N', 'PER', 1, false, 1, false, true) AS base_l \gset
ROLLBACK;

-- REQUIRED rows: removing them changes the outcome.
-- R1: the shared teacher's placement of another specialty in the same period
--     (occupancy restriccion_de_profesor and load, generar.sql:869).
BEGIN; DELETE FROM actividad WHERE plantilla = 'U1';
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS r1_d \gset
ROLLBACK;
BEGIN; DELETE FROM actividad WHERE plantilla = 'U1';
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 1, false, true) AS r1_l \gset
ROLLBACK;
SELECT tst.check(:'r1_d' <> :'base_d' AND :'r1_l' <> :'base_l', 'R1 required: the shared teacher''s placement in the period');
-- R2: group G1's placement in a room of T1 in ANOTHER PLAN (plan P2, period
--     QER): read by busca_lugar_mas_utilizado_por with buscalugar = false only.
BEGIN; DELETE FROM actividad WHERE plantilla = 'TQ' AND grupo = 'G1';
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS r2_d \gset
ROLLBACK;
BEGIN; DELETE FROM actividad WHERE plantilla = 'TQ' AND grupo = 'G1';
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 1, false, true) AS r2_l \gset
ROLLBACK;
SELECT tst.check(:'r2_d' <> :'base_d', 'R2 required with buscalugar false: room usage of the group in another plan');
SELECT tst.check(:'r2_d' LIKE 'placements=[T1/G1/TA/L1@%' AND :'base_d' LIKE 'placements=[T1/G1/TA/L2@%', 'R2: the group takes the room it used in the other plan');
SELECT tst.check(:'r2_l' = :'base_l', 'R2 not read with buscalugar true (the legacy window default)');

-- REQUIRED rows that make the run fail; the pruned database fails the same way.
-- F1: a brigade of the specialty and level restricted in ANOTHER period at a
--     day the period does not have (dia_minimo_impacto.sql:58-60, 2202E).
BEGIN;
INSERT INTO restriccion_de_brigada (id, brigada, periodo, dia, turno) VALUES ('XF1', 'B1', 'PER2', 5, 1);
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS f1_full \gset
SELECT tst.cl_keep('E', 'N', 'PER', false); SELECT tst.cl_prune();
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS f1_cl \gset
DELETE FROM restriccion_de_brigada WHERE id = 'XF1';
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS f1_removed \gset
ROLLBACK;
SELECT tst.check(:'f1_full' = 'ERROR 2202E' AND :'f1_cl' = :'f1_full' AND :'f1_removed' NOT LIKE 'ERROR%', 'F1 required: an out-of-range brigade restriction of another period fails the run');
-- F2: a subject of the specialty and level without a template in the period
--     (generar.sql:250-254: the activity matrix loop never ends, 2202E).
BEGIN;
INSERT INTO asignatura (id, descripcion, especialidad, nivel, creador) VALUES ('A3', 'Subject without templates', 'E', 'N', 'u1');
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS f2_full \gset
SELECT tst.cl_keep('E', 'N', 'PER', false); SELECT tst.cl_prune();
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS f2_cl \gset
DELETE FROM asignatura WHERE id = 'A3';
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS f2_removed \gset
ROLLBACK;
SELECT tst.check(:'f2_full' = 'ERROR 2202E' AND :'f2_cl' = :'f2_full' AND :'f2_removed' NOT LIKE 'ERROR%', 'F2 required: a scope subject without templates in the period fails the run');
-- F3: an active conditional restriction of ANOTHER plan aimed at the period
--     (valor_restriccion_condicional.sql: undefined names, 42703).
BEGIN;
INSERT INTO restriccion_condicional (id, planificacion, activa, consec_periodo, consec_profesor, siempre) VALUES ('RCX', 'P2', true, 'PER', 'TC', false);
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS f3_full \gset
SELECT tst.cl_keep('E', 'N', 'PER', false); SELECT tst.cl_prune();
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS f3_cl \gset
DELETE FROM restriccion_condicional WHERE id = 'RCX';
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS f3_removed \gset
ROLLBACK;
SELECT tst.check(:'f3_full' = 'ERROR 42703' AND :'f3_cl' = :'f3_full' AND :'f3_removed' NOT LIKE 'ERROR%', 'F3 required: a conditional restriction aimed at the period, from another plan, fails the run');

-- NOT REQUIRED rows: removing them does not change the outcome.
-- N1: everything of the other creator (plan, period, periodicity on the same
--     dates and hours, classes with the same priorities, placements).
BEGIN; DELETE FROM seguridad.usuario WHERE id = 'u2';
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS n1 \gset
ROLLBACK;
-- N2: the shared teacher's occupancy in another plan, same dates and hours.
BEGIN; DELETE FROM restriccion_de_profesor WHERE periodo = 'QER';
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS n2 \gset
ROLLBACK;
-- N3: every real-date restriction row (all objects, all periods).
BEGIN; DELETE FROM restriccion_real;
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS n3 \gset
ROLLBACK;
-- N4: the periodicity of every other period.
BEGIN; DELETE FROM periodicidad WHERE periodo <> 'PER';
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS n4 \gset
ROLLBACK;
-- N5: brigade occupancy of the scope's cohorts in another plan, within the
--     period's bounds (read by dia_minimo_impacto; its value is not used).
BEGIN; DELETE FROM restriccion_de_brigada WHERE periodo = 'QER';
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS n5 \gset
ROLLBACK;
-- N6: specialty restrictions of the period are stored but never read.
BEGIN; INSERT INTO restriccion_de_especialidad (id, especialidad, periodo, dia, turno)
  SELECT 'XE' || d || t, 'E', 'PER', d, t FROM generate_series(1, 3) d, generate_series(1, 4) t;
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS n6 \gset
ROLLBACK;
-- N7: an active inheritance row between two resources the scope does not use
--     (the failing function is evaluated only for rows of the checked object).
BEGIN; INSERT INTO otro_recurso (id, nombre, creador) VALUES ('R1', 'Projector 1', 'u1'), ('R2', 'Projector 2', 'u1');
INSERT INTO herencia_de_restricciones (id, planificacion, activa, hijo_otro_recurso, padre_otro_recurso) VALUES ('HX', 'P2', true, 'R1', 'R2');
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS n7 \gset
ROLLBACK;
-- N8: an active conditional restriction aimed at another period.
BEGIN; INSERT INTO restriccion_condicional (id, planificacion, activa, consec_periodo, consec_profesor, siempre) VALUES ('RCQ', 'P2', true, 'QER', 'TA', false);
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS n8 \gset
ROLLBACK;
SELECT tst.check(:'n1' = :'base_d', 'N1 not required: the other creator''s rows');
SELECT tst.check(:'n2' = :'base_d', 'N2 not required: the shared teacher''s occupancy in another plan');
SELECT tst.check(:'n3' = :'base_d', 'N3 not required: real-date restrictions');
SELECT tst.check(:'n4' = :'base_d', 'N4 not required: periodicity of other periods');
SELECT tst.check(:'n5' = :'base_d', 'N5 not required: in-range brigade rows of other periods');
SELECT tst.check(:'n6' = :'base_d', 'N6 not read: specialty restrictions');
SELECT tst.check(:'n7' = :'base_d', 'N7 not required: inheritance rows of objects outside the scope');
SELECT tst.check(:'n8' = :'base_d', 'N8 not required: conditional restrictions aimed at another period');
SELECT format('METRIC required_changed=R1,R2 fail_same_in_closure=F1:%s,F2:%s,F3:%s not_required_unchanged=N1..N8', :'f1_full', :'f2_full', :'f3_full');
