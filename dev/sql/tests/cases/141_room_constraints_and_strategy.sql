-- expect: pass
-- refs: recurso/asignacion/{lugar_fijo,acceso_prohibido,lugar_para_actividad}.sql; recurso/costo_de_traslado.sql;
--       generador/generar.sql:1183-1335 (1216: quita_segun_prohibidos with quote_literal(grupos[j]); 1228-1234 lugar_fijo;
--       1309 buscalugar); generador/busca_lugar_menos_costoso.sql; generador/and_mpi.sql:87;
--       disparadores/nivela_prioridad_lxact.sql, nivela_prioridad_lxact_after_update.sql;
--       legacy-parity-inventory.md PAR-055, 056, 060, 061, 063, 083, A-2, Q-07, D9c
-- Room constraints and the room strategy in generar(). Base fixture with
-- two templates on one day (respetar_orden off, mismo_dia on); both
-- templates may use L1, L2 and L3, all priority 1.
-- * buscalugar FALSE (most used by the group) and TRUE without travel costs
--   choose the same rooms; with travel costs, TRUE takes the cheapest room
--   from the rooms used around the cell (directional costs);
-- * a fixed room is chosen first; with two fixed rooms the first row wins;
-- * HISTORICAL (Q-30): a forbidden room does not change the placements,
--   even when every room is forbidden for the group: generar.sql:1216
--   passes the group id through quote_literal(), so es_lugar_prohibido()
--   never matches; a fixed room that is forbidden is still chosen;
-- * lugar_para_actividad: a delete closes the priority gap of its template
--   when no other room of the template keeps the priority; an update
--   relevels only when NO room assignment of any template keeps the old
--   priority (Q-07).
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
INSERT INTO lugar (id, nombre, capacidad, creador) VALUES ('L3', 'Room 3', 50, 'u1');
DELETE FROM lugar_para_actividad;
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad) SELECT t, l, 1 FROM (VALUES ('T1'), ('T2')) a(t), (VALUES ('L1'), ('L2'), ('L3')) b(l);
UPDATE desglose SET respetar_orden = false, mismo_dia = true;
CREATE FUNCTION pg_temp.cells(setup text, bl boolean) RETURNS text LANGUAGE plpgsql AS $$
BEGIN
  DELETE FROM actividad; DELETE FROM costo_de_traslado; DELETE FROM lugar_fijo; DELETE FROM acceso_prohibido;
  IF setup <> '' THEN EXECUTE setup; END IF;
  PERFORM generar('E', 'N', 'PER', 1, false, 0, false, bl);
  RETURN coalesce((SELECT string_agg(format('%s/%s@%s.%s:%s', plantilla, grupo, dia, turno, lugar), ' ' ORDER BY plantilla, grupo) FROM actividad), '-');
END $$;
CREATE FUNCTION pg_temp.costs(near text) RETURNS text LANGUAGE sql AS $$
  SELECT format($f$INSERT INTO costo_de_traslado SELECT o, d, CASE WHEN d = %L OR o = %L THEN 1 ELSE 9 END
     FROM (VALUES ('L1'), ('L2'), ('L3')) a(o), (VALUES ('L1'), ('L2'), ('L3')) b(d) WHERE o <> d$f$, near, near)
$$;
SELECT tst.check(pg_temp.cells('', false) = 'T1/G1@1.1:L1 T1/G2@1.3:L1 T2/G1@1.2:L1 T2/G2@1.1:L2', 'most used (FALSE)');
SELECT tst.check(pg_temp.cells('', true) = 'T1/G1@1.1:L1 T1/G2@1.3:L1 T2/G1@1.2:L1 T2/G2@1.1:L2', 'nearest (TRUE) without costs: the same rooms');
SELECT tst.check(pg_temp.cells(pg_temp.costs('L3'), true) = 'T1/G1@1.1:L1 T1/G2@1.3:L1 T2/G1@1.2:L1 T2/G2@1.1:L3', 'nearest with costs: L3 is cheapest');
SELECT tst.check(pg_temp.cells(pg_temp.costs('L3'), false) = 'T1/G1@1.1:L1 T1/G2@1.3:L1 T2/G1@1.2:L1 T2/G2@1.1:L2', 'most used ignores the costs');
SELECT tst.check(pg_temp.cells($q$INSERT INTO costo_de_traslado VALUES ('L1','L3',9),('L3','L1',1),('L2','L1',5),('L2','L3',5),('L3','L2',1),('L1','L2',9)$q$, true)
                 = 'T1/G1@1.1:L1 T1/G2@1.3:L1 T2/G1@1.2:L1 T2/G2@1.1:L3', 'directional costs (origin = candidate room)');
SELECT tst.check(pg_temp.cells($q$INSERT INTO lugar_fijo VALUES ('G1', 'L3')$q$, false) = 'T1/G1@1.1:L3 T1/G2@1.3:L1 T2/G1@1.2:L3 T2/G2@1.1:L1', 'fixed room first');
SELECT tst.check(pg_temp.cells($q$INSERT INTO lugar_fijo VALUES ('G1', 'L3'), ('G1', 'L2')$q$, false) = 'T1/G1@1.1:L3 T1/G2@1.3:L1 T2/G1@1.2:L3 T2/G2@1.1:L1', 'two fixed rooms: the first row wins');
SELECT tst.check(pg_temp.cells($q$INSERT INTO acceso_prohibido VALUES ('G1', 'L1')$q$, false) = 'T1/G1@1.1:L1 T1/G2@1.3:L1 T2/G1@1.2:L1 T2/G2@1.1:L2', 'HISTORICAL (Q-30): a forbidden room is still used');
SELECT tst.check(pg_temp.cells($q$INSERT INTO acceso_prohibido VALUES ('G1', 'L1'), ('G1', 'L2'), ('G1', 'L3')$q$, false) = 'T1/G1@1.1:L1 T1/G2@1.3:L1 T2/G1@1.2:L1 T2/G2@1.1:L2', 'HISTORICAL (Q-30): every room forbidden, still placed');
SELECT tst.check(pg_temp.cells($q$INSERT INTO acceso_prohibido VALUES ('G1', 'L3'); INSERT INTO lugar_fijo VALUES ('G1', 'L3')$q$, false) = 'T1/G1@1.1:L3 T1/G2@1.3:L1 T2/G1@1.2:L3 T2/G2@1.1:L1', 'HISTORICAL (Q-30): a forbidden fixed room is chosen');
SELECT tst.check(quita_segun_prohibidos(('{' || quote_literal('G1') || '}')::varchar[], '{L1,L2}') = '{L1,L2}', 'the quoted id never matches');
SELECT tst.metrics('E', 'N', 'PER');

-- priority upkeep of lugar_para_actividad
DELETE FROM actividad;
DELETE FROM lugar_para_actividad;
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad) VALUES ('T1', 'L1', 1), ('T1', 'L2', 2), ('T1', 'L3', 3), ('T2', 'L1', 1);
DELETE FROM lugar_para_actividad WHERE plantilla = 'T1' AND lugar = 'L2';
SELECT tst.check((SELECT string_agg(lugar || '=' || prioridad, ' ' ORDER BY lugar) FROM lugar_para_actividad WHERE plantilla = 'T1') = 'L1=1 L3=2', 'delete: the gap of the template is closed');
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad) VALUES ('T1', 'L2', 2);
DELETE FROM lugar_para_actividad WHERE plantilla = 'T1' AND lugar = 'L2';
SELECT tst.check((SELECT string_agg(lugar || '=' || prioridad, ' ' ORDER BY lugar) FROM lugar_para_actividad WHERE plantilla = 'T1') = 'L1=1 L3=2', 'delete: a priority still held by another room is not closed');
UPDATE lugar_para_actividad SET prioridad = 5 WHERE plantilla = 'T1' AND lugar = 'L3';
SELECT tst.check((SELECT string_agg(lugar || '=' || prioridad, ' ' ORDER BY lugar) FROM lugar_para_actividad WHERE plantilla = 'T1') = 'L1=1 L3=4',
                 'HISTORICAL (Q-07): an update that vacates a priority no template holds relevels the template, the updated row included');
UPDATE lugar_para_actividad SET prioridad = 3 WHERE plantilla = 'T1' AND lugar = 'L1';
SELECT tst.check((SELECT string_agg(lugar || '=' || prioridad, ' ' ORDER BY lugar) FROM lugar_para_actividad WHERE plantilla = 'T1') = 'L1=3 L3=4',
                 'HISTORICAL (Q-07): the vacated priority 1 is held by T2: no relevel');
