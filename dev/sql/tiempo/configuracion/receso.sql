DROP table IF EXISTS receso CASCADE;
CREATE TABLE receso(
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE,
    despues_del_turno INTEGER,
    duracion DOUBLE PRECISION,
    PRIMARY KEY (periodo, despues_del_turno)
);
