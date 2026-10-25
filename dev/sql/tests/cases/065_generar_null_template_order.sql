-- expect: pass
-- timeout: 8s
-- refs: actividad/dame_id_plantilla.sql:11; generador/generar.sql (orden check); assessment 6.6; #23
-- Invariant: every activity template has an "orden" (the PHP UI always sets it;
-- templates are looked up by subject, period and orden). Until #23 a template
-- without one made generar() loop forever. It is now rejected before anything
-- is placed, with SQLSTATE 23502 (not_null_violation) naming the template.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
UPDATE plantilla_de_actividad SET orden = NULL WHERE id = 'T2';
DO $$
BEGIN
  PERFORM generar('E', 'N', 'PER', 1, false, 0, false, false);
  RAISE EXCEPTION 'generar() accepted a template without orden';
EXCEPTION WHEN not_null_violation THEN
  IF SQLERRM NOT LIKE '%template T2 has no orden%' THEN
    RAISE EXCEPTION 'unexpected message: %', SQLERRM;
  END IF;
END $$;
SELECT tst.check((SELECT count(*) FROM actividad) = 0, 'nothing placed');
-- with the order restored the same data generates normally
UPDATE plantilla_de_actividad SET orden = 2 WHERE id = 'T2';
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check((SELECT count(*) FROM actividad) = 4, 'all 4 demands placed once orden is set');
