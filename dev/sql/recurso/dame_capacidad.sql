CREATE OR REPLACE FUNCTION dame_capacidad(lug VARCHAR) RETURNS INTEGER AS
$$
    SELECT capacidad FROM lugar WHERE id = $1;
$$
LANGUAGE 'sql';
