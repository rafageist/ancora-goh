DROP table IF EXISTS restriccion_de_lugar CASCADE;
CREATE TABLE restriccion_de_lugar(
    id VARCHAR NOT NULL PRIMARY KEY,
    lugar VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    por_actividad BOOLEAN default FALSE,
    actividad VARCHAR REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    unique (lugar,periodo,dia,turno),
    CHECK (valor > 0)
);
