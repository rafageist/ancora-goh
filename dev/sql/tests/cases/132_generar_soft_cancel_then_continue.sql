-- expect: pass
-- refs: generador/esquema_generador.sql:9-24 (fue_cancelado consumes the row); generador/generar.sql:1004-1010 (esta_colocada);
--       legacy-parity-inventory.md PAR-085, PAR-086, D7, D8, D12; control/custom/cancelar_generacion.php
-- Cancellation keeps placed work, and the next run continues from it (legacy).
-- A cancellation request that arrives while generar() runs (here: inserted by
-- a TEST-ONLY trigger after the 3rd placement, in the same statement, as the
-- legacy window's request reaches the next of the 51 checks) makes generar()
-- return normally: the 3 placements are kept. The row is consumed. The next
-- run keeps those 3 (same ids and cells) and places the other 5 demands.
-- NEGATIVE: a request for ANOTHER scope does not stop the run.
-- The E2E fixture's owner (the installer's seed user, not loaded by the suite).
INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena) VALUES ('admin-superuser', 'admin', '-', 'admin@example.org', 'admin', 'not-a-real-hash');
\ir ../../../e2e/fixture.sql
\ir ../../../e2e/fixture-http-steps.sql
CREATE FUNCTION pg_temp.cancel_after_third() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  IF (SELECT count(*) FROM actividad) = 3 THEN
    INSERT INTO generador.cancelar (especialidad, nivel, periodo) VALUES (TG_ARGV[0], 'N', 'PER');
  END IF;
  RETURN NULL;
END $$;

-- NEGATIVE first: the request names another specialty; nothing stops the run.
BEGIN;
CREATE TRIGGER t_cancel AFTER INSERT ON actividad FOR EACH ROW EXECUTE FUNCTION pg_temp.cancel_after_third('OTHER');
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check((SELECT count(*) FROM actividad) = 8, 'a request for another scope does not stop the run (8/8)');
SELECT tst.check((SELECT count(*) FROM generador.cancelar WHERE especialidad = 'OTHER') = 1, 'and it is left in place');
ROLLBACK;

CREATE TRIGGER t_cancel AFTER INSERT ON actividad FOR EACH ROW EXECUTE FUNCTION pg_temp.cancel_after_third('E');
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check((SELECT count(*) FROM actividad) = 3, 'the cancelled run returns normally and keeps the 3 placements made so far');
SELECT tst.check((SELECT count(*) FROM generador.cancelar) = 0, 'the request is consumed');
SELECT tst.check((SELECT count(*) FROM tst.collision) = 0, 'the kept partial timetable has no collision');
CREATE TEMP TABLE first_run AS SELECT id, plantilla, grupo, profesor, lugar, dia, turno FROM actividad;
DROP TRIGGER t_cancel ON actividad;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check(NOT EXISTS (SELECT * FROM first_run EXCEPT SELECT id, plantilla, grupo, profesor, lugar, dia, turno FROM actividad),
                 'the next run keeps the cancelled run''s placements unchanged (ids and cells)');
SELECT tst.check((SELECT count(*) FROM actividad) = 8 AND (SELECT count(*) FROM tst.collision) = 0, 'and places the other 5 demands');
SELECT tst.check((SELECT string_agg(a.plantilla || '/' || a.grupo || '/' || a.dia || '.' || a.turno, ' ' ORDER BY a.plantilla, a.grupo) FROM actividad a)
                 = 'A1/G1/1.1 A1/G2/1.4 A2/G1/2.1 A2/G2/2.4 B1/G1/1.2 B1/G2/1.1 B2/G1/2.2 B2/G2/2.3',
                 'HISTORICAL cells of the continued timetable: B1/G2 takes 1.1, not 1.3 as in one uncancelled run (the E2E list)');
