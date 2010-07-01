CREATE or replace function dame_gasto_de_plantilla (idplantilla VARCHAR) RETURNS INTEGER as
$$
    SELECT gasto FROM clasificacion_de_actividad WHERE id = dame_clasif_plantilla_de_actividad($1);
$$
LANGUAGE 'sql';