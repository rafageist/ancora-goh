CREATE OR REPLACE FUNCTION dame_periodo_plantilla_de_actividad(VARCHAR) RETURNS VARCHAR as
$$
    SELECT dame_periodo_desglose(desglose) FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';