-- expect: pass
-- refs: generador/generar.sql:1465-1468 (unplaced work only reported as NOTICE); assessment 6.20
-- Over-constrained input: brigade B1 is unavailable everywhere. generar()
-- terminates, places what it can (G2) and records nothing about the unplaced
-- demands (only NOTICE output).
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
INSERT INTO restriccion_de_brigada (id, brigada, periodo, dia, turno, valor, por_actividad)
     SELECT 'RBX' || d || t, 'B1', 'PER', d, t, 1, false FROM generate_series(1, 2) d, generate_series(1, 3) t;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check((SELECT count(*) FROM tst.placed) = 2 AND NOT EXISTS (SELECT 1 FROM actividad WHERE grupo = 'G1'), 'only G2 placed');
SELECT tst.check((SELECT count(*) FROM tst.collision) = 0, 'no collisions');
