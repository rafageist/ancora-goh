DROP table IF EXISTS restriccion_de_brigada CASCADE;
CREATE TABLE restriccion_de_brigada(
    id VARCHAR NOT NULL PRIMARY KEY,
    brigada VARCHAR NOT NULL REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    por_actividad BOOLEAN default FALSE,
    actividad VARCHAR REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    unique(brigada,periodo,dia,turno),
    CHECK (valor > 0)
);