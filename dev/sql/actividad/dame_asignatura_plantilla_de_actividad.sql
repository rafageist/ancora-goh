
CREATE OR REPLACE FUNCTION dame_asignatura_plantilla_de_actividad(id VARCHAR) RETURNS VARCHAR as
$$
    SELECT dame_asignatura_desglose(desglose) FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';