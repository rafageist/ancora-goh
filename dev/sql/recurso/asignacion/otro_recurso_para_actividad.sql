DROP table IF EXISTS otro_recurso_para_actividad CASCADE;
CREATE TABLE otro_recurso_para_actividad(
    grupo_de_recursos VARCHAR NOT NULL REFERENCES otro_grupo_de_recursos_para_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    prioridad INTEGER NOT NULL default 1,
    otro_recurso VARCHAR NOT NULL REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (grupo_de_recursos,otro_recurso)
);