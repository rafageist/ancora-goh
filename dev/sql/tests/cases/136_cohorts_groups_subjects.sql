-- expect: pass
-- refs: organizacion/brigada.sql, grupo.sql, brigada_de_grupo.sql, especialidad.sql, nivel.sql;
--       actividad/asignatura.sql; organizacion/dame_grupos.sql:6, dame_brigadas_sin_grupo.sql,
--       mueve_o_crea_brigada_de_grupo.sql; actividad/dame_asig_segun_esp_anno_per.sql:14;
--       control/custom/custom_EliminaBrigadaDeGrupo.php (removeBy);
--       legacy-parity-inventory.md PAR-014..018, PAR-030, D6, D10, Q-14
-- Column rules of the organization tables, and what the engine does with them:
-- * brigada: matricula defaults to 0 and takes any integer (negative too);
--   especialidad and nivel are NOT NULL; descripcion is NOT NULL and has no
--   length limit (VARCHAR); especialidad and nivel have no other column;
-- * grupo: clasificacion, especialidad, planificacion and nivel NOT NULL;
-- * asignatura: especialidad is NULLABLE, nivel NOT NULL; a subject
--   without a specialty is in no scope (dame_asig_segun_esp_anno_per);
-- * nothing checks that a group's brigades share its specialty and level:
--   dame_grupos() selects groups by their BRIGADES' specialty and level, so
--   a brigade re-homed to another specialty (set_Brigada, Q-14) takes its
--   group into that specialty's run, and out of the group's own;
-- * dame_brigadas_sin_grupo(): the brigades of (specialty, level) with no
--   group of (classification, plan), the "no group" box of the browser;
-- * removing a brigade from a group (custom_EliminaBrigadaDeGrupo.php) has
--   no activity guard, unlike the move (case 113).
\ir ../fixtures/base.sql
-- brigada
INSERT INTO brigada (id, descripcion, especialidad, nivel, creador) VALUES ('B3', 'Cohort 3', 'E', 'N', 'u1');
SELECT tst.check((SELECT matricula FROM brigada WHERE id = 'B3') = 0, 'matricula defaults to 0');
INSERT INTO brigada (id, descripcion, especialidad, nivel, matricula, creador) VALUES ('B4', repeat('x', 600), 'E', 'N', -5, 'u1');
SELECT tst.check((SELECT matricula = -5 AND length(descripcion) = 600 FROM brigada WHERE id = 'B4'), 'a negative matricula and a 600-character description are stored');
DO $$ BEGIN
  INSERT INTO brigada (id, descripcion, especialidad, nivel, creador) VALUES ('B5', 'No specialty', NULL, 'N', 'u1');
  RAISE EXCEPTION 'brigada without specialty was accepted';
EXCEPTION WHEN not_null_violation THEN NULL;
END $$;
-- grupo
DO $$ BEGIN
  INSERT INTO grupo (id, descripcion, clasificacion, especialidad, planificacion, nivel) VALUES ('G9', 'No level', 'C1', 'E', 'P', NULL);
  RAISE EXCEPTION 'grupo without level was accepted';
EXCEPTION WHEN not_null_violation THEN NULL;
END $$;
-- asignatura: specialty nullable, level required
INSERT INTO asignatura (id, descripcion, especialidad, nivel, creador) VALUES ('A0', 'No specialty', NULL, 'N', 'u1');
SELECT tst.check((SELECT especialidad IS NULL FROM asignatura WHERE id = 'A0'), 'a subject without a specialty is stored');
SELECT tst.check(dame_asig_segun_esp_anno_per('E', 'N', 'PER') = ARRAY['A']::varchar[], 'a subject without a specialty is in no scope');
DO $$ BEGIN
  INSERT INTO asignatura (id, descripcion, especialidad, nivel, creador) VALUES ('A9', 'No level', 'E', NULL, 'u1');
  RAISE EXCEPTION 'asignatura without level was accepted';
EXCEPTION WHEN not_null_violation THEN NULL;
END $$;
-- the "no group" box
SELECT tst.check((SELECT string_agg(b, ' ' ORDER BY b) FROM dame_brigadas_sin_grupo('E', 'N', 'C1', 'P') b) = 'B3 B4', 'brigades of (E, N) without a group of (C1, P)');
-- group membership does not follow the group's specialty
INSERT INTO especialidad (id, descripcion, creador) VALUES ('F', 'Program F', 'u1');
SELECT tst.check(dame_grupos('E', 'N', 'C1') = ARRAY['G1', 'G2']::varchar[] OR dame_grupos('E', 'N', 'C1') = ARRAY['G2', 'G1']::varchar[], 'E run: groups G1 and G2');
UPDATE brigada SET especialidad = 'F' WHERE id = 'B1';
SELECT tst.check((SELECT count(*) FROM brigada_de_grupo WHERE brigada = 'B1' AND grupo = 'G1') = 1, 're-homing the brigade keeps its membership');
SELECT tst.check(dame_grupos('E', 'N', 'C1') = ARRAY['G2']::varchar[], 'HISTORICAL: group G1 (specialty E) leaves the E run with its brigade');
SELECT tst.check(dame_grupos('F', 'N', 'C1') = ARRAY['G1']::varchar[], 'HISTORICAL: group G1 (specialty E) is scheduled in the F run');
-- a brigade of another specialty can be moved into a group (no check)
SELECT tst.check(mueve_o_crea_brigada_de_grupo('B3', 'G1'), 'a brigade of E joins G1, whose other brigade is now F');
SELECT tst.check((SELECT string_agg(brigada, ' ' ORDER BY brigada) FROM brigada_de_grupo WHERE grupo = 'G1') = 'B1 B3', 'G1 holds brigades of two specialties');
-- removal has no activity guard
INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, creador) VALUES ('X1', 'T1', 'G2', 'TA', 'L1', 1, 1, 'u1');
DELETE FROM brigada_de_grupo WHERE brigada = 'B2' AND dame_planificacion_de_grupo(grupo) = 'P' AND dame_clasificacion_de_grupo(grupo) = 'C1'
   AND dame_nivel_de_grupo(grupo) = 'N' AND dame_especialidad_de_grupo(grupo) = 'E';
SELECT tst.check(NOT EXISTS (SELECT 1 FROM brigada_de_grupo WHERE brigada = 'B2') AND EXISTS (SELECT 1 FROM actividad WHERE grupo = 'G2'),
                 'the removal is not guarded: B2 leaves G2 although G2 has an activity, which stays');
SELECT tst.check(NOT mueve_o_crea_brigada_de_grupo('B2', 'G2'), 'but moving it back is refused: G2 has an activity');
