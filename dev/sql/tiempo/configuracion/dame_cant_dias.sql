
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_dias
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de dias de un periodo
--     EJEMPLO: SELECT dame_cant_dias('si');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_dias(idperiodo VARCHAR) RETURNS INTEGER AS
$$
    SELECT cant_dias FROM periodo WHERE id = $1;
$$
LANGUAGE 'sql';