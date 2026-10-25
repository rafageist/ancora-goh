-- expect: pass
-- timeout: 8s
-- refs: generador/generar.sql (day loop, EXIT WHEN kk = CicloFin); assessment 6.5; #23
-- With teacher TA restricted on all of day 1 the day search range of a stage
-- can be empty (CicloIni > CicloFin). Until #23 the day loop then never ended.
-- Now an empty range is not searched and generar() returns. Observed result
-- (recorded, not judged here): T2 is placed for both groups on day 1 and T1
-- stays unplaced, because its search range is empty. Whether day 2 should
-- be offered to T1 is a placement question outside #23.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad)
     SELECT 'RPX' || t, 'TA', 'PER', 1, t, 1, false FROM generate_series(1, 3) t;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check((SELECT string_agg(plantilla || '/' || grupo, ',' ORDER BY plantilla, grupo) FROM actividad) = 'T2/G1,T2/G2',
                 'generar() returned; T2 placed for both groups, T1 unplaced');
