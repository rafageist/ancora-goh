DROP table IF EXISTS restriccion_de_otro_recurso CASCADE;
CREATE TABLE restriccion_de_otro_recurso(
    id VARCHAR NOT NULL PRIMARY KEY,
    otro_recurso VARCHAR NOT NULL REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    por_actividad BOOLEAN default FALSE,
    actividad VARCHAR REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    unique (otro_recurso,periodo,dia,turno),
    CHECK (valor > 0)
);