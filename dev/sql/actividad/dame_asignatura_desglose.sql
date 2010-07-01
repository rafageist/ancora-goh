CREATE OR REPLACE FUNCTION dame_asignatura_desglose(VARCHAR) RETURNS VARCHAR as
$$
    SELECT asignatura FROM desglose WHERE id = $1;
$$
LANGUAGE 'sql';