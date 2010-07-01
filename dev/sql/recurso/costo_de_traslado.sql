DROP table IF EXISTS costo_de_traslado CASCADE;
CREATE TABLE costo_de_traslado(
    origen VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    destino VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    costo BIGINT,
    PRIMARY KEY(origen,destino)
);
