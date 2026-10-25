-- expect: xfail
-- sqlstate: 57014
-- timeout: 8s
-- refs: actividad/dame_id_plantilla.sql:11; generador/and_mpi.sql; assessment 6.6
-- Intended: a template without "orden" is rejected or skipped. Nothing enforces
-- orden (the PHP UI always sets it); with orden NULL generar() never returns.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
UPDATE plantilla_de_actividad SET orden = NULL WHERE id = 'T2';
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
