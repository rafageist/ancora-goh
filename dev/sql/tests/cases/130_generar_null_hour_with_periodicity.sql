-- expect: pass
-- refs: tiempo/restriccion/real/actualiza_restriccion_real.sql:72-95 (dynamic SQL built by concatenation);
--       ancoragoh.sql:34-50 (real-date RULEs on the occupancy tables); periodo.sql (hora_inicial nullable);
--       legacy-parity-inventory.md PAR-011, PAR-072, A-1; docs/modernization/engine-dependency-closure.md section 4.1
-- HISTORICAL: the period's own start hour and periodicity are inputs that
-- decide whether a run can place anything. Every placement writes occupancy
-- rows, whose RULEs write one real-date row per periodicity of the period with
-- an EXECUTE string built by concatenation. With a NULL start hour (the
-- column default) the string is NULL and the run fails with 22004, keeping
-- nothing. Without periodicity rows no real-date row is written and the run
-- succeeds.
\ir ../fixtures/base.sql
UPDATE periodo SET hora_inicial = NULL WHERE id = 'PER';
DO $$
DECLARE st text := 'none';
BEGIN
  BEGIN
    PERFORM generar('E', 'N', 'PER', 1, false, 0, false, false);
  EXCEPTION WHEN OTHERS THEN st := SQLSTATE;
  END;
  PERFORM tst.check(st = '22004', 'HISTORICAL: NULL start hour with a periodicity row fails the run (22004), got ' || st);
END $$;
SELECT tst.check((SELECT count(*) FROM actividad) = 0, 'nothing kept');
DELETE FROM periodicidad WHERE periodo = 'PER';
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check((SELECT count(*) FROM actividad) = 2 AND (SELECT count(*) FROM restriccion_real) = 0,
                 'without periodicity the run places both demands and writes no real-date row');
