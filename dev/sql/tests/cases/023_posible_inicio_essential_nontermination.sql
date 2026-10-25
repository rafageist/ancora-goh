-- expect: pass
-- timeout: 3s
-- refs: generador/posible_inicio.sql (k initialization); assessment 6.3; #23
-- posible_inicio terminates when the template requires neither a teacher nor a
-- place. Until #23, k stayed NULL in the essential-resource loop ("EXIT WHEN
-- k = duracion" never held) and the call never returned. k now starts at 0.
-- This is a termination fix only: the carry-over of k between the resource
-- checks (case 024, assessment 6.13) is unchanged.
\ir ../fixtures/base.sql
\ir ../fixtures/essential_resources.sql
UPDATE plantilla_de_actividad SET exige_profesor = false, exige_lugar = false WHERE id = 'T1';
SELECT tst.check(posible_inicio('T1', 1, 2, 'B1', NULL, NULL) IS NOT NULL, 'terminates');
