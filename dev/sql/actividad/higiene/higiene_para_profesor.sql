DROP table IF EXISTS higiene_para_profesor CASCADE;
CREATE TABLE higiene_para_profesor(
    periodo VARCHAR NOT NULL REFERENCES periodo(id)  ON DELETE CASCADE ON UPDATE CASCADE, 
    profesor VARCHAR NOT NULL REFERENCES profesor(id)  ON DELETE CASCADE ON UPDATE CASCADE, 
    gasto_maximo_diario INTEGER,
    PRIMARY KEY (periodo, profesor, gasto_maximo_diario)
);
