CREATE OR REPLACE FUNCTION dame_matricula(brg VARCHAR) RETURNS INTEGER AS
$$
    SELECT matricula FROM brigada WHERE id = $1;
$$
LANGUAGE 'sql';