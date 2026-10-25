-- expect: xfail
-- sqlstate: 57014
-- timeout: 3s
-- refs: prioriza/nivelar.sql (optimo is INTEGER); found while writing this suite
-- Diagnostic: the running optimum is stored in an INTEGER, so a non-integer
-- value never equals it and the levelling loop never ends. generar() feeds
-- integer counts today, so this is latent. Intended: terminates with {1,2}.
SELECT tst.check(prioriza.nivelar('{1.5,2}', true) = '{1,2}', 'fractional values levelled');
