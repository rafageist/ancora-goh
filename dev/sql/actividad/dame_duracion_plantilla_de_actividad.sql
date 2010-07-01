CREATE OR REPLACE FUNCTION dame_duracion_plantilla_de_actividad(VARCHAR) RETURNS INTEGER as
$$
    SELECT dame_duracion_clasif_de_actividad(clasificacion) FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';
