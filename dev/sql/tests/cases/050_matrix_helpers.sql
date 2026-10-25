-- expect: pass
-- refs: matrix/set_matrix_in_cube.sql:7, matrix/extract_boolean_column.sql; assessment 6.22, 6.23
-- Historical behavior of two matrix helpers used by generar().
SELECT tst.check(matrix.set_matrix_in_cube('{{t}}'::boolean[], 1) IS NULL, 'HISTORICAL: OUT parameter makes set_matrix_in_cube return NULL');
SELECT tst.check(matrix.extract_boolean_column('{{t,f,t},{t,t,f}}'::boolean[], 1) = '{t,t,NULL}', 'HISTORICAL: one extra element');
SELECT tst.check(matrix.get_length_of('{{1,2,3},{4,5,6}}'::int[], 1) = 2 AND matrix.get_length_of('{{1,2,3},{4,5,6}}'::int[], 2) = 3, 'get_length_of');
