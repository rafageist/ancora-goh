-- expect: pass
-- refs: prioriza/nivelar.sql, prioriza/nivelar_matrix.sql; generador/generar.sql:425 (criteria {t,f,t,f,f,f,t,f})
-- Prioriza levelling: dense ranking per criterion column. criterio = true ranks
-- the minimum first, false the maximum first; ties share a level.
SELECT tst.check(prioriza.nivelar('{5,3,4,1}', true)  = '{4,2,3,1}', 'min first');
SELECT tst.check(prioriza.nivelar('{5,3,4,1}', false) = '{1,3,2,4}', 'max first');
SELECT tst.check(prioriza.nivelar('{2,2,1}', true)    = '{2,2,1}', 'ties share a level');
SELECT tst.check(prioriza.nivelar('{7}', true)        = '{1}', 'single value');
SELECT tst.check(prioriza.nivelar_matrix('{{5,1},{3,1},{4,0}}', '{t,f}') = '{{3,1},{1,1},{2,2}}', 'matrix: per-column levelling');
