-- expect: pass
-- refs: generador/generar.sql; assessment §6 positive verifications
-- generar(specialty, level, period, forma=1, parche=false, compactar=0,
-- parche1=false, buscalugar=false) on 2 templates x 2 groups places all 4
-- demands without collisions. The exact cells are the historical baseline:
-- directional scanning leaves a gap (slots 1 and 3) between the two groups.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check((SELECT count(*) FROM tst.demand) = 4, '4 demands');
SELECT tst.check((SELECT count(*) FROM tst.placed) = 4, '4 placements');
SELECT tst.check((SELECT count(*) FROM tst.collision) = 0, 'no collisions');
SELECT tst.check((SELECT string_agg(format('%s/%s/%s/%s@%s.%s', plantilla, grupo, profesor, lugar, dia, turno), ' ' ORDER BY plantilla, grupo) FROM actividad)
               = 'T1/G1/TA/L1@1.1 T1/G2/TA/L1@1.3 T2/G1/TB/L2@2.1 T2/G2/TB/L2@2.3', 'historical placement cells');
SELECT tst.check((SELECT count(*) FROM restriccion_de_profesor WHERE por_actividad) = 4, 'teacher occupancy recorded per placement');
