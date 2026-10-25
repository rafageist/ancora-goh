-- expect: pass
-- timeout: 3s
-- refs: prioriza/nivelar.sql (optimo); #23
-- The running optimum used to be an INTEGER, so a non-integer value never
-- equalled it and the levelling loop never ended. It is now NUMERIC, and
-- integer input levels exactly as before (case 040).
SELECT tst.check(prioriza.nivelar('{1.5,2}', true) = '{1,2}', 'fractional values levelled');
