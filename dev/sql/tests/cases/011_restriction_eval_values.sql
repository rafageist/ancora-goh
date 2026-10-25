-- expect: pass
-- refs: tiempo/restriccion/valor_restriccion_{profesor,lugar,brigada,periodo,generica}.sql
-- Nominal restriction values per object, day and slot (default settings).
\ir ../fixtures/base.sql
INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad) VALUES ('RP1', 'TA', 'PER', 1, 2, 1, false);
INSERT INTO restriccion_de_lugar    (id, lugar,    periodo, dia, turno, valor, por_actividad) VALUES ('RL1', 'L1', 'PER', 2, 1, 1, false);
INSERT INTO restriccion_de_brigada  (id, brigada,  periodo, dia, turno, valor, por_actividad) VALUES ('RB1', 'B1', 'PER', 2, 3, 1, false);
SELECT tst.check(valor_restriccion_profesor('TA', 'PER', 1, 2) = 1, 'restricted teacher cell = 1');
SELECT tst.check(valor_restriccion_profesor('TA', 'PER', 1, 1) = 0, 'free teacher cell = 0');
SELECT tst.check(valor_restriccion_profesor('TB', 'PER', 1, 2) = 0, 'other teacher unaffected');
SELECT tst.check(valor_restriccion_lugar('L1', 'PER', 2, 1) = 1, 'restricted place cell = 1');
SELECT tst.check(valor_restriccion_lugar('L2', 'PER', 2, 1) = 0, 'other place unaffected');
SELECT tst.check(valor_restriccion_brigada('B1', 'PER', 2, 3) = 1, 'restricted brigade cell = 1');
SELECT tst.check(valor_restriccion_brigada('B2', 'PER', 2, 3) = 0, 'other brigade unaffected');
SELECT tst.check(valor_restriccion_periodo('PER', 1, 1) = 0, 'unrestricted period cell = 0');
