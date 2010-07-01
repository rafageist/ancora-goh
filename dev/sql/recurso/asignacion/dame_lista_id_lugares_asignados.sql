-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_lugares_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los LUGARES asignados a una plantilla de actividad
--     EJEMPLO: SELECT  dame_lista_id_lugares_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_lugares_asignados(idplantilla VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM lugar_para_actividad WHERE plantilla = idplantilla LOOP
            arr := array_append(arr,r.lugar);
        END LOOP;

        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';