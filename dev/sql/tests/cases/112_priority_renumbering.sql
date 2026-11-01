-- expect: pass
-- refs: disparadores/arregla_prioridades_clasif_after_delete.sql, actividad/arregla_prioridades_clasif.sql,
--       actividad/sube_prioridad_clasif.sql, disparadores/nivela_prioridad_lxact.sql,
--       disparadores/nivela_prioridad_rxact_after_delete.sql; legacy-parity-inventory.md PAR-020, PAR-055, PAR-058
-- Priority renumbering:
-- * deleting a classification renumbers its creator's classifications 1..n;
--   moving one up swaps it with the previous one of the same creator;
-- * HISTORICAL: a new classification gets the default priority 1 even if
--   another classification of the creator already has priority 1;
-- * deleting a place assignment closes the gap in that template's priorities;
-- * HISTORICAL: deleting a resource group closes the gap only if NO resource
--   group of ANY template still has the vacated priority (the lookup is not
--   filtered by template).
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena)
     VALUES ('u2', 'Other planner', '-', 'other@example.org', 'other', 'not-a-real-hash');
INSERT INTO clasificacion_de_actividad (id, descripcion, creador, prioridad) VALUES ('C2', 'Lab', 'u1', 2), ('C3', 'Seminar', 'u1', 3), ('CQ', 'Other', 'u2', 1);
DELETE FROM clasificacion_de_actividad WHERE id = 'C2';
SELECT tst.check((SELECT string_agg(id || '=' || prioridad, ' ' ORDER BY creador, prioridad) FROM clasificacion_de_actividad) = 'C1=1 C3=2 CQ=1',
                 'delete renumbers the creator''s classifications only');
SELECT sube_prioridad_clasif('C3');
SELECT tst.check((SELECT string_agg(id || '=' || prioridad, ' ' ORDER BY creador, prioridad) FROM clasificacion_de_actividad) = 'C3=1 C1=2 CQ=1',
                 'move up swaps within the creator');
INSERT INTO clasificacion_de_actividad (id, descripcion, creador) VALUES ('C9', 'New', 'u1');
SELECT tst.check((SELECT prioridad FROM clasificacion_de_actividad WHERE id = 'C9') = 1, 'HISTORICAL: new classification takes priority 1 (duplicate)');
-- place assignments: T1 has L1 (1), add L2 (2); delete L1
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad) VALUES ('T1', 'L2', 2);
DELETE FROM lugar_para_actividad WHERE plantilla = 'T1' AND lugar = 'L1';
SELECT tst.check((SELECT prioridad FROM lugar_para_actividad WHERE plantilla = 'T1' AND lugar = 'L2') = 1, 'place priority gap closed within the template');
-- resource groups: T1 has 1, 2, 3; T2 has 1. Delete T1's priority 1.
INSERT INTO otro_grupo_de_recursos_para_actividad (id, plantilla, prioridad, creador) VALUES ('R1', 'T1', 1, 'u1'), ('R2', 'T1', 2, 'u1'), ('R3', 'T1', 3, 'u1'), ('S1', 'T2', 1, 'u1');
SELECT tst.check((SELECT necesarios = 1 AND imprescindibles FROM otro_grupo_de_recursos_para_actividad WHERE id = 'R2'), 'resource group defaults: 1 needed, essential');
DELETE FROM otro_grupo_de_recursos_para_actividad WHERE id = 'R1';
SELECT tst.check((SELECT string_agg(id || '=' || prioridad, ' ' ORDER BY id) FROM otro_grupo_de_recursos_para_actividad) = 'R2=2 R3=3 S1=1',
                 'HISTORICAL: no relevel because another template still has priority 1');
DELETE FROM otro_grupo_de_recursos_para_actividad WHERE id = 'S1';
SELECT tst.check((SELECT string_agg(id || '=' || prioridad, ' ' ORDER BY id) FROM otro_grupo_de_recursos_para_actividad) = 'R2=2 R3=3',
                 'deleting S1 releveled nothing in T2 (now empty) and left T1 unchanged');
