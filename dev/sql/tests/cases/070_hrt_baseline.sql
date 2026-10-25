-- expect: pass
-- refs: tiempo/restriccion/herencia/check_hrt.sql:29-43 (iif overwrite); assessment 6.8
-- Historical HRT (restriction inheritance) acceptance: check_hrt() overwrites
-- the detected type with each iif(), so only rows whose child and parent are
-- both "other resources" pass the CHECK.
\ir ../fixtures/base.sql
INSERT INTO otro_recurso (id, nombre, creador) VALUES ('R1', 'Projector 1', 'u1'), ('R2', 'Projector 2', 'u1');
DO $$
DECLARE st text;
BEGIN
  BEGIN
    INSERT INTO herencia_de_restricciones (id, planificacion, activa, hijo_profesor, padre_profesor) VALUES ('H1', 'P', true, 'TB', 'TA');
    st := '00000';
  EXCEPTION WHEN check_violation THEN st := '23514';
  END;
  PERFORM tst.check(st = '23514', 'HISTORICAL: teacher -> teacher inheritance rejected by CHECK');
  BEGIN
    INSERT INTO herencia_de_restricciones (id, planificacion, activa, hijo_brigada, padre_brigada) VALUES ('H2', 'P', true, 'B2', 'B1');
    st := '00000';
  EXCEPTION WHEN check_violation THEN st := '23514';
  END;
  PERFORM tst.check(st = '23514', 'HISTORICAL: brigade -> brigade inheritance rejected by CHECK');
END $$;
INSERT INTO herencia_de_restricciones (id, planificacion, activa, hijo_otro_recurso, padre_otro_recurso) VALUES ('H3', 'P', true, 'R1', 'R2');
SELECT tst.check(check_hrt(NULL, NULL, NULL, 'R1', NULL, NULL, NULL, 'R2'), 'other resource -> other resource accepted');
SELECT tst.check(NOT check_hrt(NULL, NULL, NULL, 'R1', NULL, NULL, NULL, 'R1'), 'self inheritance rejected');
