-- expect: xfail
-- sqlstate: 23514
-- refs: tiempo/restriccion/herencia/check_hrt.sql:29-43, valor_restriccion_heredada.sql; assessment 6.8
-- Intended: a teacher -> teacher HRT row is accepted and the child inherits the
-- parent's restriction. Fails at the CHECK constraint (23514).
\ir ../fixtures/base.sql
INSERT INTO herencia_de_restricciones (id, planificacion, activa, hijo_profesor, padre_profesor) VALUES ('H1', 'P', true, 'TB', 'TA');
INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad) VALUES ('RP1', 'TA', 'PER', 1, 1, 1, false);
SELECT tst.check(valor_restriccion_profesor('TB', 'PER', 1, 1) = 1, 'child inherits parent restriction');
