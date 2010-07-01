DROP table IF EXISTS profesor_para_actividad CASCADE;
CREATE TABLE profesor_para_actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'profesor_para_actividad', 'id', 'profesor_para_actividad'),
    plantilla VARCHAR NOT NULL REFERENCES plantilla_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    profesor VARCHAR NOT NULL REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    prioridad INTEGER NOT NULL default 1,
    unique (plantilla, profesor)
);