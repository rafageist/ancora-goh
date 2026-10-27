-- expect: pass
-- refs: generador/generar.sql:281-288 (forma: cantCiclo1/cantCiclo2), :585 (maxDia := CD - ((cantActs[i] - nivel_actual)
--       - compactar) * IIf(respetar_orden, 1, 0)); www/js/app/client/wins/ancoragoh_client_wins_generador.js:5-11
--       (defaults compactar 1, priorizar 1); legacy-parity-inventory.md PAR-081, PAR-082, D11
-- D11: what the two strategy options of the legacy generator window do.
-- One teacher TA for everything; subject A: T1, T2 (class C1, group G1);
-- subject B: U1 (class C2, priority 2, group G3).
-- * forma 1 ("classification order"): the classes are the outer loop, so
--   T1 and T2 come before U1; forma 2 ("activities per subject"): the
--   activity levels are the outer loop, so U1 (level 1) comes before T2.
--   Visible with respetar_orden off (all on one day).
-- * compactar, with respetar_orden: the last day an activity may use is
--   CD - (activities of its subject after it - compactar). With 0, T1 keeps a
--   day free for T2; with 1 or more, T1 may take the last day. Days 1-3
--   blocked on a 4-day period: compactar 0 places T2 (not T1) on day 4,
--   compactar 1 places T1 there (T2 then has no later day). Without
--   respetar_orden compactar is not read.
\ir ../fixtures/base.sql
DELETE FROM brigada_de_grupo WHERE grupo = 'G2';
DELETE FROM grupo WHERE id = 'G2';
INSERT INTO clasificacion_de_actividad (id, descripcion, duracion, creador, prioridad, gasto) VALUES ('C2', 'Lab', 1, 'u1', 2, 1);
INSERT INTO grupo (id, descripcion, clasificacion, especialidad, planificacion, nivel) VALUES ('G3', 'Lab group', 'C2', 'E', 'P', 'N');
INSERT INTO brigada_de_grupo (brigada, grupo) VALUES ('B1', 'G3');
INSERT INTO asignatura (id, descripcion, especialidad, nivel, creador) VALUES ('B', 'Subject B', 'E', 'N', 'u1');
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion) SELECT 'T2', id, 2, 'C1' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER';
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion) SELECT 'U1', id, 1, 'C2' FROM desglose WHERE asignatura = 'B' AND periodo = 'PER';
INSERT INTO profesor_para_actividad (id, plantilla, profesor, prioridad) VALUES ('PPA2', 'T2', 'TA', 1), ('PPA3', 'U1', 'TA', 1);
INSERT INTO profesor_para_grupo (profesor_para_actividad, grupo) VALUES ('PPA2', 'G1'), ('PPA3', 'G3');
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad) VALUES ('T2', 'L1', 1), ('U1', 'L1', 1);
CREATE FUNCTION pg_temp.cells(forma int, compactar int) RETURNS text LANGUAGE plpgsql AS $$
DECLARE out text;
BEGIN
  DELETE FROM actividad;
  PERFORM generar('E', 'N', 'PER', forma, false, compactar, false, false);
  SELECT coalesce(string_agg(plantilla || '@' || dia || '.' || turno, ' ' ORDER BY dia, turno), '-') INTO out FROM actividad;
  RETURN out;
END $$;
CREATE FUNCTION pg_temp.expect(got text, want text, msg text) RETURNS void LANGUAGE plpgsql AS $$
BEGIN
  PERFORM tst.check(got = want, msg || ': got ' || got);
END $$;

UPDATE desglose SET respetar_orden = false, mismo_dia = true;
SELECT pg_temp.expect(pg_temp.cells(1, 0), 'T1@1.1 T2@1.2 U1@1.3', 'forma 1: class C1 first');
SELECT pg_temp.expect(pg_temp.cells(2, 0), 'T1@1.1 U1@1.2 T2@1.3', 'forma 2: activity level 1 of every subject first');

UPDATE desglose SET respetar_orden = true, mismo_dia = false;
UPDATE periodo SET cant_dias = 4;
SELECT pg_temp.expect(pg_temp.cells(1, 0), 'T1@1.1 U1@1.2 T2@2.1', 'free days: compactar 0');
SELECT pg_temp.expect(pg_temp.cells(1, 1), 'T1@1.1 U1@1.2 T2@2.1', 'free days: compactar 1 gives the same cells');
INSERT INTO restriccion_de_periodo (id, periodo, dia, turno)
     SELECT 'X' || d || t, 'PER', d, t FROM generate_series(1, 3) d, generate_series(1, 3) t;
SELECT pg_temp.expect(pg_temp.cells(1, 0), 'T2@4.1 U1@4.2', 'compactar 0: T1 keeps a day for T2, so T1 is not placed');
SELECT pg_temp.expect(pg_temp.cells(1, 1), 'T1@4.1 U1@4.2', 'compactar 1: T1 may take the last day');
SELECT pg_temp.expect(pg_temp.cells(1, 10), 'T1@4.1 U1@4.2', 'compactar 10: the same here');
UPDATE desglose SET respetar_orden = false;
SELECT pg_temp.expect(pg_temp.cells(1, 0), 'T1@4.1 U1@4.2', 'without respetar_orden compactar is not read');
