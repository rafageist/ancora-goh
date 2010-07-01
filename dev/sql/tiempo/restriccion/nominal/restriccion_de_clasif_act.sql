DROP table IF EXISTS restriccion_de_clasif_act CASCADE;
CREATE TABLE restriccion_de_clasif_act(
    id VARCHAR NOT NULL PRIMARY KEY,
    clasificacion VARCHAR NOT NULL REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    unique (clasificacion,periodo,dia,turno),
    CHECK (valor > 0)
);