-- expect: pass
-- refs: generador/esquema_generador.sql:9-24; generador/generar.sql:198-215 (the NULL-order check runs before the first
--       cancellation check); model/generator/CancelarGeneradorModel.php:33 (the legacy handler only INSERTs);
--       legacy-parity-inventory.md PAR-086, D12; tests/concurrency/cancel_midflight.sh (late-row)
-- HISTORICAL (D12 quirk): legacy never clears a cancellation request. A row
-- that arrives after its run finished stays, and stops the NEXT run of that
-- scope at its first check: that run places nothing and consumes the row. The
-- run after it places everything.
-- NEGATIVE: a leftover row of another scope does not stop this scope.
-- The E2E fixture's owner (the installer's seed user, not loaded by the suite).
INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena) VALUES ('admin-superuser', 'admin', '-', 'admin@example.org', 'admin', 'not-a-real-hash');
\ir ../../../e2e/fixture.sql
\ir ../../../e2e/fixture-http-steps.sql
INSERT INTO generador.cancelar (especialidad, nivel, periodo) VALUES ('E', 'N', 'OTHER');
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check((SELECT count(*) FROM actividad) = 8, 'a leftover row of another scope does not stop the run');
SELECT tst.check((SELECT count(*) FROM generador.cancelar) = 1, 'and is left in place');
DELETE FROM actividad;
DELETE FROM generador.cancelar;
-- The request of a run that already finished ("late row").
INSERT INTO generador.cancelar (especialidad, nivel, periodo) VALUES ('E', 'N', 'PER');
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check((SELECT count(*) FROM actividad) = 0, 'HISTORICAL: the next run stops at its first check and places nothing');
SELECT tst.check((SELECT count(*) FROM generador.cancelar) = 0, 'HISTORICAL: and consumes the leftover row');
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check((SELECT count(*) FROM actividad) = 8, 'the run after it places everything');
