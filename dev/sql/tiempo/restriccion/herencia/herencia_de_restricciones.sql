DROP table IF EXISTS herencia_de_restricciones CASCADE;
CREATE TABLE herencia_de_restricciones(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'herencia_de_restricciones', 'id', 'herencia_de_restricciones'),
    planificacion VARCHAR REFERENCES planificacion(id)  ON DELETE CASCADE ON UPDATE CASCADE, 
    activa BOOLEAN default TRUE,
        
    -- los foreign keys no llevan NOT NULL pues es uno de ellos el que debe llevarlo
    hijo_brigada VARCHAR REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    hijo_profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    hijo_lugar VARCHAR REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    hijo_otro_recurso VARCHAR REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 

    padre_brigada VARCHAR REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    padre_profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    padre_lugar VARCHAR REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    padre_otro_recurso VARCHAR REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 

    CHECK (check_hrt(hijo_brigada, hijo_profesor, hijo_lugar, hijo_otro_recurso, padre_brigada, padre_profesor, padre_lugar, padre_otro_recurso) = TRUE),
    unique (hijo_brigada, hijo_profesor, hijo_lugar, hijo_otro_recurso, padre_brigada, padre_profesor, padre_lugar, padre_otro_recurso)
);