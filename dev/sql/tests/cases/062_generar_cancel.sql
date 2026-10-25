-- expect: pass
-- refs: generador/esquema_generador.sql (generador.cancelar, fue_cancelado); generar.sql cancellation checks
-- A pending cancellation row makes generar() return without placing, and is consumed.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
INSERT INTO generador.cancelar (especialidad, nivel, periodo) VALUES ('E', 'N', 'PER');
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.metrics('E', 'N', 'PER');
SELECT tst.check((SELECT count(*) FROM actividad) = 0, 'nothing placed');
SELECT tst.check((SELECT count(*) FROM generador.cancelar) = 0, 'cancellation consumed');
