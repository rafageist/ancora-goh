-- Second template T2 (order 2) of subject A, with teacher TB and room L2.
-- With base.sql this gives 4 demands: {T1,T2} x {G1,G2}. Load after base.sql.
INSERT INTO plantilla_de_actividad (id, desglose, orden, clasificacion)
     SELECT 'T2', id, 2, 'C1' FROM desglose WHERE asignatura = 'A' AND periodo = 'PER';
INSERT INTO profesor_para_actividad (id, plantilla, profesor, prioridad) VALUES ('PPA2', 'T2', 'TB', 1);
INSERT INTO profesor_para_grupo (profesor_para_actividad, grupo) VALUES ('PPA2', 'G1'), ('PPA2', 'G2');
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad) VALUES ('T2', 'L2', 1);
