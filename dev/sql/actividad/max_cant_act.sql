-- -----------------------------------------------------------------------------------
--      NOMBRE: max_cant_act
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad m?ma de actividades las asignaturas
--              de una especialidad en un per?o espec?co
--     EJEMPLO: SELECT max_cant_act('info','si');
-- -----------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION max_cant_act(especialidad VARCHAR, nivel VARCHAR, periodo VARCHAR) RETURNS BIGINT as
$$
    SELECT max(cantidad)
    FROM asignaturas_cant_act 
    WHERE especialidad = $1 AND nivel = $2 AND periodo = $3;
$$
LANGUAGE 'sql';