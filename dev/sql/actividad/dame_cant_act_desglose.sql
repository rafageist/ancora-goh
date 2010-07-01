-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_act_desglose
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de actividades de un desglose
--     EJEMPLO: SELECT dame_cant_act_desglose('iddesglose');
-- -----------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION dame_cant_act_desglose(desglose varchar) RETURNS BIGINT as
$$
    SELECT count(plantilla_de_actividad.id)
    FROM desglose 
    INNER JOIN plantilla_de_actividad 
        ON plantilla_de_actividad.desglose = desglose.id
    WHERE desglose.id = $1;
$$
LANGUAGE 'sql';