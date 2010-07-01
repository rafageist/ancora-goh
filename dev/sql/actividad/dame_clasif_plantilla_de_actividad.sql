CREATE OR REPLACE FUNCTION dame_clasif_plantilla_de_actividad(VARCHAR) RETURNS VARCHAR as
$$
    SELECT clasificacion FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';