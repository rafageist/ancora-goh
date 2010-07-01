DROP table IF EXISTS otro_grupo_de_recursos_para_actividad CASCADE;
CREATE TABLE otro_grupo_de_recursos_para_actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'otro_grupo_de_recursos_para_actividad', 'id', 'otro_grupo_de_recursos_para_actividad'),
    plantilla VARCHAR NOT NULL REFERENCES plantilla_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    prioridad INTEGER NOT NULL default 1,
    necesarios INTEGER NOT NULL default 1,
    imprescindibles BOOLEAN NOT NULL default TRUE,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
