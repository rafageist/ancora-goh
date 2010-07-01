CREATE OR REPLACE FUNCTION dame_cant_otros_recursos_asignados(idgrupo VARCHAR) RETURNS BIGINT AS
$$
    SELECT count(otro_recurso) FROM otro_recurso_para_actividad WHERE grupo_de_recursos = $1;
$$
LANGUAGE 'sql';