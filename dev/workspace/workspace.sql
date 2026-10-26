-- Job-scoped SQL generation workspace inside a tenant database (issue #93).
-- Prototype, test code only: docs/modernization/single-db-workspace.md.
--
-- Loaded by dev/workspace/install.sh as a superuser, after the
-- unchanged legacy bundle (dev/sql/compilasql.php) was loaded with
-- search_path = scheduler. The legacy functions, types, views, RULEs and
-- triggers then live in schema "scheduler" (plus the fixed legacy schemas
-- matrix, prioriza, tiempo, seguridad, generador, portal). The legacy tables
-- of "scheduler" stay EMPTY: they are the shape templates of the workspaces
-- and no job role has any privilege on them, so a name that did not resolve
-- to the job's workspace would fail with 42501 instead of reading shared data.
--
-- A workspace is a schema scheduler_job_<n> owned by one job role. Its tables,
-- inheritance, foreign keys, views, RULEs and triggers are generated from the
-- catalog of "scheduler" (pg_get_*def), so the views, RULE actions and FKs are
-- bound (by OID) to the workspace tables, and every function they call is the
-- unchanged legacy function of "scheduler". No legacy function is copied.

-- ---------------------------------------------------------------- roles
-- agoh_engine: the group of every job role (USAGE on the engine code only).
DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'agoh_engine') THEN
    CREATE ROLE agoh_engine NOLOGIN;
  END IF;
END $$;

-- Job roles connect and use session TEMP tables (the closure keeps its row
-- set in one); pg_temp is always LAST in their search_path.
DO $$ BEGIN
  EXECUTE format('GRANT CONNECT, TEMPORARY ON DATABASE %I TO agoh_engine', current_database());
END $$;

-- ---------------------------------------------------------------- control schema
CREATE SCHEMA scheduler_control;
REVOKE ALL ON SCHEMA scheduler_control FROM PUBLIC;
GRANT USAGE ON SCHEMA scheduler_control TO agoh_engine;

-- Per-job cancellation requests. generador.cancelar (schema-qualified in
-- generar() and generador.fue_cancelado) becomes an updatable view that shows
-- each job role only its own rows: two jobs of the same scope never consume
-- each other's request. The function generador.fue_cancelado is unchanged.
CREATE TABLE scheduler_control.cancel_request (
  job_role name NOT NULL DEFAULT current_user,
  especialidad varchar NOT NULL,
  nivel varchar NOT NULL,
  periodo varchar NOT NULL,
  requested_at timestamptz NOT NULL DEFAULT clock_timestamp(),
  PRIMARY KEY (job_role, especialidad, nivel, periodo)
);
DROP TABLE generador.cancelar;
CREATE VIEW generador.cancelar AS
  SELECT especialidad, nivel, periodo FROM scheduler_control.cancel_request WHERE job_role = current_user
  WITH LOCAL CHECK OPTION;
GRANT SELECT, INSERT, DELETE ON generador.cancelar TO agoh_engine;

-- Provisional output kept after a job (stand-in for the Drupal promotion of
-- #95): the placements a later job of the tenant receives as input.
CREATE TABLE scheduler_control.placement_store (
  seq bigserial PRIMARY KEY,
  job text NOT NULL,
  id varchar NOT NULL UNIQUE,
  plantilla varchar NOT NULL, grupo varchar NOT NULL, profesor varchar, lugar varchar,
  dia integer, turno integer, momento_de_colocacion double precision, creador varchar NOT NULL
);
CREATE TABLE scheduler_control.resource_store (
  seq bigserial PRIMARY KEY,
  job text NOT NULL,
  otro_recurso varchar NOT NULL, actividad varchar NOT NULL, creador varchar NOT NULL
);
GRANT SELECT ON scheduler_control.placement_store, scheduler_control.resource_store TO agoh_engine;

-- Job registry and lifecycle: created, loaded, ran, kept, dropped, failed.
CREATE TABLE scheduler_control.workspace (
  job text PRIMARY KEY,
  schema_name name NOT NULL UNIQUE,
  job_role name NOT NULL UNIQUE,
  state text NOT NULL DEFAULT 'created',
  created_at timestamptz NOT NULL DEFAULT clock_timestamp(),
  updated_at timestamptz NOT NULL DEFAULT clock_timestamp(),
  keep_until timestamptz
);

-- ---------------------------------------------------------------- engine code
-- Code schemas: USAGE for the job roles; the template tables: nothing.
REVOKE ALL ON SCHEMA scheduler FROM PUBLIC;
GRANT USAGE ON SCHEMA scheduler, matrix, prioriza, tiempo, generador, seguridad TO agoh_engine;
REVOKE ALL ON ALL TABLES IN SCHEMA scheduler FROM PUBLIC;
-- The engine users (creador) are tenant-level: "u<uid>" of every Drupal user
-- (LegacyTableSync::ensureCreator writes them, schema-qualified). Every
-- workspace references this one table; a job can add a user, never change
-- or delete one.
GRANT SELECT, INSERT, REFERENCES ON seguridad.usuario TO agoh_engine;

-- The legacy tables of the template schema, in creation order.
CREATE VIEW scheduler_control.template_tables AS
  SELECT c.oid, c.relname FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
  WHERE n.nspname = 'scheduler' AND c.relkind = 'r';

-- ---------------------------------------------------------------- workspace DDL
-- Creates schema p_schema owned by p_owner with the legacy shapes.
CREATE FUNCTION scheduler_control.create_workspace(p_job text, p_schema name, p_owner name) RETURNS integer
LANGUAGE plpgsql AS $$
DECLARE
  r record; n integer := 0; ddl text[] := '{}'; d text;
  ws_path text := format('%I, scheduler, pg_temp', p_schema);
BEGIN
  EXECUTE format('CREATE SCHEMA %I AUTHORIZATION %I', p_schema, p_owner);
  EXECUTE format('REVOKE ALL ON SCHEMA %I FROM PUBLIC', p_schema);
  -- 1. tables: columns, defaults, NOT NULL, CHECK, primary and unique keys
  FOR r IN SELECT relname FROM scheduler_control.template_tables ORDER BY oid LOOP
    EXECUTE format('CREATE TABLE %I.%I (LIKE scheduler.%I INCLUDING DEFAULTS INCLUDING CONSTRAINTS INCLUDING INDEXES)',
                   p_schema, r.relname, r.relname);
    n := n + 1;
  END LOOP;
  -- 2. inheritance (restriccion_real, restriccion_por_actividad)
  FOR r IN SELECT c.relname AS child, p.relname AS parent FROM pg_inherits i
             JOIN pg_class c ON c.oid = i.inhrelid JOIN pg_class p ON p.oid = i.inhparent
             JOIN pg_namespace cn ON cn.oid = c.relnamespace WHERE cn.nspname = 'scheduler' ORDER BY c.oid LOOP
    EXECUTE format('ALTER TABLE %I.%I INHERIT %I.%I', p_schema, r.child, p_schema, r.parent);
  END LOOP;
  -- 3. definitions of FKs, views, RULEs and triggers, printed relative to the
  --    template schema (unqualified names), then executed relative to the
  --    workspace: the same text binds to the workspace tables.
  PERFORM set_config('search_path', 'scheduler, pg_temp', true);
  FOR r IN SELECT c.conrelid::regclass::text AS tbl, c.conname, pg_get_constraintdef(c.oid, true) AS def
             FROM pg_constraint c JOIN scheduler_control.template_tables t ON t.oid = c.conrelid
            WHERE c.contype = 'f' ORDER BY c.oid LOOP
    ddl := ddl || format('ALTER TABLE %s ADD CONSTRAINT %I %s', r.tbl, r.conname, r.def);
  END LOOP;
  FOR r IN SELECT c.relname, pg_get_viewdef(c.oid, true) AS def FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
            WHERE n.nspname = 'scheduler' AND c.relkind = 'v' ORDER BY c.oid LOOP
    ddl := ddl || format('CREATE VIEW %I AS %s', r.relname, r.def);
  END LOOP;
  FOR r IN SELECT pg_get_ruledef(w.oid, true) AS def FROM pg_rewrite w JOIN scheduler_control.template_tables t ON t.oid = w.ev_class
            WHERE w.rulename <> '_RETURN' ORDER BY w.oid LOOP
    ddl := ddl || r.def;
  END LOOP;
  FOR r IN SELECT pg_get_triggerdef(g.oid, true) AS def FROM pg_trigger g JOIN scheduler_control.template_tables t ON t.oid = g.tgrelid
            WHERE NOT g.tgisinternal ORDER BY g.oid LOOP
    ddl := ddl || r.def;
  END LOOP;
  PERFORM set_config('search_path', ws_path, true);
  FOREACH d IN ARRAY ddl LOOP
    EXECUTE d;
  END LOOP;
  -- 4. ownership: the job role owns every table and view of its workspace
  FOR r IN SELECT c.relname FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
            WHERE n.nspname = p_schema AND c.relkind IN ('r', 'v') LOOP
    EXECUTE format('ALTER TABLE %I.%I OWNER TO %I', p_schema, r.relname, p_owner);
  END LOOP;
  INSERT INTO scheduler_control.workspace (job, schema_name, job_role) VALUES (p_job, p_schema, p_owner);
  RETURN n;
END $$;
REVOKE ALL ON FUNCTION scheduler_control.create_workspace(text, name, name) FROM PUBLIC;

-- Drops a workspace and its job role's cancellation rows (lifecycle "dropped").
CREATE FUNCTION scheduler_control.drop_workspace(p_job text) RETURNS void LANGUAGE plpgsql AS $$
DECLARE w record;
BEGIN
  SELECT * INTO w FROM scheduler_control.workspace WHERE job = p_job FOR UPDATE;
  IF NOT FOUND THEN RETURN; END IF;
  EXECUTE format('DROP SCHEMA IF EXISTS %I CASCADE', w.schema_name);
  DELETE FROM scheduler_control.cancel_request WHERE job_role = w.job_role;
  UPDATE scheduler_control.workspace SET state = 'dropped', updated_at = clock_timestamp() WHERE job = p_job;
END $$;
REVOKE ALL ON FUNCTION scheduler_control.drop_workspace(text) FROM PUBLIC;

-- Cleanup and recovery: every workspace whose job role has no backend and
-- whose state is not final is marked failed; failed, ran and expired kept
-- workspaces are dropped. Returns the jobs it dropped.
CREATE FUNCTION scheduler_control.cleanup(p_now timestamptz DEFAULT clock_timestamp()) RETURNS SETOF text LANGUAGE plpgsql AS $$
DECLARE w record;
BEGIN
  UPDATE scheduler_control.workspace SET state = 'failed', updated_at = clock_timestamp()
   WHERE state IN ('created', 'loaded', 'running')
     AND NOT EXISTS (SELECT 1 FROM pg_stat_activity a WHERE a.usename = job_role);
  FOR w IN SELECT job FROM scheduler_control.workspace
            WHERE state = 'failed' OR (state = 'kept' AND keep_until IS NOT NULL AND keep_until <= p_now) LOOP
    PERFORM scheduler_control.drop_workspace(w.job);
    RETURN NEXT w.job;
  END LOOP;
END $$;
REVOKE ALL ON FUNCTION scheduler_control.cleanup(timestamptz) FROM PUBLIC;

-- ---------------------------------------------------------------- closure (#92)
-- Run by the job role with search_path = <workspace>, scheduler, pg_temp.
-- The same rules as dev/sql/tests/fixtures/closure.sql (tst.cl_keep), applied
-- to the tables of the first schema of the search_path. seguridad.usuario is
-- tenant-level and never pruned.
CREATE FUNCTION scheduler_control.ws_schema() RETURNS name LANGUAGE sql STABLE AS $$ SELECT (current_schemas(false))[1] $$;

CREATE FUNCTION scheduler_control.ws_tables() RETURNS SETOF regclass LANGUAGE sql STABLE AS $$
  SELECT c.oid::regclass FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
  WHERE n.nspname = scheduler_control.ws_schema() AND c.relkind = 'r'
    AND c.relname IN (SELECT relname FROM scheduler_control.template_tables)
$$;

CREATE FUNCTION scheduler_control.pk_cols(t regclass, alias text DEFAULT NULL) RETURNS text LANGUAGE sql STABLE AS $$
  SELECT coalesce(
    (SELECT string_agg(coalesce(alias || '.', '') || quote_ident(a.attname), ', ' ORDER BY array_position(i.indkey::int2[], a.attnum))
       FROM pg_index i JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY (i.indkey)
      WHERE i.indrelid = t AND i.indisprimary),
    (SELECT string_agg(coalesce(alias || '.', '') || quote_ident(a.attname), ', ' ORDER BY a.attnum)
       FROM pg_attribute a WHERE a.attrelid = t AND a.attnum > 0 AND NOT a.attisdropped))
$$;

CREATE FUNCTION scheduler_control.closure_keep(pesp varchar, pniv varchar, pper varchar, pbuscalugar boolean) RETURNS bigint
LANGUAGE plpgsql AS $$
DECLARE
  q text; added bigint; total bigint; fk record;
  subj text := format('SELECT id FROM asignatura WHERE especialidad = %L AND nivel = %L', pesp, pniv);
  brk text; tpl text; cls text; grp text; brg text; brg_scope text; tch text; rms text; res text; ppa text; ogr text;
BEGIN
  CREATE TEMP TABLE IF NOT EXISTS ws_keep (tbl regclass NOT NULL, k jsonb NOT NULL, PRIMARY KEY (tbl, k));
  DELETE FROM ws_keep;
  brk := format('SELECT id FROM desglose WHERE periodo = %L AND asignatura IN (%s)', pper, subj);
  tpl := format('SELECT id FROM plantilla_de_actividad WHERE desglose IN (%s)', brk);
  cls := format('SELECT clasificacion FROM plantilla_de_actividad WHERE id IN (%s)', tpl);
  grp := format('SELECT bg.grupo FROM brigada_de_grupo bg JOIN brigada b ON b.id = bg.brigada JOIN grupo g ON g.id = bg.grupo
                  WHERE b.especialidad = %L AND b.nivel = %L AND g.clasificacion IN (%s)', pesp, pniv, cls);
  brg_scope := format('SELECT id FROM brigada WHERE especialidad = %L AND nivel = %L', pesp, pniv);
  brg := format('SELECT brigada FROM brigada_de_grupo WHERE grupo IN (%s) UNION %s', grp, brg_scope);
  ppa := format('SELECT id FROM profesor_para_actividad WHERE plantilla IN (%s)', tpl);
  tch := format('SELECT profesor FROM profesor_para_actividad WHERE plantilla IN (%s)', tpl);
  rms := format('SELECT lugar FROM lugar_para_actividad WHERE plantilla IN (%s)', tpl);
  ogr := format('SELECT id FROM otro_grupo_de_recursos_para_actividad WHERE plantilla IN (%s)', tpl);
  res := format('SELECT otro_recurso FROM otro_recurso_para_actividad WHERE grupo_de_recursos IN (%s)', ogr);
  PERFORM scheduler_control.cl_add('periodo', format('id = %L', pper));
  PERFORM scheduler_control.cl_add('periodicidad', format('periodo = %L', pper));
  PERFORM scheduler_control.cl_add('receso', format('periodo = %L', pper));
  PERFORM scheduler_control.cl_add('asignatura', format('id IN (%s)', subj));
  PERFORM scheduler_control.cl_add('desglose', format('id IN (%s)', brk));
  PERFORM scheduler_control.cl_add('plantilla_de_actividad', format('id IN (%s)', tpl));
  PERFORM scheduler_control.cl_add('clasificacion_de_actividad', format('id IN (%s)', cls));
  PERFORM scheduler_control.cl_add('zpriori', format('periodo = %L AND clasificacion IN (%s)', pper, cls));
  PERFORM scheduler_control.cl_add('grupo', format('id IN (%s)', grp));
  PERFORM scheduler_control.cl_add('brigada_de_grupo', format('grupo IN (%s)', grp));
  PERFORM scheduler_control.cl_add('brigada', format('id IN (%s)', brg));
  PERFORM scheduler_control.cl_add('profesor_para_actividad', format('id IN (%s)', ppa));
  PERFORM scheduler_control.cl_add('profesor_para_grupo', format('profesor_para_actividad IN (%s)', ppa));
  PERFORM scheduler_control.cl_add('lugar_para_actividad', format('plantilla IN (%s)', tpl));
  PERFORM scheduler_control.cl_add('otro_grupo_de_recursos_para_actividad', format('id IN (%s)', ogr));
  PERFORM scheduler_control.cl_add('otro_recurso_para_actividad', format('grupo_de_recursos IN (%s)', ogr));
  PERFORM scheduler_control.cl_add('acceso_prohibido', format('grupo IN (%s)', grp));
  PERFORM scheduler_control.cl_add('lugar_fijo', format('grupo IN (%s)', grp));
  IF pbuscalugar THEN
    PERFORM scheduler_control.cl_add('costo_de_traslado', format('origen IN (%s)', rms));
  END IF;
  PERFORM scheduler_control.cl_add('higiene_para_profesor', format('periodo = %L AND profesor IN (%s)', pper, tch));
  PERFORM scheduler_control.cl_add('higiene_para_brigada', format('periodo = %L AND brigada IN (%s)', pper, brg));
  PERFORM scheduler_control.cl_add('restriccion_de_periodo', format('periodo = %L', pper));
  PERFORM scheduler_control.cl_add('restriccion_de_clasif_act', format('periodo = %L AND clasificacion IN (%s)', pper, cls));
  PERFORM scheduler_control.cl_add('restriccion_de_asignatura', format('periodo = %L AND asignatura IN (%s)', pper, subj));
  PERFORM scheduler_control.cl_add('restriccion_de_brigada', format('periodo = %L AND brigada IN (%s)', pper, brg));
  PERFORM scheduler_control.cl_add('restriccion_de_profesor', format('periodo = %L AND profesor IN (%s)', pper, tch));
  PERFORM scheduler_control.cl_add('restriccion_de_lugar', format('periodo = %L AND lugar IN (%s)', pper, rms));
  PERFORM scheduler_control.cl_add('restriccion_de_otro_recurso', format('periodo = %L AND otro_recurso IN (%s)', pper, res));
  PERFORM scheduler_control.cl_add('restriccion_de_brigada', format('brigada IN (%s)', brg_scope));
  PERFORM scheduler_control.cl_add('actividad', format('plantilla IN (%s)', tpl));
  PERFORM scheduler_control.cl_add('actividad', format('profesor IN (%s) AND dame_periodo_plantilla_de_actividad(plantilla) = %L', tch, pper));
  PERFORM scheduler_control.cl_add('actividad', format('dame_periodo_plantilla_de_actividad(plantilla) = %L AND grupo IN (SELECT grupo FROM brigada_de_grupo WHERE brigada IN (%s))', pper, brg));
  IF NOT pbuscalugar THEN
    PERFORM scheduler_control.cl_add('actividad', format('lugar IN (%s)', rms));
  END IF;
  PERFORM scheduler_control.cl_add('otros_recursos_usados', format('otro_recurso IN (%s)', res));
  PERFORM scheduler_control.cl_add('restriccion_condicional', format('activa AND consec_periodo = %L', pper));
  PERFORM scheduler_control.cl_add('herencia_de_restricciones', format(
    'hijo_profesor IN (%1$s) OR padre_profesor IN (%1$s) OR hijo_lugar IN (%2$s) OR padre_lugar IN (%2$s)
     OR hijo_brigada IN (%3$s) OR padre_brigada IN (%3$s) OR hijo_otro_recurso IN (%4$s) OR padre_otro_recurso IN (%4$s)', tch, rms, brg, res));
  PERFORM scheduler_control.cl_add('excepcion_de_hrt', format('periodo = %L', pper));
  LOOP
    added := 0;
    FOR fk IN
      SELECT c.conrelid::regclass AS child, c.confrelid::regclass AS parent,
             (SELECT string_agg(format('c.%I = p.%I', ca.attname, pa.attname), ' AND ')
                FROM unnest(c.conkey, c.confkey) AS u(ck, pk)
                JOIN pg_attribute ca ON ca.attrelid = c.conrelid AND ca.attnum = u.ck
                JOIN pg_attribute pa ON pa.attrelid = c.confrelid AND pa.attnum = u.pk) AS on_clause
      FROM pg_constraint c WHERE c.contype = 'f'
        AND c.conrelid IN (SELECT scheduler_control.ws_tables()) AND c.confrelid IN (SELECT scheduler_control.ws_tables())
    LOOP
      q := format('INSERT INTO ws_keep SELECT DISTINCT %L::regclass, jsonb_build_array(%s) FROM ONLY %s p JOIN ONLY %s c ON %s
                   WHERE EXISTS (SELECT 1 FROM ws_keep k WHERE k.tbl = %L::regclass AND k.k = jsonb_build_array(%s)) ON CONFLICT DO NOTHING',
                  fk.parent, scheduler_control.pk_cols(fk.parent, 'p'), fk.parent, fk.child, fk.on_clause, fk.child, scheduler_control.pk_cols(fk.child, 'c'));
      EXECUTE q;
      GET DIAGNOSTICS total = ROW_COUNT;
      added := added + total;
    END LOOP;
    EXIT WHEN added = 0;
  END LOOP;
  SELECT count(*) INTO total FROM ws_keep;
  RETURN total;
END $$;

CREATE FUNCTION scheduler_control.cl_add(t regclass, cond text) RETURNS void LANGUAGE plpgsql AS $$
BEGIN
  EXECUTE format('INSERT INTO ws_keep SELECT %L::regclass, jsonb_build_array(%s) FROM ONLY %s WHERE %s ON CONFLICT DO NOTHING',
                 t, scheduler_control.pk_cols(t), t, cond);
END $$;

-- Deletes every workspace row outside the closure. The job role owns the
-- tables, so it disables their user triggers (order and priority renumbering
-- would otherwise change kept rows) and re-enables them; FK actions still run.
CREATE FUNCTION scheduler_control.prune() RETURNS bigint LANGUAGE plpgsql AS $$
DECLARE t regclass; n bigint; total bigint := 0;
BEGIN
  FOR t IN SELECT scheduler_control.ws_tables() LOOP
    EXECUTE format('ALTER TABLE %s DISABLE TRIGGER USER', t);
  END LOOP;
  FOR t IN SELECT scheduler_control.ws_tables() LOOP
    EXECUTE format('DELETE FROM ONLY %s x WHERE NOT EXISTS (SELECT 1 FROM ws_keep k WHERE k.tbl = %L::regclass AND k.k = jsonb_build_array(%s))',
                   t, t, scheduler_control.pk_cols(t, 'x'));
    GET DIAGNOSTICS n = ROW_COUNT;
    total := total + n;
  END LOOP;
  FOR t IN SELECT scheduler_control.ws_tables() LOOP
    EXECUTE format('ALTER TABLE %s ENABLE TRIGGER USER', t);
  END LOOP;
  RETURN total;
END $$;

CREATE FUNCTION scheduler_control.ws_rows() RETURNS bigint LANGUAGE plpgsql AS $$
DECLARE t regclass; n bigint; total bigint := 0;
BEGIN
  FOR t IN SELECT scheduler_control.ws_tables() LOOP
    EXECUTE format('SELECT count(*) FROM ONLY %s', t) INTO n;
    total := total + n;
  END LOOP;
  RETURN total;
END $$;

CREATE FUNCTION scheduler_control.ws_analyze() RETURNS void LANGUAGE plpgsql AS $$
DECLARE t regclass;
BEGIN
  FOR t IN SELECT scheduler_control.ws_tables() LOOP
    EXECUTE format('ANALYZE %s', t);
  END LOOP;
END $$;

-- Placements carried forward from earlier jobs of the tenant, in their order.
-- Inserting them fires the legacy RULEs, which rebuild their occupancy rows,
-- as a sync of accepted placements would.
CREATE FUNCTION scheduler_control.load_stored_placements() RETURNS bigint LANGUAGE plpgsql AS $$
DECLARE n bigint; m bigint;
BEGIN
  INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, momento_de_colocacion, creador)
    SELECT id, plantilla, grupo, profesor, lugar, dia, turno, momento_de_colocacion, creador
      FROM scheduler_control.placement_store ORDER BY seq;
  GET DIAGNOSTICS n = ROW_COUNT;
  INSERT INTO otros_recursos_usados (otro_recurso, actividad, creador)
    SELECT otro_recurso, actividad, creador FROM scheduler_control.resource_store ORDER BY seq;
  GET DIAGNOSTICS m = ROW_COUNT;
  RETURN n + m;
END $$;

GRANT EXECUTE ON FUNCTION scheduler_control.closure_keep(varchar, varchar, varchar, boolean), scheduler_control.cl_add(regclass, text),
  scheduler_control.prune(), scheduler_control.ws_rows(), scheduler_control.ws_analyze(), scheduler_control.load_stored_placements(),
  scheduler_control.ws_schema(), scheduler_control.ws_tables(), scheduler_control.pk_cols(regclass, text) TO agoh_engine;
GRANT SELECT ON scheduler_control.template_tables TO agoh_engine;

-- Appends the provisional output of a finished job (rows of its workspace
-- that were not input) to the store. Run by the orchestrator.
CREATE FUNCTION scheduler_control.store_output(p_job text) RETURNS bigint LANGUAGE plpgsql AS $$
DECLARE w record; n bigint;
BEGIN
  SELECT * INTO w FROM scheduler_control.workspace WHERE job = p_job;
  EXECUTE format('INSERT INTO scheduler_control.placement_store (job, id, plantilla, grupo, profesor, lugar, dia, turno, momento_de_colocacion, creador)
                  SELECT %L, a.id, a.plantilla, a.grupo, a.profesor, a.lugar, a.dia, a.turno, a.momento_de_colocacion, a.creador
                    FROM %I.actividad a WHERE NOT EXISTS (SELECT 1 FROM scheduler_control.placement_store s WHERE s.id = a.id)
                   ORDER BY a.ctid', p_job, w.schema_name);
  GET DIAGNOSTICS n = ROW_COUNT;
  EXECUTE format('INSERT INTO scheduler_control.resource_store (job, otro_recurso, actividad, creador)
                  SELECT %L, u.otro_recurso, u.actividad, u.creador FROM %I.otros_recursos_usados u
                   WHERE NOT EXISTS (SELECT 1 FROM scheduler_control.resource_store s WHERE s.otro_recurso = u.otro_recurso AND s.actividad = u.actividad)
                   ORDER BY u.ctid', p_job, w.schema_name);
  RETURN n;
END $$;
REVOKE ALL ON FUNCTION scheduler_control.store_output(text) FROM PUBLIC;
