-- expect: pass
-- refs: actividad/desglose.sql (defaults); generador/rango.sql:26-28, :37-97; generador/generar.sql:585 (maxDia),
--       :831 (rango with min_dias, max_dias), :1062-1066 (CicloFin capped at maxDia), :1132-1140 (mismo_dia);
--       actividad/sube_orden_de_plantilla.sql; legacy-parity-inventory.md PAR-032..035, quirks Q-04, Q-24, D4
-- Effect of each breakdown (desglose) option on generar(). One group G1 and
-- one subject A with three templates T1..T3 (orders 1..3), one teacher and one
-- room; 5 days x 3 slots. The options are edited with UPDATE, as the legacy
-- handler set_Desglose.php does (one UPDATE per posted column).
-- * defaults 1, 5, true, false: one template per day, in template order;
-- * mismo_dia is not read while respetar_orden is true;
-- * respetar_orden false + mismo_dia true: the day range is the whole period,
--   so the three templates share day 1;
-- * respetar_orden false + mismo_dia false: only the first template (Q-04);
-- * min_dias: HISTORICAL, the first template starts at day min_dias, not 1.
--   rango.sql:37 counts the EMPTY days (get_count_of_value_in(arr, 0)), so the
--   "no activity yet" case is the c > 1 branch with da = 0: ini = 0 + min_dias
--   (quirk Q-24); min_dias 0 or less allows the same day;
-- * min_dias > max_dias: nothing is placed (empty ranges);
-- * template order: the day order follows "orden", and the legacy move
--   (sube_orden_de_plantilla) changes it;
-- * max_dias: on 10 days with the cohort unavailable on days 2-3, the second
--   template's range ends at da + max_dias (rango.sql:95); max 2 leaves only
--   days 2-3, so T2 and then T3 stay unplaced; max 5 reaches day 4.
\ir ../fixtures/base.sql
UPDATE periodo SET cant_dias = 5, cant_turnos = 3 WHERE id = 'PER';
DELETE FROM profesor_para_grupo WHERE grupo = 'G2';
DELETE FROM brigada_de_grupo WHERE grupo = 'G2';
DELETE FROM grupo WHERE id = 'G2';
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion)
     SELECT t, id, o, 'C1' FROM desglose, (VALUES ('T2', 2), ('T3', 3)) v(t, o) WHERE asignatura = 'A' AND periodo = 'PER';
INSERT INTO profesor_para_actividad (id, plantilla, profesor, prioridad) VALUES ('PPA2', 'T2', 'TA', 1), ('PPA3', 'T3', 'TA', 1);
INSERT INTO profesor_para_grupo (profesor_para_actividad, grupo) VALUES ('PPA2', 'G1'), ('PPA3', 'G1');
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad) VALUES ('T2', 'L1', 1), ('T3', 'L1', 1);

CREATE FUNCTION pg_temp.cells(mn int, mx int, ro bool, md bool) RETURNS text LANGUAGE plpgsql AS $$
BEGIN
  DELETE FROM actividad;
  UPDATE desglose SET min_dias = mn WHERE asignatura = 'A';
  UPDATE desglose SET max_dias = mx WHERE asignatura = 'A';
  UPDATE desglose SET respetar_orden = ro WHERE asignatura = 'A';
  UPDATE desglose SET mismo_dia = md WHERE asignatura = 'A';
  PERFORM generar('E', 'N', 'PER', 1, false, 0, false, false);
  RETURN coalesce((SELECT string_agg(format('%s@%s.%s', plantilla, dia, turno), ' ' ORDER BY plantilla) FROM actividad), '-');
END $$;

SELECT tst.check((SELECT min_dias = 1 AND max_dias = 5 AND respetar_orden AND NOT mismo_dia FROM desglose WHERE asignatura = 'A'),
                 'the trigger row has the defaults 1, 5, true, false');
SELECT tst.check(pg_temp.cells(1, 5, true, false) = 'T1@1.1 T2@2.1 T3@3.1', 'defaults: one template per day, in order');
SELECT tst.check(pg_temp.cells(1, 5, true, true) = 'T1@1.1 T2@2.1 T3@3.1', 'mismo_dia is not read while respetar_orden is true');
SELECT tst.check(pg_temp.cells(1, 5, false, true) = 'T1@1.1 T2@1.2 T3@1.3', 'respetar_orden false, mismo_dia true: the three templates share day 1');
SELECT tst.check(pg_temp.cells(1, 5, false, false) = 'T1@1.1', 'HISTORICAL (Q-04): respetar_orden false, mismo_dia false places only the first template');
SELECT tst.check(pg_temp.cells(2, 5, true, false) = 'T1@2.1 T2@4.1', 'HISTORICAL (Q-24): min_dias 2 starts at day 2; T3 would need day 6');
SELECT tst.check(pg_temp.cells(3, 5, true, false) = 'T1@3.1 T3@3.2', 'HISTORICAL (Q-24): min_dias 3, T1 on day 3; T3 (no order-2 neighbour) shares it, T2 is unplaced');
SELECT tst.check(pg_temp.cells(0, 5, true, false) = 'T1@1.1 T2@1.2 T3@1.3', 'min_dias 0: the same day is allowed');
SELECT tst.check(pg_temp.cells(-1, 5, true, false) = 'T1@1.1 T2@1.2 T3@1.3', 'a negative min_dias is used as is');
SELECT tst.check(pg_temp.cells(5, 1, true, false) = '-', 'min_dias > max_dias: nothing is placed');
SELECT tst.metrics('E', 'N', 'PER');

-- template order: the legacy move "left" of T3 twice makes it the first
SELECT sube_orden_de_plantilla('T3');
SELECT sube_orden_de_plantilla('T3');
SELECT tst.check((SELECT string_agg(id || '=' || orden, ' ' ORDER BY orden) FROM plantilla_de_actividad) = 'T3=1 T1=2 T2=3', 'sube_orden_de_plantilla swaps with the neighbour');
SELECT tst.check(pg_temp.cells(1, 5, true, false) = 'T1@2.1 T2@3.1 T3@1.1', 'respetar_orden: the days follow the template order');
SELECT tst.check(pg_temp.cells(1, 5, false, true) = 'T1@1.2 T2@1.3 T3@1.1', 'without respetar_orden the order still decides who is placed first');

-- max_dias, on 10 days x 1 slot with cohort B1 unavailable on days 2 and 3
SELECT sube_orden_de_plantilla('T1');
SELECT sube_orden_de_plantilla('T2');
UPDATE periodo SET cant_dias = 10, cant_turnos = 1 WHERE id = 'PER';
INSERT INTO restriccion_de_brigada (id, brigada, periodo, dia, turno, valor, por_actividad)
     SELECT 'RB1' || d, 'B1', 'PER', d, 1, 1, false FROM generate_series(2, 3) d;
SELECT tst.check(pg_temp.cells(1, 5, true, false) = 'T1@1.1 T2@4.1 T3@5.1', 'max_dias 5: T2 reaches day 4');
SELECT tst.check(pg_temp.cells(1, 3, true, false) = 'T1@1.1 T2@4.1 T3@5.1', 'max_dias 3: day 4 is still in range');
SELECT tst.check(pg_temp.cells(1, 2, true, false) = 'T1@1.1', 'max_dias 2: T2''s range is days 2-3 only, so T2 and T3 stay unplaced');
SELECT tst.check(pg_temp.cells(1, 2, false, true) = 'T1@1.1 T2@4.1 T3@5.1', 'without respetar_orden max_dias is not read');
