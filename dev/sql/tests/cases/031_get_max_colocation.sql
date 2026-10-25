-- expect: pass
-- refs: generador/get_max_colocation.sql (examples in its header comment)
-- Maximum number of non-overlapping placements in a row of possible starts.
SELECT tst.check(get_max_colocation('{t,t,f,t}', 1) = 3, '{t,t,f,t} x1 = 3');
SELECT tst.check(get_max_colocation('{t,t,f,t}', 2) = 1, '{t,t,f,t} x2 = 1');
SELECT tst.check(get_max_colocation('{f,f,f}', 1) = 0, 'no possible start = 0');
SELECT tst.check(get_max_colocation('{t,t,t}', 3) = 1, 'full row x3 = 1');
SELECT tst.check(get_max_colocation('{f,t,t,t}', 1) = 3, 'documented example x1 = 3');
SELECT tst.check(get_max_colocation('{f,t,t,t}', 2) = 1, 'documented example x2 = 1');
SELECT tst.check(get_max_colocation('{f,t,t,t}', 3) = 1, 'documented example x3 = 1');
