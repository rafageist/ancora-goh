-- expect: xfail
-- sqlstate: P0001
-- refs: matrix/extract_boolean_column.sql; assessment 6.23
-- Intended: column 1 of a 2x3 matrix has 2 elements.
SELECT tst.check(matrix.extract_boolean_column('{{t,f,t},{t,t,f}}'::boolean[], 1) = '{t,t}', 'column has one element per row');
