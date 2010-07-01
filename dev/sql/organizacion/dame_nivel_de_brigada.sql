CREATE OR REPLACE FUNCTION dame_nivel_de_brigada (idbrigada VARCHAR) RETURNS VARCHAR AS
$$
    SELECT nivel FROM brigada WHERE id = $1;
$$
LANGUAGE 'sql';