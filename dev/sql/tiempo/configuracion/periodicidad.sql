DROP table IF EXISTS periodicidad CASCADE;
CREATE TABLE periodicidad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'periodicidad', 'id', 'periodicidad'),
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE,
    dia INTEGER,
    UNIQUE (periodo,dia),
    CHECK (dia > 0)
);