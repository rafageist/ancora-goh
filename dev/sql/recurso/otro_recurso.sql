DROP table IF EXISTS otro_recurso CASCADE;
CREATE TABLE otro_recurso(
    id VARCHAR NOT NULL PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'otro_recurso', 'id', 'otro_recurso'),
    nombre VARCHAR,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
