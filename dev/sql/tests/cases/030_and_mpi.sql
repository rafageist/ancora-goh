-- expect: pass
-- refs: generador/and_mpi.sql, generador/posible_inicio.sql
-- MPI (matrix of possible starts) for a brigade list: days x slots booleans.
-- A brigade restriction removes that cell; restricting the only teacher does
-- not (historical defect 6.2, see 021).
\ir ../fixtures/base.sql
SELECT tst.check(and_mpi(ARRAY['B1']::varchar[], 2, 3, 'T1', 0, false) = '{{t,t,t},{t,t,t}}', 'free MPI is all true');
INSERT INTO restriccion_de_brigada (id, brigada, periodo, dia, turno, valor, por_actividad) VALUES ('RB1', 'B1', 'PER', 2, 2, 1, false);
SELECT tst.check(and_mpi(ARRAY['B1']::varchar[], 2, 3, 'T1', 0, false) = '{{t,t,t},{t,f,t}}', 'brigade restriction removes (2,2)');
SELECT tst.check(and_mpi(ARRAY['B1','B2']::varchar[], 2, 3, 'T1', 0, false) = '{{t,t,t},{t,f,t}}', 'AND over brigades');
INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad) VALUES ('RP1', 'TA', 'PER', 1, 1, 1, false);
SELECT tst.check(and_mpi(ARRAY['B2']::varchar[], 2, 3, 'T1', 0, false) = '{{t,t,t},{t,t,t}}', 'HISTORICAL: only-teacher restriction not reflected');
