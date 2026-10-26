-- expect: pass
-- refs: actividad/actividad.sql (CHECK plantilla_exige_profesor); tiempo/restriccion/nominal/restringe_segun_actividad.sql;
--       tiempo/restriccion/nominal/restriccion_de_profesor.sql (profesor NOT NULL); legacy-parity-inventory.md PAR-037, quirk Q-05
-- HISTORICAL: a template with exige_profesor = false (or exige_lugar = false) cannot be placed.
-- * The activity CHECK allows profesor NULL, but the occupancy RULE then
--   inserts restriccion_de_profesor with profesor NULL (23502), so a direct
--   insert fails;
-- * generar() picks no teacher for it, reaches the same insert and fails with
--   23502; the run is one statement, so nothing at all is kept, including the
--   templates that do require a teacher.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
UPDATE plantilla_de_actividad SET exige_profesor = false WHERE id = 'T1';
DO $$ BEGIN
  INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES ('N1', 'T1', 'G1', NULL, 'L1', 2, 2, 'u1');
  RAISE EXCEPTION 'activity without teacher was accepted';
EXCEPTION WHEN not_null_violation THEN NULL;
END $$;
DO $$ BEGIN
  PERFORM generar('E', 'N', 'PER', 1, false, 0, false, false);
  RAISE EXCEPTION 'generar() succeeded with a template that requires no teacher';
EXCEPTION WHEN not_null_violation THEN NULL;
END $$;
SELECT tst.check((SELECT count(*) FROM actividad) = 0, 'HISTORICAL: the failed run keeps no placement');
-- The same holds for exige_lugar = false (restriccion_de_lugar.lugar is NOT NULL).
UPDATE plantilla_de_actividad SET exige_profesor = true, exige_lugar = false WHERE id = 'T1';
DO $$ BEGIN
  INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES ('N2', 'T1', 'G1', 'TA', NULL, 2, 2, 'u1');
  RAISE EXCEPTION 'activity without place was accepted';
EXCEPTION WHEN not_null_violation THEN NULL;
END $$;
DO $$ BEGIN
  PERFORM generar('E', 'N', 'PER', 1, false, 0, false, false);
  RAISE EXCEPTION 'generar() succeeded with a template that requires no place';
EXCEPTION WHEN not_null_violation THEN NULL;
END $$;
SELECT tst.check((SELECT count(*) FROM actividad) = 0, 'HISTORICAL: no placement without a place either');
