DROP table IF EXISTS nivel CASCADE;
CREATE TABLE nivel(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'nivel', 'id', 'nivel'),
    descripcion VARCHAR NOT NULL,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
