-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_clasif
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de clasificaciones
--     EJEMPLO: SELECT  dame_cant_clasif();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_clasif() RETURNS BIGINT AS
$$
    SELECT count(id) AS cant FROM clasificacion_de_actividad;
$$
LANGUAGE 'sql';