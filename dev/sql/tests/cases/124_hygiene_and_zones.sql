-- expect: pass
-- refs: actividad/higiene/es_higienico_profesor.sql, dame_gasto_de_plantilla.sql; tiempo/zpriori/dame_zpriori.sql;
--       legacy-parity-inventory.md PAR-077, PAR-078
-- * Teacher workload (higiene_para_profesor.gasto_maximo_diario): a day is
--   acceptable while (1 + existing activities of the teacher that day) x the
--   candidate template's classification cost (gasto) stays within the limit;
--   without a row there is no limit (assessment 6.21: the candidate's cost is
--   used for every existing activity).
-- * Zone priorities default to 1 for every cell without a zpriori row.
\ir ../fixtures/base.sql
UPDATE clasificacion_de_actividad SET gasto = 2;
SELECT tst.check(es_higienico_profesor('TA', 'T1', 1), 'no hygiene row: no limit');
INSERT INTO higiene_para_profesor (periodo, profesor, gasto_maximo_diario) VALUES ('PER', 'TA', 3);
SELECT tst.check(es_higienico_profesor('TA', 'T1', 1), 'first activity: cost 2 <= 3');
INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES ('H1', 'T1', 'G1', 'TA', 'L1', 1, 1, 'u1');
SELECT tst.check(NOT es_higienico_profesor('TA', 'T1', 1), 'second activity same day: 4 > 3');
SELECT tst.check(es_higienico_profesor('TA', 'T1', 2), 'other day unaffected');
SELECT tst.check(dame_zpriori('C1', 'PER') = '{{1,1,1},{1,1,1}}'::int[], 'zones default to 1');
INSERT INTO zpriori (clasificacion, periodo, dia, turno, valor) VALUES ('C1', 'PER', 2, 3, 2);
SELECT tst.check(dame_zpriori('C1', 'PER') = '{{1,1,1},{1,1,2}}'::int[], 'explicit zone value');
