DROP table IF EXISTS actividad CASCADE;
CREATE TABLE actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'actividad', 'id', 'actividad'),
    plantilla VARCHAR NOT NULL REFERENCES plantilla_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    grupo VARCHAR NOT NULL REFERENCES grupo(id) ON DELETE CASCADE ON UPDATE CASCADE, 

    -- el profesor y el lugar no son obligatorios a no ser que la plantilla lo exija
    
    profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    lugar VARCHAR  REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 

    CHECK ((plantilla_exige_profesor(plantilla) = TRUE AND profesor IS NOT NULL) OR plantilla_exige_profesor(plantilla) = FALSE),
    CHECK ((plantilla_exige_lugar(plantilla) = TRUE AND lugar IS NOT NULL) OR plantilla_exige_lugar(plantilla) = FALSE),
    
    dia INTEGER,
    turno INTEGER,
    momento_de_colocacion DOUBLE PRECISION,
    unique (plantilla,grupo),
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);