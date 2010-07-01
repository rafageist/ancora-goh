DROP table IF EXISTS lugar_para_actividad CASCADE;
CREATE TABLE lugar_para_actividad(
    plantilla VARCHAR NOT NULL REFERENCES plantilla_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    lugar VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    prioridad INTEGER NOT NULL default 1,
    actualizacion_interna BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (plantilla,lugar)
);
