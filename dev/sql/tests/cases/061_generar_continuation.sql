-- expect: pass
-- refs: generador/generar.sql (skips demands already placed)
-- Re-running generar() on a complete timetable adds nothing (anchor/continuation semantics).
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
CREATE TEMP TABLE first_run AS SELECT plantilla, grupo, profesor, lugar, dia, turno FROM actividad;
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check((SELECT count(*) FROM actividad) = 4, 'still 4 placements');
SELECT tst.check(NOT EXISTS (SELECT plantilla, grupo, profesor, lugar, dia, turno FROM actividad EXCEPT SELECT * FROM first_run), 'placements unchanged');
