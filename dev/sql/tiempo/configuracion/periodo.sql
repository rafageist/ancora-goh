DROP table IF EXISTS periodo CASCADE;
CREATE TABLE periodo(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'periodo', 'id', 'periodo'),
    descripcion VARCHAR NOT NULL,
    cant_dias INTEGER default 5,
    cant_turnos INTEGER default 6,
    CHECK (cant_dias >0),
    CHECK (cant_turnos >0),
    hora_inicial DOUBLE PRECISION,
    duracion_del_turno DOUBLE PRECISION,
    planificacion VARCHAR NOT NULL REFERENCES planificacion(id) ON DELETE CASCADE ON UPDATE CASCADE
);
