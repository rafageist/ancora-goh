-- expect: xfail
-- sqlstate: 57014
-- timeout: 3s
-- refs: generador/posible_inicio.sql:200-254 (k never initialized); assessment 6.3
-- Intended: posible_inicio terminates. With exige_profesor = exige_lugar = false,
-- k is NULL in the essential-resource loop ("EXIT WHEN k = duracion" never
-- holds), so the call never returns, even for a free slot. Bounded by
-- statement_timeout (57014).
\ir ../fixtures/base.sql
\ir ../fixtures/essential_resources.sql
UPDATE plantilla_de_actividad SET exige_profesor = false, exige_lugar = false WHERE id = 'T1';
SELECT tst.check(posible_inicio('T1', 1, 2, 'B1', NULL, NULL) IS NOT NULL, 'terminates');
