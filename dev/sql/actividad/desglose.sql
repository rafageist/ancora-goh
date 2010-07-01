DROP table IF EXISTS desglose CASCADE;
CREATE TABLE desglose(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'desglose', 'id', 'desglose'),
    asignatura VARCHAR NOT NULL REFERENCES asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    min_dias INTEGER default 1,
    max_dias INTEGER default 5,
    respetar_orden BOOLEAN default TRUE,
    mismo_dia BOOLEAN default FALSE,
    unique(asignatura,periodo)
);