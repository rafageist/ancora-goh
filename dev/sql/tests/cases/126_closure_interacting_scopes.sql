-- expect: pass
-- timeout: 90s
-- refs: docs/modernization/engine-dependency-closure.md (sections 4.3 and 7); tests/fixtures/closure.sql;
--       organizacion/dame_grupos.sql:6 (groups of every plan, Q-02); generador/generar.sql:869, tiempo/restriccion/valor_restriccion_generica.sql:58
-- POSITIVE closure, two interacting requests run in sequence. For each pair,
-- both runs give the same placements and DML side effects on the full database
-- and on the database pruned to the UNION of the two closures, computed before
-- the first run (the second run reads what the first one wrote):
--   (c) two creators: Bob's (EB, NB, QB), then Alice's (E, N, PER);
--   (b) one creator, two plans: (E, N, QER) of plan P2, then (E, N, PER) of plan P;
--   (a) two specialties sharing teacher TA in one period: (F, N, PER), then (E, N, PER).
-- The second run of (a) differs from the same request without the first run:
-- the interaction is real. Options: as Drupal sends them (buscalugar false).
\ir ../fixtures/closure_world.sql
\ir ../fixtures/closure.sql

-- (c) two creators
BEGIN;
SELECT tst.cl_run('EB', 'NB', 'QB', 1, false, 0, false, false) AS c1_full \gset
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS c2_full \gset
ROLLBACK;
BEGIN;
SELECT tst.cl_keep('EB', 'NB', 'QB', false) AS c_kept1 \gset
SELECT tst.cl_keep('E', 'N', 'PER', false, false) AS c_kept \gset
SELECT tst.cl_prune();
SELECT tst.cl_run('EB', 'NB', 'QB', 1, false, 0, false, false) AS c1_cl \gset
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS c2_cl \gset
ROLLBACK;
BEGIN;
SELECT tst.cl_keep('E', 'N', 'PER', false);
SELECT tst.cl_prune();
SELECT tst.check((SELECT count(*) FROM seguridad.usuario WHERE id = 'u2') = 0, 'no row of the other creator is in Alice''s closure');
ROLLBACK;
SELECT tst.check(:'c1_full' = :'c1_cl' AND :'c2_full' = :'c2_cl', '(c) two creators: same outcome on the union of the closures');
SELECT generar('EB', 'NB', 'QB', 1, false, 0, false, false);

-- (b) one creator, two plans
BEGIN;
SELECT tst.cl_run('E', 'N', 'QER', 1, false, 0, false, false) AS b1_full \gset
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS b2_full \gset
ROLLBACK;
BEGIN;
SELECT tst.cl_keep('E', 'N', 'QER', false);
SELECT tst.cl_keep('E', 'N', 'PER', false, false) AS b_kept \gset
SELECT tst.cl_prune();
SELECT tst.cl_run('E', 'N', 'QER', 1, false, 0, false, false) AS b1_cl \gset
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS b2_cl \gset
ROLLBACK;
SELECT tst.check(:'b1_full' = :'b1_cl' AND :'b2_full' = :'b2_cl', '(b) one creator, two plans: same outcome on the union of the closures');
SELECT tst.check(:'b1_full' LIKE '%TQ/GQ/TA/L2@%' AND :'b2_full' LIKE '%T1/GQ/TA/%', 'HISTORICAL: the plan 2 group is scheduled in both plans (Q-02)');
SELECT generar('E', 'N', 'QER', 1, false, 0, false, false);

-- (a) two specialties sharing a teacher in the same period
BEGIN;
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS e_alone \gset
ROLLBACK;
BEGIN;
SELECT tst.cl_run('F', 'N', 'PER', 1, false, 0, false, false) AS a1_full \gset
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS a2_full \gset
ROLLBACK;
BEGIN;
SELECT tst.cl_keep('F', 'N', 'PER', false);
SELECT tst.cl_keep('E', 'N', 'PER', false, false) AS a_kept \gset
SELECT tst.cl_prune();
SELECT tst.cl_run('F', 'N', 'PER', 1, false, 0, false, false) AS a1_cl \gset
SELECT tst.cl_run('E', 'N', 'PER', 1, false, 0, false, false) AS a2_cl \gset
ROLLBACK;
SELECT tst.check(:'a1_full' = :'a1_cl' AND :'a2_full' = :'a2_cl', '(a) shared teacher: same outcome on the union of the closures');
SELECT tst.check(:'a1_full' LIKE 'placements=[U1/H1/TA/L4@1.1]%', 'the other specialty takes TA at day 1 slot 1');
SELECT tst.check(:'e_alone' LIKE 'placements=[T1/G1/TA/L2@1.1 %', 'alone, T1 of G1 takes day 1 slot 1');
SELECT tst.check(:'a2_full' LIKE 'placements=[T1/G1/TA/L2@1.2 %', 'after the other specialty, T1 of G1 moves to slot 2');
SELECT format('METRIC union_closure_rows two_creators=%s two_plans=%s shared_teacher=%s (closure of Bob alone %s)', :c_kept, :b_kept, :a_kept, :c_kept1);
