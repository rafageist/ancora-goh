-- expect: pass
-- refs: organizacion/mueve_o_crea_brigada_de_grupo.sql; organizacion/grupo.sql; legacy-parity-inventory.md PAR-016, PAR-018
-- Brigade (cohort) membership through mueve_o_crea_brigada_de_grupo(brigade, group):
-- * a brigade belongs to at most one group per (specialty, level,
--   classification, plan): adding it to another such group MOVES it;
-- * a group of another plan is a separate slot: the brigade is added, not moved;
-- * nothing changes, and FALSE is returned, when the target group or the
--   group the brigade would leave already has placed activities;
-- * grupo.id has no default: the application must supply it.
\ir ../fixtures/base.sql
INSERT INTO planificacion (id, descripcion, fecha_inicial, cant_dias, creador) VALUES ('P2', 'Plan 2', 100, 30, 'u1');
INSERT INTO grupo (id, descripcion, clasificacion, especialidad, planificacion, nivel) VALUES ('G3', 'Group 3', 'C1', 'E', 'P', 'N'), ('G4', 'Group 4', 'C1', 'E', 'P2', 'N');
CREATE TEMP VIEW m AS SELECT string_agg(brigada || '>' || grupo, ' ' ORDER BY brigada, grupo) AS s FROM brigada_de_grupo;
SELECT tst.check(mueve_o_crea_brigada_de_grupo('B1', 'G3'), 'move accepted');
SELECT tst.check((SELECT s FROM m) = 'B1>G3 B2>G2', 'B1 moved from G1 to G3 (same plan, specialty, level, classification)');
SELECT tst.check(mueve_o_crea_brigada_de_grupo('B1', 'G4'), 'add to other plan accepted');
SELECT tst.check((SELECT s FROM m) = 'B1>G3 B1>G4 B2>G2', 'group of another plan: added, not moved');
INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES ('X1', 'T1', 'G3', 'TA', 'L1', 1, 1, 'u1');
SELECT tst.check(NOT mueve_o_crea_brigada_de_grupo('B1', 'G1'), 'refused: the group it would leave has activities');
SELECT tst.check(NOT mueve_o_crea_brigada_de_grupo('B2', 'G3'), 'refused: the target group has activities');
SELECT tst.check((SELECT s FROM m) = 'B1>G3 B1>G4 B2>G2', 'membership unchanged after refusals');
DO $$ BEGIN
  INSERT INTO grupo (descripcion, clasificacion, especialidad, planificacion, nivel) VALUES ('No id', 'C1', 'E', 'P', 'N');
  RAISE EXCEPTION 'grupo without id was accepted';
EXCEPTION WHEN not_null_violation THEN NULL;
END $$;
