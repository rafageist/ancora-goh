CREATE OR REPLACE FUNCTION dame_nivel_de_asignatura (idasig VARCHAR) RETURNS VARCHAR AS
$$
    SELECT nivel FROM asignatura WHERE id = $1;
$$
LANGUAGE 'sql';