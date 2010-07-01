DROP table IF EXISTS zpriori CASCADE;
CREATE TABLE zpriori(
    clasificacion VARCHAR NOT NULL REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    PRIMARY KEY (clasificacion,periodo,dia,turno),
    CHECK (valor > 0)
);
