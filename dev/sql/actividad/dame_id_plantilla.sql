
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_id_plantilla
--        TIPO: funcion
-- DESCRIPCION: Devuelve el id de la plantilla de actividad segun asignatura, periodo y numero de orden
--     EJEMPLO: SELECT  dame_id_plantilla('act1');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_id_plantilla(idasig VARCHAR, idperiodo VARCHAR, orden BIGINT) RETURNS VARCHAR AS
$$
    SELECT plantilla_de_actividad.id FROM plantilla_de_actividad INNER JOIN desglose ON plantilla_de_actividad.desglose = desglose.id WHERE desglose.asignatura = $1 AND desglose.periodo = $2 AND plantilla_de_actividad.orden = $3;
$$
LANGUAGE 'sql';