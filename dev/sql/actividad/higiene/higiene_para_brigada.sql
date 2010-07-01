DROP table IF EXISTS higiene_para_brigada CASCADE;
CREATE TABLE higiene_para_brigada(
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    brigada VARCHAR NOT NULL REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    gasto_maximo_diario INTEGER,
    PRIMARY KEY(periodo, brigada, gasto_maximo_diario)
);