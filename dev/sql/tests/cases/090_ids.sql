-- expect: pass
-- refs: miscelanea/crear_cadena_aleatoria.sql (decision D-01), miscelanea/crear_id_de_replica.sql
-- DB-side ids. Seeded, so the run is deterministic. Historical quirk kept:
-- round(1 + random() * n) can index past the end, so some strings are shorter
-- than requested (see 091).
SELECT setseed(0.42);
CREATE TEMP TABLE s AS SELECT crear_cadena_aleatoria() AS v FROM generate_series(1, 500);
SELECT tst.check((SELECT bool_and(v ~ '^[a-z0-9]*$') FROM s), 'only the default alphabet');
SELECT tst.check((SELECT max(length(v)) FROM s) = 20, 'never longer than requested');
SELECT tst.check((SELECT count(*) FROM s WHERE length(v) < 20) > 0, 'HISTORICAL: some strings are shorter');
SELECT tst.check(length(crear_cadena_aleatoria('ab', 5)) <= 5, 'custom alphabet and length');
CREATE TEMP TABLE ids AS SELECT crear_id_de_replica('ancoragoh', 'asignatura', 'id', 'asignatura') AS v FROM generate_series(1, 200);
SELECT tst.check((SELECT count(DISTINCT v) FROM ids) = 200, 'replica ids are distinct');
