DROP table IF EXISTS clasificacion_de_actividad CASCADE;
CREATE TABLE clasificacion_de_actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'clasificacion_de_actividad', 'id', 'clasificacion_de_actividad'),
    descripcion VARCHAR NOT NULL,
    duracion INTEGER default 1,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE,
    prioridad INTEGER NOT NULL default 1,
    gasto INTEGER default 1
);