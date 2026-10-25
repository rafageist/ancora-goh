-- expect: xfail
-- sqlstate: 42601
-- refs: tiempo/restriccion/nominal/restringe_por_uso_de_otro_recurso.sql (decision D-05, pending); assessment 6.19
-- Intended: using an other resource records its occupancy, like teachers and places.
\ir ../fixtures/base.sql
INSERT INTO otro_recurso (id, nombre, creador) VALUES ('R1', 'Projector 1', 'u1');
INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES ('ACT1', 'T1', 'G1', 'TA', 'L1', 1, 1, 'u1');
INSERT INTO otros_recursos_usados (otro_recurso, actividad, creador) VALUES ('R1', 'ACT1', 'u1');
SELECT tst.check(valor_restriccion_otro_recurso('R1', 'PER', 1, 1) = 1, 'resource occupied at (1,1)');
