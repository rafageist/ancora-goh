-- Two essential other-resource groups for T1 (GR1 = {R1}, GR2 = {R2}, one needed each),
-- both resources restricted at day 1, slot 1. Load after base.sql.
INSERT INTO otro_recurso (id, nombre, creador) VALUES ('R1', 'Projector 1', 'u1'), ('R2', 'Projector 2', 'u1');
INSERT INTO otro_grupo_de_recursos_para_actividad (id, plantilla, prioridad, necesarios, imprescindibles, creador)
     VALUES ('GR1', 'T1', 1, 1, true, 'u1'), ('GR2', 'T1', 2, 1, true, 'u1');
INSERT INTO otro_recurso_para_actividad (grupo_de_recursos, prioridad, otro_recurso) VALUES ('GR1', 1, 'R1'), ('GR2', 1, 'R2');
INSERT INTO restriccion_de_otro_recurso (id, otro_recurso, periodo, dia, turno, valor, por_actividad)
     VALUES ('RO1', 'R1', 'PER', 1, 1, 1, false), ('RO2', 'R2', 'PER', 1, 1, 1, false);
