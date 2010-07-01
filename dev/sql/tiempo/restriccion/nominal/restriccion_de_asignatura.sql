DROP table IF EXISTS restriccion_de_asignatura CASCADE;
CREATE TABLE restriccion_de_asignatura(
    id VARCHAR NOT NULL PRIMARY KEY,
    asignatura VARCHAR NOT NULL REFERENCES asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    unique (asignatura,periodo,dia,turno),
    CHECK (valor > 0)
);