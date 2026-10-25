-- expect: xfail
-- sqlstate: P0001
-- refs: generador/generar.sql:467-494; VB6 TAncora.cls ~6214-6225 (sorts correctly); assessment 6.4
-- Intended (as in VB6): ascending by total, so {5,3,4,1} -> activities 4,2,3,1.
\ir ../fixtures/prioriza_order.sql
SELECT tst.check(tst.prioriza_order(tst.totals_table('{5,3,4,1}'), 4) = '{4,2,3,1}', 'ascending by total');
