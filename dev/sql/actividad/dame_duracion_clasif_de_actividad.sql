CREATE OR REPLACE FUNCTION dame_duracion_clasif_de_actividad(VARCHAR) RETURNS INTEGER as
$$
    SELECT duracion FROM clasificacion_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';