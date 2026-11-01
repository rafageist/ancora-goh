-- expect: pass
-- refs: www/js/app/client/wins/ancoragoh_client_wins_generador.js:5-11,312-316 (defaults posted: forma 1, parche false,
--       compactar 1, parche1 false, buscalugar true); generador/generar.sql:585 (compactar), :1309 (buscalugar);
--       modern/.../ancora_engine/src/Job/JobOptions.php:24-28 (Drupal defaults compactar 0, buscalugar false);
--       legacy-parity-inventory.md PAR-081..085, D7, D11
-- D11: the options the legacy window posts by default against the ones
-- Drupal sends. On the E2E organization both give the reviewed E2E cells.
-- On the #92 world (room usage in another plan) they give different rooms (case 125).
-- D7: a second run of the same scope with the other options keeps the first
-- run's placements and adds nothing (continuation, not regeneration).
-- The E2E fixture's owner (the installer's seed user, not loaded by the suite).
INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena) VALUES ('admin-superuser', 'admin', '-', 'admin@example.org', 'admin', 'not-a-real-hash');
\ir ../../../e2e/fixture.sql
\ir ../../../e2e/fixture-http-steps.sql
SELECT generar('E', 'N', 'PER', 1, false, 1, false, true);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check(md5((SELECT string_agg(a.plantilla || '/' || a.grupo || '/' || p.nombre || '/' || a.lugar || '/' || a.dia || '/' || a.turno, ',' ORDER BY a.plantilla, a.grupo)
                     FROM actividad a JOIN profesor p ON p.id = a.profesor)) = '4199818b55d4f1c44f2cba24f0367e29',
                 'legacy window options: the E2E digest');
CREATE TEMP TABLE first_run AS SELECT id, plantilla, grupo, profesor, lugar, dia, turno FROM actividad;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check((SELECT count(*) FROM actividad) = 8 AND NOT EXISTS (SELECT * FROM first_run EXCEPT SELECT id, plantilla, grupo, profesor, lugar, dia, turno FROM actividad),
                 'D7: a second run with Drupal''s options keeps every placement and adds nothing');
-- NEGATIVE: what deleting the placements first and running with the other
-- options gives on the E2E organization: the same cells (the options do not
-- matter here).
DELETE FROM actividad;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check(NOT EXISTS (SELECT plantilla, grupo, dia, turno FROM first_run EXCEPT SELECT plantilla, grupo, dia, turno FROM actividad),
                 'on the E2E organization, Drupal options give the same cells');
