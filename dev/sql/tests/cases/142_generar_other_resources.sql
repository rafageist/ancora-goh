-- expect: pass
-- refs: generador/generar.sql:1366-1393 (gr from 0, EXIT WHEN gr = cantGrupoRecursos, necesarios of gruporecursos[i]);
--       generador/posible_inicio.sql:191-254 (i and j from 0, k carried over; 6.3, 6.13); generador/agrega_actividad.sql;
--       recurso/asignacion/{otro_grupo_de_recursos_para_actividad,otro_recurso_para_actividad}.sql;
--       recurso/dame_uso_de_otro_recurso.sql; legacy-parity-inventory.md PAR-057, PAR-059, Q-31, D9d
-- The other resources generar() gives the activities it places
-- (otros_recursos_usados), base fixture (T1 for G1 and G2, one teacher, so
-- G1 at 1.1 and G2 at 1.3) with two_templates.sql (T2 on day 2).
-- * Only the groups BEFORE the template's last group (entry order) give
--   resources: the loop reads gruporecursos[0] (empty) up to
--   gruporecursos[n - 1]. One group gives nothing.
-- * Each group gives as many resources as the "needed" of the template's
--   FIRST group (rgruporecurso is read from gruporecursos[i], i = 1 here),
--   ordered by resource priority, then use, then travel cost.
-- * The group priority is never read; neither is "essential" for the choice.
-- * posible_inicio never finds a resource unavailable, but an essential
--   group (not the last) with more "needed" than resources blocks the
--   template; a non-essential one does not.
-- * The choice has no availability check: a chosen resource that is
--   restricted at the cell fails the run with 23505 and keeps nothing (D-05).
--   A restriction of a resource that is never chosen has no effect.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
INSERT INTO otro_recurso (id, nombre, creador) VALUES ('R1', 'P1', 'u1'), ('R2', 'P2', 'u1'), ('R3', 'P3', 'u1'), ('R4', 'P4', 'u1'), ('R5', 'P5', 'u1');
CREATE FUNCTION pg_temp.run(groups text, members text, restricted text DEFAULT NULL) RETURNS text LANGUAGE plpgsql AS $$
DECLARE out text;
BEGIN
  DELETE FROM actividad; DELETE FROM otro_recurso_para_actividad; DELETE FROM otro_grupo_de_recursos_para_actividad;
  DELETE FROM restriccion_de_otro_recurso;
  EXECUTE 'INSERT INTO otro_grupo_de_recursos_para_actividad (id, plantilla, prioridad, necesarios, imprescindibles, creador) VALUES ' || groups;
  IF members <> '' THEN
    EXECUTE 'INSERT INTO otro_recurso_para_actividad (grupo_de_recursos, prioridad, otro_recurso) VALUES ' || members;
  END IF;
  IF restricted IS NOT NULL THEN
    INSERT INTO restriccion_de_otro_recurso (id, otro_recurso, periodo, dia, turno, valor, por_actividad) VALUES ('X', restricted, 'PER', 1, 1, 1, false);
  END IF;
  BEGIN
    PERFORM generar('E', 'N', 'PER', 1, false, 0, false, false);
  EXCEPTION WHEN OTHERS THEN
    RETURN 'ERROR ' || SQLSTATE || ' kept ' || (SELECT count(*) FROM actividad);
  END;
  SELECT coalesce(string_agg(a.plantilla || '/' || a.grupo || ':' || u.otro_recurso, ' ' ORDER BY a.plantilla, a.grupo, u.ctid), '-') INTO out
    FROM otros_recursos_usados u JOIN actividad a ON a.id = u.actividad;
  RETURN out || ' (' || (SELECT count(*) FROM actividad) || ' placed)';
END $$;
CREATE FUNCTION pg_temp.expect(got text, want text, msg text) RETURNS void LANGUAGE plpgsql AS $$
BEGIN
  PERFORM tst.check(got = want, msg || ': got ' || got);
END $$;

SELECT pg_temp.expect(pg_temp.run($$('GR1','T1',1,1,true,'u1')$$, $$('GR1',1,'R1')$$), '- (4 placed)',
                      'HISTORICAL: one group gives no resource');
SELECT pg_temp.expect(pg_temp.run($$('GR1','T1',1,1,true,'u1'),('GR2','T1',2,1,true,'u1')$$, $$('GR1',1,'R1'),('GR2',1,'R2')$$),
                      'T1/G1:R1 T1/G2:R1 (4 placed)', 'HISTORICAL: two groups give only the first group''s resource');
SELECT pg_temp.expect(pg_temp.run($$('GR1','T1',1,1,true,'u1'),('GR2','T1',2,1,true,'u1'),('GR3','T1',3,1,true,'u1')$$,
                                  $$('GR1',1,'R1'),('GR2',1,'R2'),('GR3',1,'R3')$$),
                      'T1/G1:R1 T1/G1:R2 T1/G2:R1 T1/G2:R2 (4 placed)', 'HISTORICAL: three groups: all but the last');
SELECT pg_temp.expect(pg_temp.run($$('GR1','T1',1,1,true,'u1'),('GR2','T1',2,1,true,'u1')$$, $$('GR1',1,'R1'),('GR1',1,'R2'),('GR2',1,'R3')$$),
                      'T1/G1:R1 T1/G2:R2 (4 placed)', 'same priority: the least used resource');
SELECT pg_temp.expect(pg_temp.run($$('GR1','T1',1,1,true,'u1'),('GR2','T1',2,1,true,'u1')$$, $$('GR1',2,'R1'),('GR1',1,'R2'),('GR2',1,'R3')$$),
                      'T1/G1:R2 T1/G2:R2 (4 placed)', 'the resource priority comes before the use');
SELECT pg_temp.expect(pg_temp.run($$('GR1','T1',1,1,true,'u1'),('GR2','T1',2,2,true,'u1'),('GR3','T1',3,1,true,'u1')$$,
                                  $$('GR1',1,'R1'),('GR1',2,'R3'),('GR2',1,'R2'),('GR2',2,'R4'),('GR3',1,'R5')$$),
                      'T1/G1:R1 T1/G1:R2 T1/G2:R1 T1/G2:R2 (4 placed)', 'HISTORICAL: every group takes the first group''s "needed" (1)');
SELECT pg_temp.expect(pg_temp.run($$('GA','T2',1,2,true,'u1'),('GB','T2',2,1,true,'u1')$$, $$('GA',1,'R1'),('GA',1,'R2'),('GB',1,'R3')$$),
                      'T2/G1:R1 T2/G1:R2 T2/G2:R1 T2/G2:R2 (4 placed)', 'the first group''s "needed" (2), second template');
SELECT pg_temp.expect(pg_temp.run($$('GR1','T1',2,1,true,'u1'),('GR2','T1',1,1,true,'u1')$$, $$('GR1',1,'R1'),('GR2',1,'R2')$$),
                      'T1/G1:R1 T1/G2:R1 (4 placed)', 'HISTORICAL: the group priority is not read (entry order)');
SELECT pg_temp.expect(pg_temp.run($$('GR1','T1',1,1,false,'u1'),('GR2','T1',2,1,false,'u1')$$, $$('GR1',1,'R1'),('GR2',1,'R2')$$),
                      'T1/G1:R1 T1/G2:R1 (4 placed)', 'non-essential groups give the same resources');
SELECT pg_temp.expect(pg_temp.run($$('GR1','T1',1,2,true,'u1'),('GR2','T1',2,1,true,'u1')$$, $$('GR1',1,'R1'),('GR2',1,'R2')$$),
                      '- (2 placed)', 'an essential group (not the last) needing more than it has blocks the template');
SELECT pg_temp.expect(pg_temp.run($$('GR1','T1',1,2,false,'u1'),('GR2','T1',2,1,true,'u1')$$, $$('GR1',1,'R1'),('GR2',1,'R2')$$),
                      'T1/G1:R1 T1/G2:R1 (4 placed)', 'the same group, not essential, does not');
SELECT pg_temp.expect(pg_temp.run($$('GR1','T1',1,1,true,'u1'),('GR2','T1',2,2,true,'u1')$$, $$('GR1',1,'R1'),('GR2',1,'R2')$$),
                      'T1/G1:R1 T1/G2:R1 (4 placed)', 'HISTORICAL: the last group is not checked either');
SELECT pg_temp.expect(pg_temp.run($$('GR1','T1',1,1,true,'u1'),('GR2','T1',2,1,true,'u1')$$, $$('GR1',1,'R1'),('GR2',1,'R2')$$, 'R1'),
                      'ERROR 23505 kept 0', 'HISTORICAL: the chosen resource restricted at the cell fails the run');
SELECT pg_temp.expect(pg_temp.run($$('GR1','T1',1,1,true,'u1'),('GR2','T1',2,1,true,'u1')$$, $$('GR1',1,'R1'),('GR2',1,'R2')$$, 'R2'),
                      'T1/G1:R1 T1/G2:R1 (4 placed)', 'HISTORICAL: a restriction of a resource never chosen has no effect');
-- the usage rows book the resources (PAR-059): one occupancy row per placement here
SELECT pg_temp.expect(pg_temp.run($$('GR1','T1',1,1,true,'u1'),('GR2','T1',2,1,true,'u1')$$, $$('GR1',1,'R1'),('GR2',1,'R2')$$),
                      'T1/G1:R1 T1/G2:R1 (4 placed)', 'baseline again');
SELECT tst.check((SELECT string_agg(dia || '.' || turno, ' ' ORDER BY dia, turno) FROM restriccion_de_otro_recurso WHERE por_actividad AND otro_recurso = 'R1') = '1.1 1.3',
                 'occupancy rows at the cells of the placements');
SELECT tst.check((SELECT bool_and(u.creador = a.creador) FROM otros_recursos_usados u JOIN actividad a ON a.id = u.actividad), 'usage rows carry the activity creador');
