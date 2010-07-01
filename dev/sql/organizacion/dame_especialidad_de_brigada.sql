CREATE OR REPLACE FUNCTION dame_especialidad_de_brigada (idbrigada VARCHAR) RETURNS VARCHAR AS
$$
    SELECT especialidad FROM brigada WHERE id = $1;
$$
LANGUAGE 'sql';