-- expect: pass
-- refs: ancoragoh.sql:131-134; tiempo/restriccion/nominal/restringe_por_uso_de_otro_recurso.sql (decision D-05);
--       generador/agrega_actividad.sql:22; assessment 6.19
-- Historical behavior: recording that an activity uses an other resource fails.
-- Without "creador" the insert violates NOT NULL (which is what
-- agrega_actividad() does); with it, the RULE calls a function that cannot compile.
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
  PERFORM tst.check(st = '23502', 'HISTORICAL: insert without creador -> not_null_violation, got ' || st);
  BEGIN
    INSERT INTO otros_recursos_usados (otro_recurso, actividad, creador) VALUES ('R1', 'ACT1', 'u1');
    st := '00000';
  EXCEPTION WHEN OTHERS THEN st := SQLSTATE;
  END;
  PERFORM tst.check(st = '42601', 'HISTORICAL: occupancy RULE fails to compile its function, got ' || st);
END $$;
