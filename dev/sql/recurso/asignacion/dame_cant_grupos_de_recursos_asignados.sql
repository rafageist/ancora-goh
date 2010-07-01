CREATE OR REPLACE FUNCTION dame_cant_grupos_de_recursos_asignados(idplantilla VARCHAR) RETURNS BIGINT AS
$$
    SELECT count(id) FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = $1;
$$
LANGUAGE 'sql';