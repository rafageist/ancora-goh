-- expect: xfail
-- sqlstate: 57014
-- timeout: 8s
-- refs: generador/generar.sql:1041-1047, 1439 (EXIT WHEN kk = CicloFin); assessment 6.5
-- Intended: generar() terminates (placing T2 elsewhere or reporting it).
-- With teacher TA restricted on all of day 1 the day search range becomes
-- empty (CicloFin = 0) and the day loop never ends. Bounded by statement_timeout.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad)
     SELECT 'RPX' || t, 'TA', 'PER', 1, t, 1, false FROM generate_series(1, 3) t;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
