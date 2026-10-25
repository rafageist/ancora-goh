-- expect: pass
-- timeout: 5s
-- refs: generador/generar.sql (one statement, one transaction); assessment §7
-- A generation interrupted by statement_timeout keeps nothing: the whole run
-- is one transaction. (Cancellation through generador.cancelar, case 062,
-- returns normally instead and keeps what was placed.)
-- Until #23 this case used the empty day range of case 064 to make generar()
-- run into the timeout. That range now terminates, so a test-only trigger
-- slows every placement down instead. A sequence, which a rollback does not
-- reset, proves that placement had started before the timeout.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
CREATE SEQUENCE tst.placements_started;
CREATE FUNCTION tst.slow_placement() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  PERFORM nextval('tst.placements_started');
  PERFORM pg_sleep(1);
  RETURN NULL;
END $$;
CREATE TRIGGER tst_slow_placement AFTER INSERT ON actividad FOR EACH ROW EXECUTE FUNCTION tst.slow_placement();
SET statement_timeout = '1500ms';
DO $$
BEGIN
  PERFORM generar('E', 'N', 'PER', 1, false, 0, false, false);
  RAISE EXCEPTION 'generar() was expected to hit statement_timeout';
EXCEPTION WHEN query_canceled THEN
  NULL;
END $$;
SET statement_timeout = 0;
SELECT tst.check((SELECT last_value FROM tst.placements_started) >= 1 AND (SELECT is_called FROM tst.placements_started), 'placement had started before the timeout');
SELECT tst.check((SELECT count(*) FROM actividad) = 0, 'no placement survived the timeout');
