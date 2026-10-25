-- Test helpers (issue #10), installed into the suite's template database only.
CREATE SCHEMA tst;

-- Fail the test with SQLSTATE P0001 (raise_exception) unless cond is true.
CREATE FUNCTION tst.check(cond boolean, msg text) RETURNS void LANGUAGE plpgsql AS $$
BEGIN
  IF cond IS DISTINCT FROM TRUE THEN
    RAISE EXCEPTION 'assertion failed: %', msg;
  END IF;
END $$;

-- Demands: template x group pairs that generar() is expected to place for a
-- specialty/level/period (group of the same classification, specialty, level
-- and plan as the template's subject and period).
CREATE VIEW tst.demand AS
SELECT pa.id AS plantilla, g.id AS grupo, d.periodo, a.especialidad, a.nivel
FROM plantilla_de_actividad pa
JOIN desglose d ON d.id = pa.desglose
JOIN asignatura a ON a.id = d.asignatura
JOIN periodo pe ON pe.id = d.periodo
JOIN grupo g ON g.clasificacion = pa.clasificacion AND g.especialidad = a.especialidad
            AND g.nivel = a.nivel AND g.planificacion = pe.planificacion;

-- Placed activities with their slot interval.
CREATE VIEW tst.placed AS
SELECT ac.id, ac.plantilla, ac.grupo, ac.profesor, ac.lugar, ac.dia, ac.turno,
       ac.turno + c.duracion - 1 AS turno_fin, d.periodo
FROM actividad ac
JOIN plantilla_de_actividad pa ON pa.id = ac.plantilla
JOIN clasificacion_de_actividad c ON c.id = pa.clasificacion
JOIN desglose d ON d.id = pa.desglose;

-- Overlapping placements that share a teacher, a place or a brigade.
CREATE VIEW tst.collision AS
SELECT x.id AS a, y.id AS b,
       CASE WHEN x.profesor = y.profesor THEN 'teacher'
            WHEN x.lugar = y.lugar THEN 'place' ELSE 'brigade' END AS resource
FROM tst.placed x JOIN tst.placed y
  ON x.id < y.id AND x.periodo = y.periodo AND x.dia = y.dia
 AND x.turno <= y.turno_fin AND y.turno <= x.turno_fin
WHERE x.profesor = y.profesor OR x.lugar = y.lugar
   OR EXISTS (SELECT 1 FROM brigada_de_grupo bx JOIN brigada_de_grupo by ON bx.brigada = by.brigada
              WHERE bx.grupo = x.grupo AND by.grupo = y.grupo);

-- One METRIC line for the runner report.
CREATE FUNCTION tst.metrics(pesp varchar, pniv varchar, pper varchar) RETURNS text LANGUAGE sql AS $$
  SELECT format('METRIC demands=%s placed=%s unplaced=%s collisions=%s overflow=%s',
    (SELECT count(*) FROM tst.demand WHERE especialidad = pesp AND nivel = pniv AND periodo = pper),
    (SELECT count(DISTINCT (p.plantilla, p.grupo)) FROM tst.placed p WHERE p.periodo = pper),
    (SELECT count(*) FROM tst.demand d WHERE d.especialidad = pesp AND d.nivel = pniv AND d.periodo = pper
       AND NOT EXISTS (SELECT 1 FROM actividad a WHERE a.plantilla = d.plantilla AND a.grupo = d.grupo)),
    (SELECT count(*) FROM tst.collision),
    (SELECT count(*) FROM tst.placed p JOIN periodo pe ON pe.id = p.periodo WHERE p.turno_fin > pe.cant_turnos))
$$;
