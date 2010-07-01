-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_profes_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de profesores asignados a una actividad
--     EJEMPLO: SELECT  dame_cant_profes_asignados('act1');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_profes_asignados(idplantilla VARCHAR) RETURNS BIGINT AS
$$
    SELECT count(id) FROM profesor_para_actividad WHERE plantilla = $1;
$$
LANGUAGE 'sql';