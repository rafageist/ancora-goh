DROP table IF EXISTS restriccion_de_especialidad CASCADE;
CREATE TABLE restriccion_de_especialidad(
    id VARCHAR NOT NULL PRIMARY KEY,
    especialidad VARCHAR NOT NULL REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    unique (especialidad,periodo,dia,turno),
    CHECK (valor > 0)
);