-- expect: pass
-- refs: tiempo/configuracion/receso.sql, dame_hora_inicial_turno.sql; tiempo/zpriori/{zpriori,dame_zpriori}.sql;
--       generador/generar.sql:548-560; actividad/higiene/{higiene_para_profesor,higiene_para_brigada,
--       es_higienico_profesor,es_higienico_brigada}.sql; generador/generar.sql:1088-1121;
--       legacy-parity-inventory.md PAR-013, PAR-077, PAR-078, D9b
-- Breaks (receso), priority zones (zpriori) and daily load limits (higiene)
-- and generar(). Base fixture with two templates: 2 days x 3 slots, groups G1,
-- G2, templates T1 (teacher TA) and T2 (teacher TB), cost 1.
-- * a break changes the start hours (dame_hora_inicial_turno), never the
--   placements;
-- * zones have no effect with the options the legacy window sends
--   (parche = parche1 = false, Q-19), nor with parche; with parche1 they
--   change the cells;
-- * a teacher limit of 1 leaves T1 for one group per day, so T1/G2 stays
--   unplaced and T2 moves; a limit of 0 places no activity of the teacher;
--   a brigade limit of 0 places nothing for its group; a limit equal to the
--   day's load changes nothing;
-- * the limit is NOT NULL and part of the key: two limits of one teacher can
--   coexist; zpriori valor must be > 0.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
CREATE FUNCTION pg_temp.cells(setup text, p1 boolean DEFAULT false, pa boolean DEFAULT false) RETURNS text LANGUAGE plpgsql AS $$
BEGIN
  DELETE FROM actividad; DELETE FROM zpriori; DELETE FROM higiene_para_profesor; DELETE FROM higiene_para_brigada; DELETE FROM receso;
  IF setup <> '' THEN EXECUTE setup; END IF;
  PERFORM generar('E', 'N', 'PER', 1, pa, 0, p1, false);
  RETURN coalesce((SELECT string_agg(format('%s/%s@%s.%s', plantilla, grupo, dia, turno), ' ' ORDER BY plantilla, grupo) FROM actividad), '-');
END $$;
SELECT tst.check(pg_temp.cells('') = 'T1/G1@1.1 T1/G2@1.3 T2/G1@2.1 T2/G2@2.3', 'no input');
-- breaks
SELECT tst.check(pg_temp.cells($q$INSERT INTO receso VALUES ('PER', 1, 0.5)$q$) = 'T1/G1@1.1 T1/G2@1.3 T2/G1@2.1 T2/G2@2.3', 'a break does not move the placements');
INSERT INTO receso VALUES ('PER', 2, 0.25);
SELECT tst.check(dame_hora_inicial_turno('PER', 1) = 8 AND dame_hora_inicial_turno('PER', 2) = 9.5 AND dame_hora_inicial_turno('PER', 3) = 10.75,
                 'slot start hours add every earlier break');
DELETE FROM receso;
-- zones
SELECT tst.check(pg_temp.cells($q$INSERT INTO zpriori VALUES ('C1', 'PER', 1, 1, 3), ('C1', 'PER', 1, 2, 2)$q$) = 'T1/G1@1.1 T1/G2@1.3 T2/G1@2.1 T2/G2@2.3', 'zones: no effect with the window''s options');
SELECT tst.check(pg_temp.cells($q$INSERT INTO zpriori VALUES ('C1', 'PER', 1, 1, 3), ('C1', 'PER', 1, 2, 2)$q$, false, true) = 'T1/G1@1.1 T1/G2@1.3 T2/G1@2.1 T2/G2@2.3', 'zones: no effect with parche');
SELECT tst.check(pg_temp.cells('', true) = 'T1/G1@1.3 T1/G2@1.2 T2/G1@2.3 T2/G2@2.2', 'parche1 without zones');
SELECT tst.check(pg_temp.cells($q$INSERT INTO zpriori SELECT 'C1', 'PER', d, t, 2 FROM generate_series(1, 2) d, generate_series(1, 3) t WHERE (d, t) <> (2, 3)$q$, true)
                 = 'T1/G1@1.1 T1/G2@1.3 T2/G1@2.3 T2/G2@2.2', 'zones change the cells with parche1');
DO $$ BEGIN
  INSERT INTO zpriori VALUES ('C1', 'PER', 1, 1, 0);
  RAISE EXCEPTION 'zone 0 was accepted';
EXCEPTION WHEN check_violation THEN NULL;
END $$;
-- load limits
SELECT tst.check(pg_temp.cells($q$INSERT INTO higiene_para_profesor VALUES ('PER', 'TA', 1)$q$) = 'T1/G1@1.1 T2/G1@2.1 T2/G2@1.3', 'HISTORICAL: teacher limit 1');
SELECT tst.check(pg_temp.cells($q$INSERT INTO higiene_para_profesor VALUES ('PER', 'TA', 0)$q$) = 'T2/G1@1.1 T2/G2@1.3', 'teacher limit 0: nothing of TA');
SELECT tst.check(pg_temp.cells($q$INSERT INTO higiene_para_brigada VALUES ('PER', 'B1', 0)$q$) = 'T1/G2@1.3 T2/G2@2.3', 'brigade limit 0: nothing of G1');
SELECT tst.check(pg_temp.cells($q$INSERT INTO higiene_para_brigada VALUES ('PER', 'B1', 1)$q$) = 'T1/G1@1.1 T1/G2@1.3 T2/G1@2.1 T2/G2@2.3', 'brigade limit 1: one activity a day fits');
SELECT tst.metrics('E', 'N', 'PER');
INSERT INTO higiene_para_profesor VALUES ('PER', 'TA', 3), ('PER', 'TA', 5);
SELECT tst.check((SELECT count(*) FROM higiene_para_profesor WHERE profesor = 'TA') = 2, 'two limits of one teacher coexist (the key includes the limit)');
DO $$ BEGIN
  INSERT INTO higiene_para_profesor VALUES ('PER', 'TB', NULL);
  RAISE EXCEPTION 'a NULL limit was accepted';
EXCEPTION WHEN not_null_violation THEN NULL;
END $$;
