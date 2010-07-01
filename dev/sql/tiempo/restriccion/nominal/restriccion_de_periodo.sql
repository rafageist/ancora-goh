DROP table IF EXISTS restriccion_de_periodo CASCADE;
CREATE TABLE restriccion_de_periodo(
    id VARCHAR NOT NULL PRIMARY KEY,
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    UNIQUE (periodo,dia,turno),
    CHECK (valor > 0)
);