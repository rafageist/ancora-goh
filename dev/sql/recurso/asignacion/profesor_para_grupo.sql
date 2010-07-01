DROP table IF EXISTS profesor_para_grupo CASCADE;
CREATE TABLE profesor_para_grupo(
    profesor_para_actividad VARCHAR NOT NULL REFERENCES profesor_para_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    grupo VARCHAR NOT NULL REFERENCES grupo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    PRIMARY KEY (profesor_para_actividad,grupo)
);
