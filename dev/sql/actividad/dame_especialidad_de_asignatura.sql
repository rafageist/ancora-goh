
CREATE OR REPLACE FUNCTION dame_especialidad_de_asignatura (idasig VARCHAR) RETURNS VARCHAR AS
$$
    SELECT especialidad FROM asignatura WHERE id = $1;
$$
LANGUAGE 'sql';