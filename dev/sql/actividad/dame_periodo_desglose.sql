CREATE OR REPLACE FUNCTION dame_periodo_desglose(VARCHAR) RETURNS VARCHAR as
$$
    SELECT periodo FROM desglose WHERE id = $1;
$$
LANGUAGE 'sql';