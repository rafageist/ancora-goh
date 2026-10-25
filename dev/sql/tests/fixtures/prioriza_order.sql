-- Isolated diagnostic for the Prioriza ordering step inside generar() (issue #10).
-- The ordering loop is not a separate function, so this builds
-- tst.prioriza_order(table, n) from the *installed* generar() source text:
-- everything from "idxOrdenAct := '{}'" up to the "Determinando clasificacion
-- siguiente" notice (generador/generar.sql:456-496). It therefore always tests
-- the current source, never a copy. Fails loudly if the markers move.
DO $do$
DECLARE src text; blk text;
BEGIN
  SELECT prosrc INTO src FROM pg_proc WHERE proname = 'generar';
  blk := substring(src FROM $re$(idxOrdenAct := '\{\}'::INTEGER\[\];.*?)RAISE NOTICE '\[INFO\] Determinando clasificacion siguiente$re$);
  IF blk IS NULL THEN
    RAISE EXCEPTION 'ordering block markers not found in generar()';
  END IF;
  blk := replace(blk, 'RETURN;', 'RETURN NULL;');  -- the cancellation check returns from generar()
  EXECUTE format($f$
    CREATE FUNCTION tst.prioriza_order("TablaPrioriza" numeric[], "cantPriorizando" bigint) RETURNS bigint[]
    LANGUAGE plpgsql AS $body$
    DECLARE TablaPrioriza numeric[] := "TablaPrioriza";
            cantPriorizando bigint := "cantPriorizando";
            idxOrdenAct bigint[]; i bigint; j bigint; k integer;
            idesp varchar; idnivel varchar; idperiodo varchar;
    BEGIN
      %s
      RETURN idxOrdenAct;
    END $body$ $f$, blk);
END
$do$;

-- 9-column table whose column 9 holds the given horizontal totals.
CREATE FUNCTION tst.totals_table(totals numeric[]) RETURNS numeric[] LANGUAGE sql AS $$
  SELECT array_agg(ARRAY[0,0,0,0,0,0,0,0,t] ORDER BY n) FROM unnest(totals) WITH ORDINALITY u(t, n)
$$;
