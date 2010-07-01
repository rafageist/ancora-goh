
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_act
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de actividades de una asignatura en un per?o dado
--     EJEMPLO: SELECT dame_cant_act('mat','si');
-- -----------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION dame_cant_act(asig VARCHAR, periodo VARCHAR) RETURNS BIGINT as
$$
    SELECT count(plantilla_de_actividad.id)
    FROM asignatura 
    INNER JOIN desglose 
        ON asignatura.id = desglose.asignatura
    INNER JOIN plantilla_de_actividad 
        ON plantilla_de_actividad.desglose = desglose.id
    WHERE asignatura.id = $1 AND desglose.periodo = $2;
$$
LANGUAGE 'sql';