CREATE OR REPLACE FUNCTION dame_clasificacion_de_grupo (idgrupo VARCHAR) RETURNS VARCHAR AS
$$
    SELECT clasificacion FROM grupo WHERE id = $1;
$$
LANGUAGE 'sql';