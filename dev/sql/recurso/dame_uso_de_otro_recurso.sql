-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_uso_de_otro_recurso
--        TIPO: funcion
-- DESCRIPCION: Uso de un recurso dado
--     EJEMPLO: SELECT  dame_uso_de_otro_recurso('pc');
-- -----------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION dame_uso_de_otro_recurso(id VARCHAR) RETURNS BIGINT as
$$
    SELECT count(actividad) FROM otros_recursos_usados WHERE otro_recurso = $1
$$
LANGUAGE 'sql';