-- Dependency-closure helpers for generar() (issue #92). Test code only.
--
-- tst.cl_keep(esp, nivel, periodo, buscalugar) computes the rows that the
-- legacy generator can read for one scheduling request, as derived from the
-- SQL sources (docs/modernization/engine-dependency-closure.md, section 4),
-- plus every row they reference through a foreign key, and stores their
-- primary keys in tst.keep. tst.cl_prune() deletes every other row of every
-- table (without firing triggers, so no kept row is renumbered), then checks
-- that no foreign key is left dangling.
--
-- tst.cl_before() and tst.cl_outcome(...) measure one run: the placements of
-- the scope, the rows the run inserted (normalized: generated ids replaced by
-- natural keys), the cancellation rows it consumed, and the per-table DML
-- counters of the transaction (pg_stat_xact_user_tables). Two runs with the
-- same outcome text have the same placements and the same DML side effects.

CREATE TABLE tst.keep (tbl regclass NOT NULL, k jsonb NOT NULL, PRIMARY KEY (tbl, k));

-- Primary key columns of a table, as a SQL list "a, b" (every column for a
-- table without a primary key, such as seguridad.traza).
CREATE FUNCTION tst.pk_cols(t regclass, alias text DEFAULT NULL) RETURNS text LANGUAGE sql STABLE AS $$
  SELECT coalesce(
    (SELECT string_agg(coalesce(alias || '.', '') || quote_ident(a.attname), ', ' ORDER BY array_position(i.indkey::int2[], a.attnum))
       FROM pg_index i JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY (i.indkey)
      WHERE i.indrelid = t AND i.indisprimary),
    (SELECT string_agg(coalesce(alias || '.', '') || quote_ident(a.attname), ', ' ORDER BY a.attnum)
       FROM pg_attribute a WHERE a.attrelid = t AND a.attnum > 0 AND NOT a.attisdropped))
$$;

-- Every user table of the legacy schemas.
CREATE VIEW tst.legacy_tables AS
SELECT c.oid::regclass AS tbl
FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
WHERE c.relkind = 'r' AND n.nspname IN ('public', 'seguridad', 'generador');

-- Keep the rows of t that match the condition (a WHERE clause on t).
CREATE FUNCTION tst.cl_add(t regclass, cond text) RETURNS void LANGUAGE plpgsql AS $$
BEGIN
  EXECUTE format('INSERT INTO tst.keep SELECT %L::regclass, jsonb_build_array(%s) FROM ONLY %s WHERE %s ON CONFLICT DO NOTHING',
                 t, tst.pk_cols(t), t, cond);
END $$;

-- Is this row of t kept? (used inside the generated SQL)
CREATE FUNCTION tst.cl_kept_sql(t regclass, alias text) RETURNS text LANGUAGE sql STABLE AS $$
  SELECT format('EXISTS (SELECT 1 FROM tst.keep k WHERE k.tbl = %L::regclass AND k.k = jsonb_build_array(%s))', t, tst.pk_cols(t, alias))
$$;

-- The closure of one request. buscalugar selects the room choice branch
-- (generar.sql:1309), which decides whether room usage in other periods is read.
-- With preset = false the closure is added to the rows already kept (the union
-- of the closures of several requests run in sequence).
CREATE FUNCTION tst.cl_keep(pesp varchar, pniv varchar, pper varchar, pbuscalugar boolean, preset boolean DEFAULT true)
RETURNS bigint LANGUAGE plpgsql AS $$
DECLARE
  q text; added bigint; total bigint; fk record;
  subj text := format('SELECT id FROM asignatura WHERE especialidad = %L AND nivel = %L', pesp, pniv);
  brk  text; tpl text; cls text; grp text; brg text; brg_scope text; tch text; rms text; res text; ppa text; ogr text;
BEGIN
  IF preset THEN DELETE FROM tst.keep; END IF;
  brk := format('SELECT id FROM desglose WHERE periodo = %L AND asignatura IN (%s)', pper, subj);
  tpl := format('SELECT id FROM plantilla_de_actividad WHERE desglose IN (%s)', brk);
  cls := format('SELECT clasificacion FROM plantilla_de_actividad WHERE id IN (%s)', tpl);
  -- dame_grupos(esp, nivel, clasif): groups of ANY plan with a brigade of the scope (Q-02)
  grp := format('SELECT bg.grupo FROM brigada_de_grupo bg JOIN brigada b ON b.id = bg.brigada JOIN grupo g ON g.id = bg.grupo
                  WHERE b.especialidad = %L AND b.nivel = %L AND g.clasificacion IN (%s)', pesp, pniv, cls);
  brg_scope := format('SELECT id FROM brigada WHERE especialidad = %L AND nivel = %L', pesp, pniv);
  brg := format('SELECT brigada FROM brigada_de_grupo WHERE grupo IN (%s) UNION %s', grp, brg_scope);
  ppa := format('SELECT id FROM profesor_para_actividad WHERE plantilla IN (%s)', tpl);
  tch := format('SELECT profesor FROM profesor_para_actividad WHERE plantilla IN (%s)', tpl);
  rms := format('SELECT lugar FROM lugar_para_actividad WHERE plantilla IN (%s)', tpl);
  ogr := format('SELECT id FROM otro_grupo_de_recursos_para_actividad WHERE plantilla IN (%s)', tpl);
  res := format('SELECT otro_recurso FROM otro_recurso_para_actividad WHERE grupo_de_recursos IN (%s)', ogr);

  -- The period and its own configuration.
  PERFORM tst.cl_add('periodo', format('id = %L', pper));
  PERFORM tst.cl_add('periodicidad', format('periodo = %L', pper));
  PERFORM tst.cl_add('receso', format('periodo = %L', pper));
  -- Subjects, breakdowns, templates and classes of the scope.
  PERFORM tst.cl_add('asignatura', format('id IN (%s)', subj));
  PERFORM tst.cl_add('desglose', format('id IN (%s)', brk));
  PERFORM tst.cl_add('plantilla_de_actividad', format('id IN (%s)', tpl));
  PERFORM tst.cl_add('clasificacion_de_actividad', format('id IN (%s)', cls));
  PERFORM tst.cl_add('zpriori', format('periodo = %L AND clasificacion IN (%s)', pper, cls));
  -- Groups (every plan), all their brigades, and every brigade of the specialty and level.
  PERFORM tst.cl_add('grupo', format('id IN (%s)', grp));
  PERFORM tst.cl_add('brigada_de_grupo', format('grupo IN (%s)', grp));
  PERFORM tst.cl_add('brigada', format('id IN (%s)', brg));
  -- Resources assigned to the templates.
  PERFORM tst.cl_add('profesor_para_actividad', format('id IN (%s)', ppa));
  PERFORM tst.cl_add('profesor_para_grupo', format('profesor_para_actividad IN (%s)', ppa));
  PERFORM tst.cl_add('lugar_para_actividad', format('plantilla IN (%s)', tpl));
  PERFORM tst.cl_add('otro_grupo_de_recursos_para_actividad', format('id IN (%s)', ogr));
  PERFORM tst.cl_add('otro_recurso_para_actividad', format('grupo_de_recursos IN (%s)', ogr));
  PERFORM tst.cl_add('acceso_prohibido', format('grupo IN (%s)', grp));
  PERFORM tst.cl_add('lugar_fijo', format('grupo IN (%s)', grp));
  IF pbuscalugar THEN
    PERFORM tst.cl_add('costo_de_traslado', format('origen IN (%s)', rms));
  END IF;
  PERFORM tst.cl_add('higiene_para_profesor', format('periodo = %L AND profesor IN (%s)', pper, tch));
  PERFORM tst.cl_add('higiene_para_brigada', format('periodo = %L AND brigada IN (%s)', pper, brg));
  -- Nominal restrictions and occupancy of the period, for the scope's objects.
  PERFORM tst.cl_add('restriccion_de_periodo', format('periodo = %L', pper));
  PERFORM tst.cl_add('restriccion_de_clasif_act', format('periodo = %L AND clasificacion IN (%s)', pper, cls));
  PERFORM tst.cl_add('restriccion_de_asignatura', format('periodo = %L AND asignatura IN (%s)', pper, subj));
  PERFORM tst.cl_add('restriccion_de_brigada', format('periodo = %L AND brigada IN (%s)', pper, brg));
  PERFORM tst.cl_add('restriccion_de_profesor', format('periodo = %L AND profesor IN (%s)', pper, tch));
  PERFORM tst.cl_add('restriccion_de_lugar', format('periodo = %L AND lugar IN (%s)', pper, rms));
  PERFORM tst.cl_add('restriccion_de_otro_recurso', format('periodo = %L AND otro_recurso IN (%s)', pper, res));
  -- dia_minimo_impacto.sql:58: brigade restrictions of the specialty and level in EVERY period.
  PERFORM tst.cl_add('restriccion_de_brigada', format('brigada IN (%s)', brg_scope));
  -- Existing placements: of the scope's templates; of the teachers in the period
  -- (load, generar.sql:869; hygiene); of groups holding the scope's brigades in the
  -- period (brigade hygiene, rooms around); and, with buscalugar = FALSE, of the
  -- candidate rooms in every period (busca_lugar_mas/menos_utilizado_por).
  PERFORM tst.cl_add('actividad', format('plantilla IN (%s)', tpl));
  PERFORM tst.cl_add('actividad', format('profesor IN (%s) AND dame_periodo_plantilla_de_actividad(plantilla) = %L', tch, pper));
  PERFORM tst.cl_add('actividad', format('dame_periodo_plantilla_de_actividad(plantilla) = %L AND grupo IN (SELECT grupo FROM brigada_de_grupo WHERE brigada IN (%s))', pper, brg));
  IF NOT pbuscalugar THEN
    PERFORM tst.cl_add('actividad', format('lugar IN (%s)', rms));
  END IF;
  -- dame_uso_de_otro_recurso.sql:9: usage of the candidate resources in every period.
  PERFORM tst.cl_add('otros_recursos_usados', format('otro_recurso IN (%s)', res));
  -- Rows that make every evaluation of the period fail or change: conditional
  -- restrictions aimed at the period, inheritance rows of the scope's objects.
  PERFORM tst.cl_add('restriccion_condicional', format('activa AND consec_periodo = %L', pper));
  PERFORM tst.cl_add('herencia_de_restricciones', format(
    'hijo_profesor IN (%1$s) OR padre_profesor IN (%1$s) OR hijo_lugar IN (%2$s) OR padre_lugar IN (%2$s)
     OR hijo_brigada IN (%3$s) OR padre_brigada IN (%3$s) OR hijo_otro_recurso IN (%4$s) OR padre_otro_recurso IN (%4$s)', tch, rms, brg, res));
  PERFORM tst.cl_add('excepcion_de_hrt', format('periodo = %L', pper));
  PERFORM tst.cl_add('generador.cancelar', format('especialidad = %L AND nivel = %L AND periodo = %L', pesp, pniv, pper));

  -- Referential closure: the parents of every kept row, to a fixed point.
  LOOP
    added := 0;
    FOR fk IN
      SELECT c.conrelid::regclass AS child, c.confrelid::regclass AS parent,
             (SELECT string_agg(format('c.%I = p.%I', ca.attname, pa.attname), ' AND ')
                FROM unnest(c.conkey, c.confkey) AS u(ck, pk)
                JOIN pg_attribute ca ON ca.attrelid = c.conrelid AND ca.attnum = u.ck
                JOIN pg_attribute pa ON pa.attrelid = c.confrelid AND pa.attnum = u.pk) AS on_clause
      FROM pg_constraint c WHERE c.contype = 'f'
        AND c.conrelid IN (SELECT tbl FROM tst.legacy_tables)
    LOOP
      q := format('INSERT INTO tst.keep SELECT DISTINCT %L::regclass, jsonb_build_array(%s) FROM ONLY %s p JOIN ONLY %s c ON %s
                   WHERE %s ON CONFLICT DO NOTHING',
                  fk.parent, tst.pk_cols(fk.parent, 'p'), fk.parent, fk.child, fk.on_clause, tst.cl_kept_sql(fk.child, 'c'));
      EXECUTE q;
      GET DIAGNOSTICS total = ROW_COUNT;
      added := added + total;
    END LOOP;
    EXIT WHEN added = 0;
  END LOOP;
  SELECT count(*) INTO total FROM tst.keep;
  RETURN total;
END $$;

-- Delete every row that is not kept; no trigger fires (no renumbering of kept
-- rows). Returns the number of rows deleted. Fails on a dangling foreign key.
CREATE FUNCTION tst.cl_prune() RETURNS bigint LANGUAGE plpgsql AS $$
DECLARE t regclass; n bigint; total bigint := 0; fk record; orphans bigint;
BEGIN
  SET LOCAL session_replication_role = replica;
  FOR t IN SELECT tbl FROM tst.legacy_tables LOOP
    EXECUTE format('DELETE FROM ONLY %s x WHERE NOT %s', t, tst.cl_kept_sql(t, 'x'));
    GET DIAGNOSTICS n = ROW_COUNT;
    total := total + n;
  END LOOP;
  SET LOCAL session_replication_role = origin;
  FOR fk IN
    SELECT c.conname, c.conrelid::regclass AS child, c.confrelid::regclass AS parent,
           (SELECT string_agg(format('c.%I IS NOT NULL', ca.attname), ' AND ') FROM unnest(c.conkey) u(ck)
              JOIN pg_attribute ca ON ca.attrelid = c.conrelid AND ca.attnum = u.ck) AS notnull,
           (SELECT string_agg(format('c.%I = p.%I', ca.attname, pa.attname), ' AND ')
              FROM unnest(c.conkey, c.confkey) AS u(ck, pk)
              JOIN pg_attribute ca ON ca.attrelid = c.conrelid AND ca.attnum = u.ck
              JOIN pg_attribute pa ON pa.attrelid = c.confrelid AND pa.attnum = u.pk) AS on_clause
    FROM pg_constraint c WHERE c.contype = 'f' AND c.conrelid IN (SELECT tbl FROM tst.legacy_tables)
  LOOP
    EXECUTE format('SELECT count(*) FROM ONLY %s c WHERE %s AND NOT EXISTS (SELECT 1 FROM ONLY %s p WHERE %s)',
                   fk.child, fk.notnull, fk.parent, fk.on_clause) INTO orphans;
    IF orphans > 0 THEN RAISE EXCEPTION 'prune left % dangling rows for %', orphans, fk.conname; END IF;
  END LOOP;
  RETURN total;
END $$;

CREATE FUNCTION tst.row_count() RETURNS bigint LANGUAGE plpgsql AS $$
DECLARE t regclass; n bigint; total bigint := 0;
BEGIN
  FOR t IN SELECT tbl FROM tst.legacy_tables LOOP
    EXECUTE format('SELECT count(*) FROM ONLY %s', t) INTO n;
    total := total + n;
  END LOOP;
  RETURN total;
END $$;

-- Snapshot before a run: primary keys of every table and the DML counters.
CREATE FUNCTION tst.cl_before() RETURNS void LANGUAGE plpgsql AS $$
DECLARE t regclass;
BEGIN
  DROP TABLE IF EXISTS pg_temp.cl_rows, pg_temp.cl_stat, pg_temp.cl_reads;
  CREATE TEMP TABLE cl_rows (tbl regclass, k jsonb);
  FOR t IN SELECT tbl FROM tst.legacy_tables LOOP
    EXECUTE format('INSERT INTO cl_rows SELECT %L::regclass, jsonb_build_array(%s) FROM ONLY %s', t, tst.pk_cols(t), t);
  END LOOP;
  CREATE TEMP TABLE cl_stat AS
    SELECT relid::regclass AS tbl, n_tup_ins, n_tup_upd, n_tup_del, coalesce(seq_scan, 0) + coalesce(idx_scan, 0) AS scans
    FROM pg_stat_xact_user_tables WHERE schemaname IN ('public', 'seguridad', 'generador');
END $$;

-- Records which tables were scanned since tst.cl_before() (sequential or index
-- scan, foreign key checks included).
CREATE FUNCTION tst.cl_mark_reads() RETURNS void LANGUAGE plpgsql AS $$
BEGIN
  CREATE TEMP TABLE cl_reads AS
  SELECT s.relid::regclass AS tbl FROM pg_stat_xact_user_tables s LEFT JOIN cl_stat b ON b.tbl = s.relid
  WHERE s.schemaname IN ('public', 'seguridad', 'generador')
    AND coalesce(s.seq_scan, 0) + coalesce(s.idx_scan, 0) > coalesce(b.scans, 0);
END $$;

-- Natural key of an activity (unique (plantilla, grupo)).
CREATE FUNCTION tst.act_key(pid varchar) RETURNS text LANGUAGE sql STABLE AS $$
  SELECT plantilla || '/' || grupo FROM actividad WHERE id = pid
$$;

-- Outcome of the run since tst.cl_before(): placements of the scope, the rows
-- the run inserted (normalized), consumed cancellation rows and DML counters.
CREATE FUNCTION tst.cl_outcome(pesp varchar, pniv varchar, pper varchar) RETURNS text LANGUAGE plpgsql AS $$
DECLARE placements text; inserted text; consumed bigint; dml text; o text; q text; parts text[] := '{}'; part text;
BEGIN
  SELECT coalesce(string_agg(format('%s/%s/%s/%s@%s.%s', a.plantilla, a.grupo, a.profesor, a.lugar, a.dia, a.turno), ' '
                             ORDER BY a.plantilla, a.grupo), '')
    INTO placements
    FROM actividad a JOIN plantilla_de_actividad pa ON pa.id = a.plantilla JOIN desglose d ON d.id = pa.desglose
    JOIN asignatura s ON s.id = d.asignatura
   WHERE s.especialidad = pesp AND s.nivel = pniv AND d.periodo = pper;
  -- Inserted rows, generated ids replaced by natural keys.
  parts := parts || ARRAY[
    format($q$SELECT 'actividad:' || plantilla || '/' || grupo || '/' || coalesce(profesor, '-') || '/' || coalesce(lugar, '-') || '@' || dia || '.' || turno || ' ' || creador
              FROM actividad x WHERE NOT EXISTS (SELECT 1 FROM cl_rows r WHERE r.tbl = 'actividad'::regclass AND r.k = jsonb_build_array(x.id))$q$),
    format($q$SELECT 'otros_recursos_usados:' || otro_recurso || ' ' || tst.act_key(actividad) || ' ' || creador
              FROM otros_recursos_usados x WHERE NOT EXISTS (SELECT 1 FROM cl_rows r WHERE r.tbl = 'otros_recursos_usados'::regclass AND r.k = jsonb_build_array(x.otro_recurso, x.actividad))$q$)];
  FOREACH o IN ARRAY ARRAY['brigada', 'profesor', 'lugar', 'otro_recurso'] LOOP
    parts := parts || ARRAY[
      format($q$SELECT 'restriccion_de_%1$s:' || %1$s || ' ' || periodo || ' ' || dia || '.' || turno || ' v' || valor || ' ' || por_actividad || ' ' || coalesce(tst.act_key(actividad), '-')
                FROM restriccion_de_%1$s x WHERE NOT EXISTS (SELECT 1 FROM cl_rows r WHERE r.tbl = 'restriccion_de_%1$s'::regclass AND r.k = jsonb_build_array(x.id))$q$, o),
      format($q$SELECT 'restriccion_por_actividad_de_%1$s:' || n.%1$s || ' ' || n.dia || '.' || n.turno || ' ' || tst.act_key(x.actividad)
                FROM restriccion_por_actividad_de_%1$s x JOIN restriccion_de_%1$s n ON n.id = x.restriccion_nominal
                WHERE NOT EXISTS (SELECT 1 FROM cl_rows r WHERE r.tbl = 'restriccion_por_actividad_de_%1$s'::regclass AND r.k = jsonb_build_array(x.restriccion_nominal))$q$, o),
      format($q$SELECT 'restriccion_real_de_%1$s:' || n.%1$s || ' ' || n.dia || '.' || n.turno || ' ' || x.periodicidad || ' f' || coalesce(x.fecha::text, '-') || ' '
                       || coalesce(x.hora_inicial::text, '-') || '-' || coalesce(x.hora_final::text, '-') || ' ' || x.por_actividad || ' ' || coalesce(tst.act_key(x.actividad), '-') || ' ' || x.objeto
                FROM restriccion_real_de_%1$s x JOIN restriccion_de_%1$s n ON n.id = x.restriccion_nominal
                WHERE NOT EXISTS (SELECT 1 FROM cl_rows r WHERE r.tbl = 'restriccion_real_de_%1$s'::regclass AND r.k = jsonb_build_array(x.restriccion_nominal, x.periodicidad))$q$, o)];
  END LOOP;
  q := array_to_string(parts, ' UNION ALL ');
  EXECUTE format('SELECT md5(coalesce(string_agg(l, E''\n'' ORDER BY l), '''')) || '' rows='' || count(*) FROM (%s) s(l)', q) INTO inserted;
  SELECT count(*) INTO consumed FROM cl_rows r WHERE r.tbl = 'generador.cancelar'::regclass
     AND NOT EXISTS (SELECT 1 FROM generador.cancelar c WHERE jsonb_build_array(c.especialidad, c.nivel, c.periodo) = r.k);
  SELECT coalesce(string_agg(format('%s:+%s~%s-%s', s.relid::regclass, s.n_tup_ins - coalesce(b.n_tup_ins, 0),
                                    s.n_tup_upd - coalesce(b.n_tup_upd, 0), s.n_tup_del - coalesce(b.n_tup_del, 0)), ' '
                             ORDER BY s.relid::regclass::text), '')
    INTO dml
    FROM pg_stat_xact_user_tables s LEFT JOIN cl_stat b ON b.tbl = s.relid
   WHERE s.schemaname IN ('public', 'seguridad', 'generador')
     AND (s.n_tup_ins, s.n_tup_upd, s.n_tup_del) IS DISTINCT FROM (coalesce(b.n_tup_ins, 0), coalesce(b.n_tup_upd, 0), coalesce(b.n_tup_del, 0));
  RETURN format('placements=[%s] inserted=%s cancel_consumed=%s dml=[%s]', placements, inserted, consumed, dml);
END $$;

-- One run measured: tst.cl_before(), generar(...), tst.cl_outcome(...).
-- An error is caught (its effects are rolled back) and reported as
-- 'ERROR <sqlstate>'. The caller rolls back the successful run's effects.
CREATE FUNCTION tst.cl_run(pesp varchar, pniv varchar, pper varchar, forma bigint, parche boolean, compactar bigint,
                           parche1 boolean, buscalugar boolean) RETURNS text LANGUAGE plpgsql AS $$
BEGIN
  PERFORM tst.cl_before();
  BEGIN
    PERFORM generar(pesp, pniv, pper, forma, parche, compactar, parche1, buscalugar);
    PERFORM tst.cl_mark_reads();
  EXCEPTION WHEN OTHERS THEN
    RETURN 'ERROR ' || SQLSTATE;
  END;
  RETURN tst.cl_outcome(pesp, pniv, pper);
END $$;

-- The tables the last tst.cl_run() scanned.
CREATE FUNCTION tst.cl_scanned() RETURNS text LANGUAGE plpgsql STABLE AS $$
BEGIN
  RETURN (SELECT string_agg(tbl::text, ' ' ORDER BY tbl::text) FROM cl_reads);
END $$;
