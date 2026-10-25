-- expect: pass
-- refs: ancoragoh.sql:131-134; tiempo/restriccion/nominal/restringe_por_uso_de_otro_recurso.sql (D-05);
--       actividad/otros_recursos_usados.sql; assessment 6.19; #24
-- Recording that an activity uses an other resource. "creador" is still
-- required (NOT NULL). With it, the occupancy RULE now records the booking.
-- Until #24 the RULE called a function that could not compile (42601).
\ir ../fixtures/base.sql
INSERT INTO otro_recurso (id, nombre, creador) VALUES ('R1', 'Projector 1', 'u1');
INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES ('ACT1', 'T1', 'G1', 'TA', 'L1', 1, 1, 'u1');
DO $$
DECLARE st text;
BEGIN
  BEGIN
    INSERT INTO otros_recursos_usados (otro_recurso, actividad) VALUES ('R1', 'ACT1');
    st := '00000';
  EXCEPTION WHEN OTHERS THEN st := SQLSTATE;
  END;
  PERFORM tst.check(st = '23502', 'insert without creador -> not_null_violation, got ' || st);
END $$;
INSERT INTO otros_recursos_usados (otro_recurso, actividad, creador) VALUES ('R1', 'ACT1', 'u1');
SELECT tst.check((SELECT count(*) FROM restriccion_de_otro_recurso WHERE actividad = 'ACT1' AND otro_recurso = 'R1' AND periodo = 'PER' AND dia = 1 AND turno = 1 AND valor = 1 AND por_actividad) = 1,
                 'occupancy recorded by the RULE');
SELECT tst.check((SELECT count(*) FROM restriccion_por_actividad_de_otro_recurso p JOIN restriccion_de_otro_recurso r ON r.id = p.restriccion_nominal WHERE p.actividad = 'ACT1') = 1,
                 'per-activity link recorded');
