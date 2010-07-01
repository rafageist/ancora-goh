DROP table IF EXISTS excepcion_de_hrt CASCADE;
CREATE TABLE excepcion_de_hrt(
    herencia VARCHAR NOT NULL REFERENCES herencia_de_restricciones(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER NOT NULL,
    turno INTEGER NOT NULL,
    CHECK (dia > 0),
    CHECK (turno > 0),
    PRIMARY KEY (herencia,periodo,dia,turno)
);