DROP table IF EXISTS especialidad CASCADE;
CREATE TABLE especialidad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'especialidad', 'id', 'especialidad'),
    descripcion VARCHAR NOT NULL,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
