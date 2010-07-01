-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_turnos
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de turnos de un periodo
--     EJEMPLO: SELECT dame_cant_turnos('si');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_turnos(idperiodo VARCHAR) RETURNS INTEGER AS
$$
    SELECT cant_turnos FROM periodo WHERE id = $1;
$$
LANGUAGE 'sql';