-- expect: xfail
-- sqlstate: 42703
-- refs: tiempo/restriccion/condicional/valor_restriccion_condicional.sql:13-34; assessment 6.18
-- Intended: an active conditional restriction is evaluated. The evaluator
-- references undefined names (siempre, rcdia_ini, act, rc), so any restriction
-- check of a teacher fails as soon as one active conditional row exists.
\ir ../fixtures/base.sql
INSERT INTO restriccion_condicional (id, planificacion, activa, consec_periodo, consec_profesor, siempre)
     VALUES ('RC1', 'P', true, 'PER', 'TA', false);
SELECT tst.check(valor_restriccion_profesor('TA', 'PER', 1, 1) IN (0, 1), 'evaluates with a conditional restriction');
