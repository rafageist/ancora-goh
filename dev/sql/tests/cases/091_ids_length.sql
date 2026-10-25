-- expect: xfail
-- sqlstate: P0001
-- refs: miscelanea/crear_cadena_aleatoria.sql (posicion := round(1 + random() * char_length(...)))
-- Intended: every random string has the requested length.
SELECT setseed(0.42);
SELECT tst.check((SELECT bool_and(length(crear_cadena_aleatoria()) = 20) FROM generate_series(1, 500)), 'all strings have length 20');
