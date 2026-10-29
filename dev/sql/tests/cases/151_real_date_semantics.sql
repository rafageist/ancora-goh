-- expect: pass
-- refs: tiempo/restriccion/real/actualiza_restriccion_real.sql (fecha = dia_inicial + periodicidad.dia - 1 + dia - 1;
--       hora_final = hora_inicial + duracion_del_turno), tiempo/configuracion/dame_hora_inicial_turno.sql (receso),
--       tiempo/configuracion/dame_dia_real_planificacion.sql, tiempo/configuracion/periodicidad.sql, tiempo/date_to_number.sql,
--       view/custom/form_periodo_periodicidad.tpl:31-49 (a periodicity day starts a run of cant_dias consecutive plan days),
--       legacy-parity-inventory.md PAR-072, Q-01, Q-20; docs/modernization/temporal-semantics.md; issue #133
-- Characterization only (legacy SQL unchanged): the calendar the legacy
-- real-date rows encode, used as EVIDENCE of the temporal semantics (the rows
-- themselves are not a reliable source: Q-01, Q-20).
-- * A plan's day numbers are consecutive calendar days: date_to_number(d)
--   is the number of days since 1999-12-31 (from 2000 on).
-- * A periodicity row (periodo, dia) starts one repetition of the period on
--   plan day "dia"; period day d of that repetition is plan day dia + d - 1,
--   so the date is fecha_inicial + dia - 1 + d - 1. No weekday is involved,
--   repetitions are listed explicitly, and nothing clips a repetition at the
--   end of the plan.
-- * Every booked slot gets its own row per repetition: [hora_inicial,
--   hora_inicial + duracion_del_turno), the start shifted by the breaks
--   (receso) before the slot. A 2-slot activity with a break between its slots
--   is two intervals with a gap, not one.
-- * Hours are whatever unit the period holds (the UI stores minutes; Q-20
--   puts the number of days in hora_inicial, so the real hours are wrong).
-- * Without a periodicity row there is no real row at all; a plan without a
--   date (fecha_inicial 0, the Drupal export of a plan without starts_on)
--   still gets dates, around 1999-12-31: legacy cannot tell "unknown".
\ir ../fixtures/base.sql

-- 1. Day numbers are consecutive calendar days from 1999-12-31 (sampled every
--    day of 2000..2031, leap years included).
SELECT tst.check(bool_and(tiempo.date_to_number(extract(day FROM d)::int, extract(month FROM d)::int, extract(year FROM d)::int) = d::date - date '1999-12-31'),
                 'date_to_number(d) = d - 1999-12-31 for every day of 2000-2031')
  FROM generate_series(date '2000-01-01', date '2031-12-31', interval '1 day') d;

-- The plan from Monday 2026-09-07; the period: 5 days, 3 slots, 08:00 in
-- minutes (480), 90-minute slots, a 30-minute break after slot 1; repetitions
-- on plan days 1 and 8 (two weeks), and one on plan day 27 that runs past the
-- plan's 28 days.
UPDATE planificacion SET fecha_inicial = tiempo.date_to_number(7, 9, 2026), cant_dias = 28 WHERE id = 'P';
UPDATE periodo SET hora_inicial = 480, duracion_del_turno = 90 WHERE id = 'PER';
INSERT INTO periodicidad (id, periodo, dia) VALUES ('PD8', 'PER', 8), ('PD27', 'PER', 27);
INSERT INTO receso (periodo, despues_del_turno, duracion) VALUES ('PER', 1, 30);
SELECT tst.check(extract(isodow FROM date '1999-12-31' + tiempo.date_to_number(7, 9, 2026)::int) = 1, '2026-09-07 is a Monday');

-- 2. Slot starts with the break: slot 1 at 480, slot 2 at 480 + 90 + 30 = 600.
SELECT tst.check(dame_hora_inicial_turno('PER', 1) = 480 AND dame_hora_inicial_turno('PER', 2) = 600 AND dame_hora_inicial_turno('PER', 3) = 690,
                 'slot starts 480, 600, 690 (the break after slot 1 shifts the later slots)');

-- 3. A 2-slot activity of teacher TB on period day 2, slots 1-2.
INSERT INTO clasificacion_de_actividad (id, descripcion, duracion, creador, prioridad, gasto) VALUES ('C2', 'Block', 2, 'u1', 2, 1);
INSERT INTO brigada (id, descripcion, especialidad, nivel, matricula, creador) VALUES ('B3', 'Cohort 3', 'E', 'N', 20, 'u1');
INSERT INTO grupo (id, descripcion, clasificacion, especialidad, planificacion, nivel) VALUES ('GB', 'Block group', 'C2', 'E', 'P', 'N');
INSERT INTO brigada_de_grupo (brigada, grupo) VALUES ('B3', 'GB');
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion) SELECT 'TBK', id, 2, 'C2' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER';
INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES ('ABK', 'TBK', 'GB', 'TB', 'L2', 2, 1, 'u1');

CREATE TEMP VIEW occ AS
  SELECT date '1999-12-31' + r.fecha::int AS day, r.hora_inicial, r.hora_final, n.turno
    FROM restriccion_real_de_profesor r JOIN restriccion_de_profesor n ON n.id = r.restriccion_nominal
   WHERE n.actividad = 'ABK';
SELECT tst.check((SELECT string_agg(day || ' ' || hora_inicial || '-' || hora_final, ', ' ORDER BY day, hora_inicial) FROM occ) =
  '2026-09-08 480-570, 2026-09-08 600-690, 2026-09-15 480-570, 2026-09-15 600-690, 2026-10-04 480-570, 2026-10-04 600-690',
  'one row per slot and repetition: period day 2 = plan days 2, 9 and 28; slots [480,570) and [600,690), the break between them left out');
SELECT tst.check((SELECT bool_and(extract(isodow FROM day) = 2) FROM occ WHERE day < date '2026-10-01'), 'period day 2 of the weekly repetitions is a Tuesday (no weekday logic: plan day 1 is a Monday)');
SELECT tst.check((SELECT max(day) FROM occ) = date '2026-10-04' AND date '2026-09-07' + 28 - 1 = date '2026-10-04',
                 'the repetition from plan day 27 reaches day 28 = the last day of the plan; a later period day would pass it, nothing clips');
INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador)
  SELECT 'A5', 'T1', 'G1', 'TA', 'L1', 5, 1, 'u1';
SELECT tst.check((SELECT max(date '1999-12-31' + r.fecha::int) FROM restriccion_real_de_profesor r JOIN restriccion_de_profesor n ON n.id = r.restriccion_nominal WHERE n.actividad = 'A5')
                 = date '2026-10-07', 'period day 5 of the repetition from plan day 27 is plan day 31, after the 28-day plan: dated anyway');

-- 4. Unknown states: a period without a periodicity row gets no real row; a
--    plan without a date (fecha_inicial 0) gets dates around 1999-12-31.
INSERT INTO planificacion (id, descripcion, fecha_inicial, cant_dias, creador) VALUES ('P0', 'No dates', 0, 30, 'u1');
INSERT INTO periodo (id, descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion) VALUES ('PE0', 'Undated', 5, 3, 480, 90, 'P0');
INSERT INTO periodo (id, descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion) VALUES ('PEN', 'No periodicity', 5, 3, 480, 90, 'P');
INSERT INTO periodicidad (id, periodo, dia) VALUES ('PD0', 'PE0', 1);
INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor) VALUES ('R0', 'TA', 'PE0', 1, 1, 1), ('RN', 'TA', 'PEN', 1, 1, 1);
SELECT tst.check((SELECT count(*) FROM restriccion_real_de_profesor WHERE restriccion_nominal = 'RN') = 0, 'no periodicity: no real row, nothing to compare');
SELECT tst.check((SELECT date '1999-12-31' + fecha::int FROM restriccion_real_de_profesor WHERE restriccion_nominal = 'R0') = date '1999-12-31',
                 'fecha_inicial 0: dated 1999-12-31, indistinguishable from a real date');

-- 5. Q-20: with the number of days in hora_inicial (as the legacy UI posts
--    it), the real hours start at 5, not at the chosen start hour.
UPDATE periodo SET hora_inicial = cant_dias WHERE id = 'PEN';
INSERT INTO periodicidad (id, periodo, dia) VALUES ('PDN', 'PEN', 1);
INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor) VALUES ('RQ', 'TA', 'PEN', 1, 2, 1);
SELECT tst.check((SELECT hora_inicial FROM restriccion_real_de_profesor WHERE restriccion_nominal = 'RQ') = 5 + 90, 'Q-20: slot 2 "starts" at 5 + 90');
