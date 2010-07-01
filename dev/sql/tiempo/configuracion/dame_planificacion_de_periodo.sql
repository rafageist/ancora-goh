CREATE OR REPLACE FUNCTION dame_planificacion_de_periodo(pid VARCHAR) RETURNS VARCHAR as
$$
    SELECT planificacion FROM periodo WHERE id = $1;
$$
LANGUAGE 'sql';
