-- expect: pass
-- timeout: 3s
-- refs: prioriza/nivelar.sql; #23
-- Inputs that used to make the levelling loop run forever now terminate:
-- nothing to level gives no levels, and a missing (NULL) value is rejected
-- with SQLSTATE 22004 (null_value_not_allowed). Integer input is unchanged.
SELECT tst.check(prioriza.nivelar('{}', true) = '{}'::int[], 'empty input: no levels');
SELECT tst.check(prioriza.nivelar(NULL, false) = '{}'::int[], 'NULL input: no levels');
DO $$
BEGIN
  PERFORM prioriza.nivelar('{1,NULL,2}', true);
  RAISE EXCEPTION 'NULL element accepted';
EXCEPTION WHEN null_value_not_allowed THEN
  NULL;
END $$;
SELECT tst.check(prioriza.nivelar('{3,1,1,2}', true) = '{3,1,1,2}', 'HISTORICAL integer levels, minimum first');
SELECT tst.check(prioriza.nivelar('{3,1,1,2}', false) = '{1,3,3,2}', 'HISTORICAL integer levels, maximum first');
SELECT tst.check(prioriza.nivelar('{2.5,1,2.5}', false) = '{1,2,1}', 'fractional ties share a level');
