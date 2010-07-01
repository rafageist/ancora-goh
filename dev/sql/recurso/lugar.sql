DROP table IF EXISTS lugar CASCADE;
CREATE TABLE lugar(
    id VARCHAR NOT NULL PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'lugar', 'id', 'lugar'),
    nombre VARCHAR,
    capacidad INTEGER default 0,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
