-- Measurement of one generar() run (issue #93). Test code only.
--
-- Installed in schema "wsproof" of the legacy oracle databases and of the
-- tenant database. Every function resolves the legacy tables through the
-- caller's search_path, so the same functions measure the full legacy
-- database (search_path = public) and a job workspace
-- (search_path = scheduler_job_<n>, scheduler, pg_temp). The data schema is
-- the first schema of the search_path.
--
-- Natural keys replace every generated or derived id, so two databases with
-- different ids compare: a template is "<subject owner>/<subject>@<plan
-- owner>/<plan>/<period>#<order>", a group "<plan key>/<group>", a teacher,
-- room, cohort or resource "<owner>/<name>". Owners are printed as {U:<id>}
-- and mapped to scenario user keys by the caller.
CREATE SCHEMA IF NOT EXISTS wsproof;
GRANT USAGE ON SCHEMA wsproof TO PUBLIC;

CREATE OR REPLACE FUNCTION wsproof.data_schema() RETURNS name LANGUAGE sql STABLE AS $$ SELECT (current_schemas(false))[1] $$;
CREATE OR REPLACE FUNCTION wsproof.u(pid varchar) RETURNS text LANGUAGE sql IMMUTABLE AS $$ SELECT '{U:' || coalesce(pid, '-') || '}' $$;

CREATE OR REPLACE FUNCTION wsproof.k_plan(pid varchar) RETURNS text LANGUAGE plpgsql STABLE AS $$
BEGIN RETURN (SELECT wsproof.u(creador) || '/' || descripcion FROM planificacion WHERE id = pid); END $$;
CREATE OR REPLACE FUNCTION wsproof.k_period(pid varchar) RETURNS text LANGUAGE plpgsql STABLE AS $$
BEGIN RETURN (SELECT wsproof.k_plan(planificacion) || '/' || descripcion FROM periodo WHERE id = pid); END $$;
CREATE OR REPLACE FUNCTION wsproof.k_template(pid varchar) RETURNS text LANGUAGE plpgsql STABLE AS $$
BEGIN
  RETURN (SELECT wsproof.u(s.creador) || '/' || s.descripcion || '@' || wsproof.k_period(d.periodo) || '#' || coalesce(t.orden::text, '-')
            FROM plantilla_de_actividad t JOIN desglose d ON d.id = t.desglose JOIN asignatura s ON s.id = d.asignatura WHERE t.id = pid);
END $$;
CREATE OR REPLACE FUNCTION wsproof.k_group(pid varchar) RETURNS text LANGUAGE plpgsql STABLE AS $$
BEGIN RETURN (SELECT wsproof.k_plan(planificacion) || '/' || descripcion FROM grupo WHERE id = pid); END $$;
CREATE OR REPLACE FUNCTION wsproof.k_teacher(pid varchar) RETURNS text LANGUAGE plpgsql STABLE AS $$
BEGIN RETURN coalesce((SELECT wsproof.u(creador) || '/' || nombre FROM profesor WHERE id = pid), '(none)'); END $$;
CREATE OR REPLACE FUNCTION wsproof.k_room(pid varchar) RETURNS text LANGUAGE plpgsql STABLE AS $$
BEGIN RETURN coalesce((SELECT wsproof.u(creador) || '/' || nombre FROM lugar WHERE id = pid), '(none)'); END $$;
CREATE OR REPLACE FUNCTION wsproof.k_cohort(pid varchar) RETURNS text LANGUAGE plpgsql STABLE AS $$
BEGIN RETURN (SELECT wsproof.u(creador) || '/' || descripcion FROM brigada WHERE id = pid); END $$;
CREATE OR REPLACE FUNCTION wsproof.k_resource(pid varchar) RETURNS text LANGUAGE plpgsql STABLE AS $$
BEGIN RETURN (SELECT wsproof.u(creador) || '/' || nombre FROM otro_recurso WHERE id = pid); END $$;
CREATE OR REPLACE FUNCTION wsproof.k_act(pid varchar) RETURNS text LANGUAGE plpgsql STABLE AS $$
BEGIN RETURN coalesce((SELECT wsproof.k_template(plantilla) || '|' || wsproof.k_group(grupo) FROM actividad WHERE id = pid), '-'); END $$;
CREATE OR REPLACE FUNCTION wsproof.k_periodicity(pid varchar) RETURNS text LANGUAGE plpgsql STABLE AS $$
BEGIN RETURN (SELECT wsproof.k_period(periodo) || '+' || dia FROM periodicidad WHERE id = pid); END $$;
CREATE OR REPLACE FUNCTION wsproof.k_object(pkind text, pid varchar) RETURNS text LANGUAGE sql STABLE AS $$
  SELECT CASE pkind WHEN 'brigada' THEN wsproof.k_cohort(pid) WHEN 'profesor' THEN wsproof.k_teacher(pid)
                    WHEN 'lugar' THEN wsproof.k_room(pid) ELSE wsproof.k_resource(pid) END
$$;

-- Snapshot before a run: primary keys of the written tables, DML counters,
-- the visible cancellation rows.
CREATE OR REPLACE FUNCTION wsproof.before() RETURNS void LANGUAGE plpgsql AS $$
DECLARE o text;
BEGIN
  DROP TABLE IF EXISTS pg_temp.m_rows, pg_temp.m_stat;
  CREATE TEMP TABLE m_rows (tbl text, k text);
  INSERT INTO m_rows SELECT 'actividad', id FROM actividad;
  INSERT INTO m_rows SELECT 'otros_recursos_usados', otro_recurso || '|' || actividad FROM otros_recursos_usados;
  FOREACH o IN ARRAY ARRAY['brigada', 'profesor', 'lugar', 'otro_recurso'] LOOP
    EXECUTE format('INSERT INTO m_rows SELECT %L, id FROM restriccion_de_%s', 'restriccion_de_' || o, o);
    EXECUTE format('INSERT INTO m_rows SELECT %L, restriccion_nominal FROM restriccion_por_actividad_de_%s', 'restriccion_por_actividad_de_' || o, o);
    EXECUTE format('INSERT INTO m_rows SELECT %L, restriccion_nominal || ''|'' || periodicidad FROM restriccion_real_de_%s', 'restriccion_real_de_' || o, o);
  END LOOP;
  INSERT INTO m_rows SELECT 'cancel', especialidad || '|' || nivel || '|' || periodo FROM generador.cancelar;
  CREATE TEMP TABLE m_stat AS
    SELECT relname::text AS tbl, n_tup_ins, n_tup_upd, n_tup_del FROM pg_stat_xact_user_tables WHERE schemaname = wsproof.data_schema();
END $$;

-- Outcome of the run since wsproof.before(), as JSON: placements of the
-- scope, inserted rows, consumed cancellation rows, DML counters per table.
CREATE OR REPLACE FUNCTION wsproof.outcome(pesp varchar, pniv varchar, pper varchar) RETURNS jsonb LANGUAGE plpgsql AS $$
DECLARE o text; ins text[] := '{}'; part text[]; placements text[]; dml jsonb; consumed bigint;
BEGIN
  SELECT coalesce(array_agg(l ORDER BY l), '{}') INTO placements FROM (
    SELECT wsproof.k_template(a.plantilla) || '|' || wsproof.k_group(a.grupo) || '|' || wsproof.k_teacher(a.profesor) || '|'
           || wsproof.k_room(a.lugar) || '@' || a.dia || '.' || a.turno || '|' || wsproof.u(a.creador) AS l
      FROM actividad a JOIN plantilla_de_actividad pa ON pa.id = a.plantilla JOIN desglose d ON d.id = pa.desglose
      JOIN asignatura s ON s.id = d.asignatura
     WHERE s.especialidad = pesp AND s.nivel = pniv AND d.periodo = pper) q;
  SELECT coalesce(array_agg(l), '{}') INTO part FROM (
    SELECT 'actividad:' || wsproof.k_act(x.id) || '|' || wsproof.k_teacher(x.profesor) || '|' || wsproof.k_room(x.lugar)
           || '@' || x.dia || '.' || x.turno || '|' || wsproof.u(x.creador) AS l
      FROM actividad x WHERE NOT EXISTS (SELECT 1 FROM m_rows r WHERE r.tbl = 'actividad' AND r.k = x.id)
    UNION ALL
    SELECT 'otros_recursos_usados:' || wsproof.k_resource(x.otro_recurso) || '|' || wsproof.k_act(x.actividad) || '|' || wsproof.u(x.creador)
      FROM otros_recursos_usados x WHERE NOT EXISTS (SELECT 1 FROM m_rows r WHERE r.tbl = 'otros_recursos_usados' AND r.k = x.otro_recurso || '|' || x.actividad)) q;
  ins := ins || part;
  FOREACH o IN ARRAY ARRAY['brigada', 'profesor', 'lugar', 'otro_recurso'] LOOP
    EXECUTE format($q$SELECT coalesce(array_agg(l), '{}') FROM (
      SELECT 'restriccion_de_%1$s:' || wsproof.k_object(%1$L, x.%1$s) || '|' || wsproof.k_period(x.periodo) || '|' || x.dia || '.' || x.turno
             || '|v' || x.valor || '|' || x.por_actividad || '|' || coalesce(wsproof.k_act(x.actividad), '-') AS l
        FROM restriccion_de_%1$s x WHERE NOT EXISTS (SELECT 1 FROM m_rows r WHERE r.tbl = 'restriccion_de_%1$s' AND r.k = x.id)
      UNION ALL
      SELECT 'restriccion_por_actividad_de_%1$s:' || wsproof.k_object(%1$L, n.%1$s) || '|' || n.dia || '.' || n.turno || '|' || wsproof.k_act(x.actividad)
        FROM restriccion_por_actividad_de_%1$s x JOIN restriccion_de_%1$s n ON n.id = x.restriccion_nominal
       WHERE NOT EXISTS (SELECT 1 FROM m_rows r WHERE r.tbl = 'restriccion_por_actividad_de_%1$s' AND r.k = x.restriccion_nominal)
      UNION ALL
      SELECT 'restriccion_real_de_%1$s:' || wsproof.k_object(%1$L, n.%1$s) || '|' || n.dia || '.' || n.turno || '|' || wsproof.k_periodicity(x.periodicidad)
             || '|f' || coalesce(x.fecha::text, '-') || '|h' || coalesce(x.hora_inicial::text, '-') || '-' || coalesce(x.hora_final::text, '-')
             || '|' || x.por_actividad || '|' || coalesce(wsproof.k_act(x.actividad), '-') || '|' || wsproof.k_object(%1$L, x.objeto)
        FROM restriccion_real_de_%1$s x JOIN restriccion_de_%1$s n ON n.id = x.restriccion_nominal
       WHERE NOT EXISTS (SELECT 1 FROM m_rows r WHERE r.tbl = 'restriccion_real_de_%1$s' AND r.k = x.restriccion_nominal || '|' || x.periodicidad)) q$q$, o)
      INTO part;
    ins := ins || part;
  END LOOP;
  SELECT array_agg(l ORDER BY l) INTO ins FROM unnest(ins) l;
  SELECT count(*) INTO consumed FROM m_rows r WHERE r.tbl = 'cancel'
     AND NOT EXISTS (SELECT 1 FROM generador.cancelar c WHERE c.especialidad || '|' || c.nivel || '|' || c.periodo = r.k);
  SELECT coalesce(jsonb_object_agg(s.relname, jsonb_build_array(s.n_tup_ins - coalesce(b.n_tup_ins, 0), s.n_tup_upd - coalesce(b.n_tup_upd, 0),
                                                            s.n_tup_del - coalesce(b.n_tup_del, 0))), '{}')
    INTO dml
    FROM pg_stat_xact_user_tables s LEFT JOIN m_stat b ON b.tbl = s.relname::text
   WHERE s.schemaname = wsproof.data_schema() AND s.relname NOT IN ('m_rows', 'm_stat', 'ws_keep')
     AND (s.n_tup_ins, s.n_tup_upd, s.n_tup_del) IS DISTINCT FROM (coalesce(b.n_tup_ins, 0), coalesce(b.n_tup_upd, 0), coalesce(b.n_tup_del, 0));
  RETURN jsonb_build_object('placements', to_jsonb(placements), 'inserted', to_jsonb(coalesce(ins, '{}')), 'cancel_consumed', consumed, 'dml', dml);
END $$;

-- The legacy metrics of dev/sql/tests/fixtures/helpers.sql (tst.metrics),
-- without views, so they resolve through the search_path.
CREATE OR REPLACE FUNCTION wsproof.metrics(pesp varchar, pniv varchar, pper varchar) RETURNS text LANGUAGE plpgsql STABLE AS $$
DECLARE r text;
BEGIN
  WITH demand AS (
    SELECT pa.id AS plantilla, g.id AS grupo FROM plantilla_de_actividad pa JOIN desglose d ON d.id = pa.desglose
      JOIN asignatura a ON a.id = d.asignatura JOIN periodo pe ON pe.id = d.periodo
      JOIN grupo g ON g.clasificacion = pa.clasificacion AND g.especialidad = a.especialidad AND g.nivel = a.nivel AND g.planificacion = pe.planificacion
     WHERE a.especialidad = pesp AND a.nivel = pniv AND d.periodo = pper),
  placed AS (
    SELECT ac.id, ac.plantilla, ac.grupo, ac.profesor, ac.lugar, ac.dia, ac.turno, ac.turno + c.duracion - 1 AS turno_fin, d.periodo
      FROM actividad ac JOIN plantilla_de_actividad pa ON pa.id = ac.plantilla JOIN clasificacion_de_actividad c ON c.id = pa.clasificacion
      JOIN desglose d ON d.id = pa.desglose)
  SELECT format('demands=%s placed=%s unplaced=%s collisions=%s overflow=%s',
    (SELECT count(*) FROM demand),
    (SELECT count(DISTINCT (p.plantilla, p.grupo)) FROM placed p WHERE p.periodo = pper),
    (SELECT count(*) FROM demand dm WHERE NOT EXISTS (SELECT 1 FROM actividad a WHERE a.plantilla = dm.plantilla AND a.grupo = dm.grupo)),
    (SELECT count(*) FROM placed x JOIN placed y ON x.id < y.id AND x.periodo = y.periodo AND x.dia = y.dia
       AND x.turno <= y.turno_fin AND y.turno <= x.turno_fin
     WHERE x.profesor = y.profesor OR x.lugar = y.lugar
        OR EXISTS (SELECT 1 FROM brigada_de_grupo bx JOIN brigada_de_grupo by ON bx.brigada = by.brigada WHERE bx.grupo = x.grupo AND by.grupo = y.grupo)),
    (SELECT count(*) FROM placed p JOIN periodo pe ON pe.id = p.periodo WHERE p.turno_fin > pe.cant_turnos)) INTO r;
  RETURN r;
END $$;

-- The E2E placement list (dev/e2e/lib.sh e2e_placements) of a scope, with
-- every id cut after the marker (EngineClient::PLACEMENTS): its md5 is the
-- E2E digest.
CREATE OR REPLACE FUNCTION wsproof.e2e_list(pesp varchar, pniv varchar, pper varchar, pmarker text) RETURNS text LANGUAGE plpgsql STABLE AS $$
BEGIN
  RETURN (SELECT coalesce(string_agg(
        CASE WHEN pmarker <> '' AND strpos(a.plantilla, pmarker) > 0 THEN substr(a.plantilla, strpos(a.plantilla, pmarker) + length(pmarker)) ELSE a.plantilla END
        || '/' || CASE WHEN pmarker <> '' AND strpos(a.grupo, pmarker) > 0 THEN substr(a.grupo, strpos(a.grupo, pmarker) + length(pmarker)) ELSE a.grupo END
        || '/' || p.nombre
        || '/' || CASE WHEN pmarker <> '' AND strpos(a.lugar, pmarker) > 0 THEN substr(a.lugar, strpos(a.lugar, pmarker) + length(pmarker)) ELSE a.lugar END
        || '/' || a.dia || '/' || a.turno, ',' ORDER BY a.plantilla, a.grupo), '')
    FROM actividad a JOIN profesor p ON p.id = a.profesor JOIN plantilla_de_actividad pa ON pa.id = a.plantilla
    JOIN desglose d ON d.id = pa.desglose JOIN asignatura s ON s.id = d.asignatura
   WHERE s.especialidad = pesp AND s.nivel = pniv AND d.periodo = pper);
END $$;

-- One measured run in one statement (the worker's model: autocommit, READ
-- COMMITTED): snapshot, generar() with the job's options, outcome.
CREATE OR REPLACE FUNCTION wsproof.run(pesp varchar, pniv varchar, pper varchar, forma bigint, parche boolean, compactar bigint,
                                       parche1 boolean, buscalugar boolean) RETURNS jsonb LANGUAGE plpgsql AS $$
BEGIN
  PERFORM wsproof.before();
  PERFORM generar(pesp, pniv, pper, forma, parche, compactar, parche1, buscalugar);
  RETURN wsproof.outcome(pesp, pniv, pper) || jsonb_build_object('metrics', wsproof.metrics(pesp, pniv, pper));
END $$;

GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA wsproof TO PUBLIC;
