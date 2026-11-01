-- SQL equivalent of the data the HTTP harness enters through the web
-- application (dev/e2e/lib.sh, "data entry and listing"): teacher
-- "Teacher Beta", assigned to templates B1 and B2 for groups G1 and G2.
-- Load after dev/e2e/fixture.sql when a run does not go through HTTP (the
-- engine worker and its tests, #71). The placement digest uses teacher names,
-- not ids, so a run on this data must give dev/e2e/expected-placements.md5.
INSERT INTO profesor (id, nombre, creador) VALUES ('TBETA', 'Teacher Beta', 'admin-superuser');
INSERT INTO profesor_para_actividad (id, plantilla, profesor, prioridad)
     VALUES ('PPA3', 'B1', 'TBETA', 1), ('PPA4', 'B2', 'TBETA', 1);
INSERT INTO profesor_para_grupo (profesor_para_actividad, grupo)
     VALUES ('PPA3', 'G1'), ('PPA3', 'G2'), ('PPA4', 'G1'), ('PPA4', 'G2');
