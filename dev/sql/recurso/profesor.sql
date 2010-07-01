DROP table IF EXISTS profesor CASCADE;
CREATE TABLE profesor(
    id VARCHAR NOT NULL PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'profesor', 'id', 'profesor'),
    nombre VARCHAR,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
