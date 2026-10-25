-- expect: pass
-- timeout: 5s
-- refs: generador/generar.sql (one statement, one transaction); assessment §7
-- A generation interrupted by statement_timeout keeps nothing: the whole run
-- is one transaction. (Cancellation through generador.cancelar, case 062,
-- returns normally instead and keeps what was placed.)
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad)
     SELECT 'RPX' || t, 'TA', 'PER', 1, t, 1, false FROM generate_series(1, 3) t;
DO $$
BEGIN
  PERFORM generar('E', 'N', 'PER', 1, false, 0, false, false);
  RAISE EXCEPTION 'generar() was expected to hit statement_timeout';
EXCEPTION WHEN query_canceled THEN
  NULL;
END $$;
SET statement_timeout = 0;
SELECT tst.check((SELECT count(*) FROM actividad) = 0, 'no placement survived the timeout');
