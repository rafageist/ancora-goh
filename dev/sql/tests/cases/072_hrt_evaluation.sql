-- expect: xfail
-- sqlstate: 42804
-- refs: tiempo/restriccion/herencia/dame_tipo_hijo_hrt.sql:8; assessment 6.9
-- Intended: with an active HRT row, restriction evaluation still returns 0/1.
-- iif(anyelement) with two untyped literals fails on modern PostgreSQL
-- ("could not determine polymorphic type", 42804).
\ir ../fixtures/base.sql
INSERT INTO otro_recurso (id, nombre, creador) VALUES ('R1', 'Projector 1', 'u1'), ('R2', 'Projector 2', 'u1');
INSERT INTO herencia_de_restricciones (id, planificacion, activa, hijo_otro_recurso, padre_otro_recurso) VALUES ('H3', 'P', true, 'R1', 'R2');
SELECT tst.check(valor_restriccion_otro_recurso('R1', 'PER', 1, 1) IN (0, 1), 'evaluates with HRT present');
