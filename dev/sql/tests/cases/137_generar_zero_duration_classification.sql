-- expect: xfail
-- sqlstate: 57014
-- timeout: 5s
-- refs: actividad/clasificacion_de_actividad.sql (duracion INTEGER default 1, no CHECK);
--       view/custom/form_clasif_act.tpl (free text); generador/get_max_colocation.sql;
--       legacy-parity-inventory.md PAR-019, D6
-- The classification duration has no CHECK and the form posts free text, so
-- a duration of 0 can be stored. generar() then never terminates: it loops in
-- get_max_colocation() until the statement timeout (57014). The case asserts
-- that the run completes, and is expected to fail. It is why the Drupal bound
-- "duration >= 1" is not removed with the other invented validations
-- before a decision (PAR-019).
\ir ../fixtures/base.sql
UPDATE clasificacion_de_actividad SET duracion = 0;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check(true, 'generar() completed with a zero-duration classification');
