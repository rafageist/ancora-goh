CREATE OR REPLACE FUNCTION dame_cant_accesos_prohibidos_al_grupo(VARCHAR) RETURNS BIGINT AS
$$
    SELECT count(lugar) FROM acceso_prohibido WHERE grupo = $1;
$$
LANGUAGE 'sql';
