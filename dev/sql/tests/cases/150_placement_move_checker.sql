-- expect: pass
-- refs: generador/posible_inicio.sql:78-188 (period, activity type, subject, brigade, teacher, room checks; 6.2: the
--       arrays are read from index 0, so a template's only teacher or room is never checked); tiempo/restriccion/
--       valor_restriccion_{periodo,clasif_act,asignatura,brigada,profesor,lugar,otro_recurso}.sql, valor_restriccion_generica.sql
--       (nominal, then inherited and conditional rows; real dates across periods); sql-characterization.md 6.2, 6.17; issue #123
-- Characterization only (legacy SQL unchanged): a server-side check of a move
-- destination built from the engine's own restriction functions, evaluated
-- with the moved activity's occupancy removed (in a subtransaction that is
-- rolled back), compared with what the database accepts on DELETE + INSERT.
-- * Every destination the check accepts is accepted by the database.
-- * The check is stricter where the database trusts the caller: a period
--   restriction and a start whose duration overflows the period.
-- * posible_inicio() is not a usable check for a move: with one eligible
--   teacher it never reads that teacher (6.2) and accepts a cell where the
--   teacher already teaches.
-- * A teacher shared with another plan's period on the same real dates: the
--   unique keys are per period, so the database accepts the double booking.
--   The restriction functions read only this period's rows, so the check
--   does not see it either; a move check must look at the other periods.
\ir ../fixtures/base.sql
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
CREATE TEMP TABLE a AS SELECT * FROM actividad WHERE grupo = 'G1';
CREATE TEMP TABLE b AS SELECT * FROM actividad WHERE grupo = 'G2';
INSERT INTO restriccion_de_periodo (id, periodo, dia, turno, valor)
  SELECT 'PR', 'PER', d, t, 1 FROM generate_series(1, 2) d, generate_series(1, 3) t
   WHERE (d, t) NOT IN (SELECT dia, turno FROM actividad) ORDER BY d DESC, t DESC LIMIT 1;

-- The destination check: every slot of the duration inside the period, and
-- no restriction or occupancy of the period, activity type, subject, the
-- group's brigades, the activity's teacher and room, and its other resources.
CREATE FUNCTION pg_temp.ok_without(pid varchar, pdia int, pturno int) RETURNS boolean LANGUAGE plpgsql AS $$
DECLARE r record; per varchar; dur int; slots int; i int; brg varchar; res varchar; ok boolean := true;
BEGIN
  SELECT * INTO r FROM actividad WHERE id = pid;
  per := dame_periodo_plantilla_de_actividad(r.plantilla);
  dur := dame_duracion_plantilla_de_actividad(r.plantilla);
  SELECT cant_turnos INTO slots FROM periodo WHERE id = per;
  CREATE TEMP TABLE IF NOT EXISTS probe_res (otro_recurso varchar);
  DELETE FROM probe_res;
  INSERT INTO probe_res SELECT otro_recurso FROM otros_recursos_usados WHERE actividad = pid;
  BEGIN
    DELETE FROM actividad WHERE id = pid;  -- its occupancy goes (cascade); undone below
    IF pturno < 1 OR pturno + dur - 1 > slots OR pdia < 1 OR pdia > (SELECT cant_dias FROM periodo WHERE id = per) THEN
      ok := false;
    END IF;
    FOR i IN 0 .. dur - 1 LOOP
      EXIT WHEN NOT ok;
      ok := valor_restriccion_periodo(per, pdia, pturno + i) = 0
        AND valor_restriccion_clasif_act(dame_clasif_plantilla_de_actividad(r.plantilla), per, pdia, pturno + i) = 0
        AND valor_restriccion_asignatura(dame_asignatura_plantilla_de_actividad(r.plantilla), per, pdia, pturno + i) = 0
        AND (r.profesor IS NULL OR valor_restriccion_profesor(r.profesor, per, pdia, pturno + i) = 0)
        AND (r.lugar IS NULL OR valor_restriccion_lugar(r.lugar, per, pdia, pturno + i) = 0);
      FOREACH brg IN ARRAY dame_lista_id_brigadas_de_grupo(r.grupo) LOOP
        ok := ok AND valor_restriccion_brigada(brg, per, pdia, pturno + i) = 0;
      END LOOP;
      FOR res IN SELECT otro_recurso FROM probe_res LOOP
        ok := ok AND valor_restriccion_otro_recurso(res, per, pdia, pturno + i) = 0;
      END LOOP;
    END LOOP;
    RAISE EXCEPTION 'probe:%', CASE WHEN ok THEN 'yes' ELSE 'no' END;
  EXCEPTION WHEN raise_exception THEN
    IF SQLERRM LIKE 'probe:%' THEN RETURN SQLERRM = 'probe:yes'; END IF;
    RAISE;
  END;
END $$;

-- What the database accepts: DELETE + INSERT, always rolled back.
CREATE FUNCTION pg_temp.accepted(pid varchar, pdia int, pturno int) RETURNS boolean LANGUAGE plpgsql AS $$
DECLARE r record;
BEGIN
  SELECT * INTO r FROM actividad WHERE id = pid;
  BEGIN
    DELETE FROM actividad WHERE id = pid;
    INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, momento_de_colocacion, creador)
         VALUES (r.id, r.plantilla, r.grupo, r.profesor, r.lugar, pdia, pturno, r.momento_de_colocacion, r.creador);
    RAISE EXCEPTION 'probe:accepted';
  EXCEPTION
    WHEN unique_violation THEN RETURN false;
    WHEN raise_exception THEN IF SQLERRM = 'probe:accepted' THEN RETURN true; END IF; RAISE;
  END;
END $$;

CREATE TEMP TABLE grid AS
  SELECT d AS dia, t AS turno, pg_temp.ok_without((SELECT id FROM a), d, t) AS ok, pg_temp.accepted((SELECT id FROM a), d, t) AS db
    FROM generate_series(1, 2) d, generate_series(1, 3) t;
SELECT tst.check((SELECT count(*) FROM actividad) = 2 AND (SELECT (actividad.dia, actividad.turno) = (a.dia, a.turno) FROM actividad, a WHERE actividad.id = a.id),
                 'the probes changed nothing');
SELECT tst.check(NOT EXISTS (SELECT 1 FROM grid WHERE ok AND NOT db), 'every destination the check accepts is accepted by the database');
SELECT tst.check((SELECT ok FROM grid g, a WHERE g.dia = a.dia AND g.turno = a.turno), 'its own cell is a valid destination (its occupancy is excluded)');
SELECT tst.check((SELECT NOT ok AND NOT db FROM grid g, b WHERE g.dia = b.dia AND g.turno = b.turno), 'G2''s cell (same teacher and room): refused by both');
SELECT tst.check((SELECT NOT ok AND db FROM grid g JOIN restriccion_de_periodo p ON p.dia = g.dia AND p.turno = g.turno WHERE p.id = 'PR'),
                 'a period-restricted cell: refused by the check, accepted by the database');
SELECT tst.check((SELECT count(*) FROM grid WHERE ok) = 4, 'the other cells are valid (6 cells - G2''s - the restricted one)');

-- posible_inicio() at G2's cell for G1's brigade: TRUE although TA teaches there (6.2).
SELECT tst.check(valor_restriccion_profesor('TA', 'PER', (SELECT dia FROM b), (SELECT turno FROM b)) > 0, 'TA is busy at G2''s cell');
SELECT tst.check(posible_inicio('T1', (SELECT dia FROM b), (SELECT turno FROM b), 'B1', NULL, NULL), 'posible_inicio() says yes there: not a move check');

-- Duration: a 2-slot activity may not start in the last slot (the database would accept it, case 149).
INSERT INTO clasificacion_de_actividad (id, descripcion, duracion, creador, prioridad, gasto) VALUES ('C2', 'Block', 2, 'u1', 2, 1);
INSERT INTO brigada (id, descripcion, especialidad, nivel, matricula, creador) VALUES ('B3', 'Cohort 3', 'E', 'N', 20, 'u1');
INSERT INTO grupo (id, descripcion, clasificacion, especialidad, planificacion, nivel) VALUES ('GB', 'Block group', 'C2', 'E', 'P', 'N');
INSERT INTO brigada_de_grupo (brigada, grupo) VALUES ('B3', 'GB');
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion) SELECT 'TBK', id, 2, 'C2' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER';
INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES ('ABK', 'TBK', 'GB', 'TB', 'L2', 1, 1, 'u1');
SELECT tst.check(NOT pg_temp.ok_without('ABK', 1, 3) AND pg_temp.accepted('ABK', 1, 3), 'turno 3 of 3 for 2 slots: refused by the check, accepted by the database');
SELECT tst.check(pg_temp.ok_without('ABK', 1, 2), 'turno 2 of 3 for 2 slots is valid');

-- A teacher shared with another plan's period on the same real dates.
INSERT INTO planificacion (id, descripcion, fecha_inicial, cant_dias, creador) VALUES ('P2', 'Plan 2', 0, 30, 'u1');
INSERT INTO periodo (id, descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion) VALUES ('PER2', 'Week 2', 2, 3, 8, 1, 'P2');
INSERT INTO periodicidad (id, periodo, dia) VALUES ('PD2', 'PER2', 1);
INSERT INTO grupo (id, descripcion, clasificacion, especialidad, planificacion, nivel) VALUES ('G9', 'Group 9', 'C1', 'E', 'P2', 'N');
INSERT INTO brigada (id, descripcion, especialidad, nivel, matricula, creador) VALUES ('B9', 'Cohort 9', 'E', 'N', 20, 'u1');
INSERT INTO brigada_de_grupo (brigada, grupo) VALUES ('B9', 'G9');
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion) SELECT 'T9', id, 1, 'C1' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER2';
CREATE TEMP TABLE shared AS SELECT dia, turno FROM grid WHERE ok AND (dia, turno) <> (SELECT dia, turno FROM a) ORDER BY dia, turno LIMIT 1;
INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador)
  SELECT 'A9', 'T9', 'G9', 'TA', 'L2', dia, turno, 'u1' FROM shared;
SELECT tst.check(pg_temp.accepted((SELECT id FROM a), (SELECT dia FROM shared), (SELECT turno FROM shared)),
                 'the database accepts TA at the same time in PER (per-period unique keys)');
SELECT tst.check(pg_temp.ok_without((SELECT id FROM a), (SELECT dia FROM shared), (SELECT turno FROM shared)),
                 'and the restriction functions, which read this period only, do not see TA''s other plan either');
