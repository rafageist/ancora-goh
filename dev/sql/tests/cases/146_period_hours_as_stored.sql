-- expect: pass
-- refs: www/js/app/server/ancoragoh_server_periodos.js:8,20 (hora_inicial posted as cant_dias, Q-20);
--       view/custom/form_periodo.tpl (slot length in minutes, TimeUtilsCommon::hour_to_number = h*60+m, default 1:30);
--       tiempo/configuracion/dame_hora_inicial_turno.sql; RULEs crea_restriccion_real_de_* (ancoragoh.sql);
--       legacy-parity-inventory.md PAR-011, PAR-072, A-1, Q-20
-- The period hours as the legacy UI stores them: hora_inicial = the number of
-- days (Q-20) and duracion_del_turno in minutes. The engine treats both as
-- hours: dame_hora_inicial_turno() gives hora_inicial + (turno - 1) *
-- duracion (+ breaks), which the real-date rows store; nothing else reads
-- them. The placements of a run are the same with the hours a person meant
-- (8, 1) and with the stored ones (5, 90).
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
CREATE FUNCTION pg_temp.cells() RETURNS text LANGUAGE plpgsql AS $$
DECLARE out text;
BEGIN
  DELETE FROM actividad;
  PERFORM generar('E', 'N', 'PER', 1, false, 1, false, true);
  SELECT string_agg(plantilla || '/' || grupo || '/' || profesor || '/' || lugar || '@' || dia || '.' || turno, ' ' ORDER BY plantilla, grupo) INTO out FROM actividad;
  RETURN out;
END $$;
UPDATE periodo SET hora_inicial = 8, duracion_del_turno = 1;
CREATE TEMP TABLE meant AS SELECT pg_temp.cells() AS c;
SELECT tst.check(dame_hora_inicial_turno('PER', 2) = 9, 'meant hours: slot 2 at 9');
-- the legacy UI: 2 days posted as the start hour, 1:30 as 90
UPDATE periodo SET hora_inicial = cant_dias, duracion_del_turno = 90;
SELECT tst.check((SELECT c FROM meant) = pg_temp.cells(), 'the stored values give the same placements');
SELECT tst.check(dame_hora_inicial_turno('PER', 1) = 2 AND dame_hora_inicial_turno('PER', 2) = 92, 'HISTORICAL: slot hours 2, 92 (days + minutes read as hours)');
INSERT INTO restriccion_de_brigada (id, brigada, periodo, dia, turno, valor, por_actividad) VALUES ('RB', 'B1', 'PER', 2, 2, 1, false);
SELECT tst.check((SELECT hora_inicial || '-' || hora_final FROM restriccion_real_de_brigada r JOIN restriccion_de_brigada n ON n.id = r.restriccion_nominal WHERE n.id = 'RB') = '92-182',
                 'HISTORICAL: the real-date row stores those hours');
