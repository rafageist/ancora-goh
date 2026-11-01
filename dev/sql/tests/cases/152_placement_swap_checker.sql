-- expect: pass
-- refs: tiempo/restriccion/nominal/restringe_segun_actividad.sql, restringe_por_uso_de_otro_recurso.sql (#24);
--       tiempo/restriccion/nominal/restriccion_de_{profesor,lugar,brigada,otro_recurso}.sql (unique keys);
--       tiempo/restriccion/valor_restriccion_*.sql; cases 149, 150; VB6 TAncora.permuta (cls/ancora_goh_traditional_TAncora.cls:7339-7564);
--       docs/modernization/timetable-editing.md §10; issue #136
-- Characterization only (legacy SQL unchanged): what a SWAP of two placed
-- activities (each takes the other's day and first slot, keeping its own
-- group, teacher, room, resources and duration) needs to be valid, and what
-- the database accepts on DELETE both + INSERT both.
-- * The pair check removes BOTH activities' rows in one rolled-back
--   subtransaction, then checks each activity at the other's cell over its
--   whole duration with the restriction functions of case 150 (bounds,
--   period, activity type, subject, every brigade, teacher, room, resources)
--   AND checks the two against each other at their new cells (a shared
--   teacher, room, cohort or resource with overlapping slots on the same
--   day): neither activity's own row is there to collide with in the check.
-- * Two moves checked one at a time (each with the other still in place)
--   are refused whenever the two share a teacher, room, cohort or resource:
--   each target cell is the other's. Yet the swap is valid (A). With both
--   removed, each candidate can be valid while the two collide with each
--   other at the swapped cells (B, E, F): the mutual check is needed.
-- * The database refuses every collision (23505, all or nothing) but trusts
--   period restrictions and overflow, as for a move (case 149): the pair
--   check is stricter there.
\ir ../fixtures/base.sql
-- A 2-slot class with groups GB (cohort B3) and GC (cohort B1, shared with G1);
-- a resource R1; a break after slot 1 (it shifts the hours only).
INSERT INTO clasificacion_de_actividad (id, descripcion, duracion, creador, prioridad, gasto) VALUES ('C2', 'Block', 2, 'u1', 2, 1);
INSERT INTO brigada (id, descripcion, especialidad, nivel, matricula, creador) VALUES ('B3', 'Cohort 3', 'E', 'N', 20, 'u1');
INSERT INTO grupo (id, descripcion, clasificacion, especialidad, planificacion, nivel) VALUES ('GB', 'Block group', 'C2', 'E', 'P', 'N'), ('GC', 'Shared cohort group', 'C2', 'E', 'P', 'N');
INSERT INTO brigada_de_grupo (brigada, grupo) VALUES ('B3', 'GB'), ('B1', 'GC');
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion) SELECT 'TB2', id, 2, 'C2' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER';
INSERT INTO otro_recurso (id, nombre, creador) VALUES ('R1', 'Projector', 'u1');
UPDATE periodo SET hora_inicial = 480, duracion_del_turno = 90 WHERE id = 'PER';
INSERT INTO receso (periodo, despues_del_turno, duracion) VALUES ('PER', 1, 30);

-- An activity (and its resources) as a record that survives its deletion.
CREATE TEMP TABLE act (id varchar PRIMARY KEY, plantilla varchar, grupo varchar, profesor varchar, lugar varchar, dia int, turno int, recursos varchar[]);
CREATE FUNCTION pg_temp.put(pid varchar, ptpl varchar, pgrp varchar, pprof varchar, plug varchar, pdia int, pturno int, precursos varchar[] DEFAULT '{}') RETURNS void LANGUAGE plpgsql AS $$
DECLARE r varchar;
BEGIN
  INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES (pid, ptpl, pgrp, pprof, plug, pdia, pturno, 'u1');
  FOREACH r IN ARRAY precursos LOOP
    INSERT INTO otros_recursos_usados (otro_recurso, actividad, creador) VALUES (r, pid, 'u1');
  END LOOP;
  INSERT INTO act VALUES (pid, ptpl, pgrp, pprof, plug, pdia, pturno, precursos);
END $$;
CREATE FUNCTION pg_temp.reset() RETURNS void LANGUAGE sql AS $$
  DELETE FROM actividad; DELETE FROM act; DELETE FROM restriccion_de_profesor WHERE NOT por_actividad; DELETE FROM restriccion_de_periodo;
$$;

-- An activity at a cell, with the current occupancy (case 150's check).
CREATE FUNCTION pg_temp.ok_at(a act, pdia int, pturno int) RETURNS boolean LANGUAGE plpgsql AS $$
DECLARE per varchar := dame_periodo_plantilla_de_actividad(a.plantilla); dur int := dame_duracion_plantilla_de_actividad(a.plantilla);
  slots int; i int; brg varchar; res varchar; ok boolean := true;
BEGIN
  SELECT cant_turnos INTO slots FROM periodo WHERE id = per;
  IF pturno + dur - 1 > slots THEN RETURN false; END IF;
  FOR i IN 0 .. dur - 1 LOOP
    ok := ok AND valor_restriccion_periodo(per, pdia, pturno + i) = 0
      AND valor_restriccion_clasif_act(dame_clasif_plantilla_de_actividad(a.plantilla), per, pdia, pturno + i) = 0
      AND valor_restriccion_asignatura(dame_asignatura_plantilla_de_actividad(a.plantilla), per, pdia, pturno + i) = 0
      AND valor_restriccion_profesor(a.profesor, per, pdia, pturno + i) = 0
      AND valor_restriccion_lugar(a.lugar, per, pdia, pturno + i) = 0;
    FOREACH brg IN ARRAY dame_lista_id_brigadas_de_grupo(a.grupo) LOOP
      ok := ok AND valor_restriccion_brigada(brg, per, pdia, pturno + i) = 0;
    END LOOP;
    FOREACH res IN ARRAY a.recursos LOOP
      ok := ok AND valor_restriccion_otro_recurso(res, per, pdia, pturno + i) = 0;
    END LOOP;
  END LOOP;
  RETURN ok;
END $$;

-- The two activities at their swapped cells against each other.
CREATE FUNCTION pg_temp.mutual_conflict(x act, y act) RETURNS boolean LANGUAGE plpgsql AS $$
DECLARE dx int := dame_duracion_plantilla_de_actividad(x.plantilla); dy int := dame_duracion_plantilla_de_actividad(y.plantilla);
BEGIN
  -- X goes to (y.dia, y.turno), Y to (x.dia, x.turno).
  IF y.dia <> x.dia OR NOT (y.turno < x.turno + dy AND x.turno < y.turno + dx) THEN
    RETURN false;
  END IF;
  RETURN x.profesor = y.profesor OR x.lugar = y.lugar
      OR dame_lista_id_brigadas_de_grupo(x.grupo) && dame_lista_id_brigadas_de_grupo(y.grupo)
      OR x.recursos && y.recursos;
END $$;

-- The pair check: both rows removed (rolled back), both candidates, mutual.
CREATE FUNCTION pg_temp.pair(px varchar, py varchar) RETURNS text LANGUAGE plpgsql AS $$
DECLARE x act; y act; okx boolean; oky boolean; mut boolean;
BEGIN
  SELECT * INTO x FROM act WHERE id = px; SELECT * INTO y FROM act WHERE id = py;
  BEGIN
    DELETE FROM actividad WHERE id IN (px, py);
    okx := pg_temp.ok_at(x, y.dia, y.turno);
    oky := pg_temp.ok_at(y, x.dia, x.turno);
    mut := pg_temp.mutual_conflict(x, y);
    RAISE EXCEPTION 'probe:%/%/%', CASE WHEN okx THEN 'ok' ELSE 'no' END, CASE WHEN oky THEN 'ok' ELSE 'no' END, CASE WHEN mut THEN 'mutual' ELSE 'none' END;
  EXCEPTION WHEN raise_exception THEN
    IF SQLERRM LIKE 'probe:%' THEN RETURN substr(SQLERRM, 7); END IF;
    RAISE;
  END;
END $$;
-- The pair check's verdict.
CREATE FUNCTION pg_temp.valid(px varchar, py varchar) RETURNS boolean LANGUAGE sql AS $$ SELECT pg_temp.pair(px, py) = 'ok/ok/none' $$;

-- One move checked alone: only its own row removed (case 150).
CREATE FUNCTION pg_temp.alone(pid varchar, pdia int, pturno int) RETURNS boolean LANGUAGE plpgsql AS $$
DECLARE a act; ok boolean;
BEGIN
  SELECT * INTO a FROM act WHERE id = pid;
  BEGIN
    DELETE FROM actividad WHERE id = pid;
    ok := pg_temp.ok_at(a, pdia, pturno);
    RAISE EXCEPTION 'probe:%', CASE WHEN ok THEN 'yes' ELSE 'no' END;
  EXCEPTION WHEN raise_exception THEN
    IF SQLERRM LIKE 'probe:%' THEN RETURN SQLERRM = 'probe:yes'; END IF;
    RAISE;
  END;
END $$;

-- What the database accepts: DELETE both, INSERT both swapped (with their
-- resources), always rolled back. 'accepted' or the SQLSTATE.
CREATE FUNCTION pg_temp.db_swap(px varchar, py varchar) RETURNS text LANGUAGE plpgsql AS $$
DECLARE x act; y act; r varchar;
BEGIN
  SELECT * INTO x FROM act WHERE id = px; SELECT * INTO y FROM act WHERE id = py;
  BEGIN
    DELETE FROM actividad WHERE id IN (px, py);
    INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES (x.id, x.plantilla, x.grupo, x.profesor, x.lugar, y.dia, y.turno, 'u1');
    FOREACH r IN ARRAY x.recursos LOOP INSERT INTO otros_recursos_usados (otro_recurso, actividad, creador) VALUES (r, x.id, 'u1'); END LOOP;
    INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES (y.id, y.plantilla, y.grupo, y.profesor, y.lugar, x.dia, x.turno, 'u1');
    FOREACH r IN ARRAY y.recursos LOOP INSERT INTO otros_recursos_usados (otro_recurso, actividad, creador) VALUES (r, y.id, 'u1'); END LOOP;
    RAISE EXCEPTION 'probe:accepted';
  EXCEPTION
    WHEN unique_violation THEN RETURN '23505';
    WHEN raise_exception THEN IF SQLERRM = 'probe:accepted' THEN RETURN 'accepted'; END IF; RAISE;
  END;
END $$;

-- A. Same teacher TA, equal durations, two days: each move alone is refused
--    (the target cell is the other's), the swap is valid and accepted.
SELECT pg_temp.put('X', 'T1', 'G1', 'TA', 'L1', 1, 1), pg_temp.put('Y', 'T1', 'G2', 'TA', 'L2', 2, 3);
SELECT tst.check(NOT pg_temp.alone('X', 2, 3) AND NOT pg_temp.alone('Y', 1, 1), 'A: each move alone is refused (TA teaches at the other cell)');
SELECT tst.check(pg_temp.pair('X', 'Y') = 'ok/ok/none', 'A: the pair check accepts the swap: ' || pg_temp.pair('X', 'Y'));
SELECT tst.check(pg_temp.db_swap('X', 'Y') = 'accepted', 'A: the database accepts it');
SELECT tst.check((SELECT string_agg(id || '@' || dia || '.' || turno, ' ' ORDER BY id) FROM actividad) = 'X@1.1 Y@2.3', 'A: the probes changed nothing');

-- B. Same teacher, different durations, same day: X (1 slot) at slot 1, Y
--    (2 slots) at slots 2-3. Swapped, X takes slot 2 and Y slots 1-2. Each
--    candidate is valid with both removed, but they collide with each other.
SELECT pg_temp.reset();
SELECT pg_temp.put('X', 'T1', 'G1', 'TA', 'L1', 1, 1), pg_temp.put('Y', 'TB2', 'GB', 'TA', 'L2', 1, 2);
SELECT tst.check(pg_temp.pair('X', 'Y') = 'ok/ok/mutual', 'B: both candidates valid alone, mutual conflict: ' || pg_temp.pair('X', 'Y'));
SELECT tst.check(NOT pg_temp.valid('X', 'Y'), 'B: not a valid swap');
SELECT tst.check(pg_temp.db_swap('X', 'Y') = '23505', 'B: the database refuses it (TA twice in slot 2), nothing kept');

-- C. A 2-slot activity across the break: Y (TB, slots 1-2 of day 2) and X
--    (TA, slot 1 of day 1) swap days: valid; Y then books slots 1 and 2 of
--    day 1, the break leaving a gap in its real hours only.
SELECT pg_temp.reset();
SELECT pg_temp.put('X', 'T1', 'G1', 'TA', 'L1', 1, 1), pg_temp.put('Y', 'TB2', 'GB', 'TB', 'L2', 2, 1);
SELECT tst.check(pg_temp.valid('X', 'Y') AND pg_temp.db_swap('X', 'Y') = 'accepted', 'C: a 2-slot activity swapped across the break');
DELETE FROM actividad;
INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES ('Y', 'TB2', 'GB', 'TB', 'L2', 1, 1, 'u1');
SELECT tst.check((SELECT string_agg(n.turno || ':' || r.hora_inicial || '-' || r.hora_final, ' ' ORDER BY n.turno) FROM restriccion_de_profesor n JOIN restriccion_real_de_profesor r ON r.restriccion_nominal = n.id WHERE n.actividad = 'Y')
                 = '1:480-570 2:600-690', 'C: both slots booked, 480-570 and 600-690 (the 30-minute break between them)');
-- C2. Its last slot would pass the period: Y (2 slots) to slot 3 of 3.
SELECT pg_temp.reset();
SELECT pg_temp.put('X', 'T1', 'G1', 'TA', 'L1', 1, 3), pg_temp.put('Y', 'TB2', 'GB', 'TB', 'L2', 2, 1);
SELECT tst.check(pg_temp.pair('X', 'Y') = 'ok/no/none', 'C2: Y would end after the last slot: ' || pg_temp.pair('X', 'Y'));
SELECT tst.check(pg_temp.db_swap('X', 'Y') = 'accepted', 'C2: the database accepts the overflow (trusted, case 149)');

-- D. Restrictions at a destination: a manual restriction of X's teacher
--    (refused by both), a period restriction (refused by the check only).
SELECT pg_temp.reset();
SELECT pg_temp.put('X', 'T1', 'G1', 'TA', 'L1', 1, 1), pg_temp.put('Y', 'T1', 'G2', 'TB', 'L2', 2, 2);
SELECT tst.check(pg_temp.valid('X', 'Y'), 'D: valid without restrictions');
INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad) VALUES ('M1', 'TA', 'PER', 2, 2, 1, false);
SELECT tst.check(pg_temp.pair('X', 'Y') = 'no/ok/none' AND pg_temp.db_swap('X', 'Y') = '23505', 'D: a manual restriction of TA at Y''s cell: refused by both');
DELETE FROM restriccion_de_profesor WHERE id = 'M1';
INSERT INTO restriccion_de_periodo (id, periodo, dia, turno, valor) VALUES ('PR', 'PER', 1, 1, 1);
SELECT tst.check(pg_temp.pair('X', 'Y') = 'ok/no/none' AND pg_temp.db_swap('X', 'Y') = 'accepted', 'D: a period restriction at X''s cell: refused by the check, accepted by the database');

-- E. A shared cohort: G1 and GC both have B1. X (1 slot, slot 1) and Y
--    (2 slots, slots 2-3), other teachers and rooms: they collide on B1.
SELECT pg_temp.reset();
SELECT pg_temp.put('X', 'T1', 'G1', 'TA', 'L1', 1, 1), pg_temp.put('Y', 'TB2', 'GC', 'TB', 'L2', 1, 2);
SELECT tst.check(pg_temp.pair('X', 'Y') = 'ok/ok/mutual' AND pg_temp.db_swap('X', 'Y') = '23505', 'E: the shared cohort B1 in slot 2: refused by both');

-- F. A shared other resource R1, other teachers, rooms and cohorts.
SELECT pg_temp.reset();
SELECT pg_temp.put('X', 'T1', 'G1', 'TA', 'L1', 1, 1, ARRAY['R1']::varchar[]), pg_temp.put('Y', 'TB2', 'GB', 'TB', 'L2', 1, 2, ARRAY['R1']::varchar[]);
SELECT tst.check(pg_temp.pair('X', 'Y') = 'ok/ok/mutual' AND pg_temp.db_swap('X', 'Y') = '23505', 'F: R1 in slot 2 twice: refused by both');

-- G. Nothing shared, different days: the pair check equals two moves alone.
SELECT pg_temp.reset();
SELECT pg_temp.put('X', 'T1', 'G1', 'TA', 'L1', 1, 1), pg_temp.put('Y', 'T1', 'G2', 'TB', 'L2', 2, 2);
SELECT tst.check(pg_temp.valid('X', 'Y') AND pg_temp.alone('X', 2, 2) AND pg_temp.alone('Y', 1, 1), 'G: nothing shared: two moves alone and the swap agree');
