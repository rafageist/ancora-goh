DROP table IF EXISTS plantilla_de_actividad CASCADE;
CREATE TABLE plantilla_de_actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'plantilla_de_actividad', 'id', 'plantilla_de_actividad'),
    desglose VARCHAR NOT NULL REFERENCES desglose(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    orden INTEGER,
    clasificacion VARCHAR NOT NULL REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    unique (desglose,orden),
    exige_profesor BOOLEAN default TRUE,
    exige_lugar BOOLEAN default TRUE,
    actualizacion_interna BOOLEAN NOT NULL default FALSE
);