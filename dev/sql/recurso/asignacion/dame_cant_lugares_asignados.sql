CREATE OR REPLACE FUNCTION dame_cant_lugares_asignados(idplantilla VARCHAR) RETURNS BIGINT AS
$$
    SELECT count(lugar) FROM lugar_para_actividad WHERE plantilla = $1;
$$
LANGUAGE 'sql';