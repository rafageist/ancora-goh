-- expect: pass
-- refs: generador/generar.sql:456-496; assessment 6.4
-- Historical ordering of activities by horizontal Prioriza totals (column 9),
-- run from the installed generar() source (fixtures/prioriza_order.sql).
-- The selection sort swaps the totals by row *and* the index array, which
-- detaches totals from activities.
\ir ../fixtures/prioriza_order.sql
SELECT tst.check(tst.prioriza_order(tst.totals_table('{5,3,4,1}'), 4) = '{4,3,1,2}', 'HISTORICAL order for {5,3,4,1}');
SELECT tst.check(tst.prioriza_order(tst.totals_table('{1,2,3}'), 3)   = '{1,2,3}', 'already sorted');
SELECT tst.check(tst.prioriza_order(tst.totals_table('{3,2,1}'), 3)   = '{3,2,1}', 'reverse order');
