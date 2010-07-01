    -- duracion de una actividad 
    CREATE or replace function dame_duracion_actividad(id VARCHAR) RETURNS INTEGER as
    $$
        SELECT dame_duracion_plantilla_de_actividad(plantilla) from actividad where id =$1
    $$
    LANGUAGE 'sql';