DROP table IF EXISTS restriccion_de_profesor CASCADE;
CREATE TABLE restriccion_de_profesor(
    id VARCHAR NOT NULL PRIMARY KEY,
    profesor VARCHAR NOT NULL REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    por_actividad BOOLEAN default FALSE,
    actividad VARCHAR REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    unique (profesor,periodo,dia,turno),
    CHECK (valor > 0)
);