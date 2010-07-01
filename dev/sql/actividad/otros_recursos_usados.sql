DROP table IF EXISTS otros_recursos_usados CASCADE;
CREATE TABLE otros_recursos_usados(
    otro_recurso VARCHAR NOT NULL REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE,
    actividad VARCHAR NOT NULL REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (otro_recurso, actividad)
);
