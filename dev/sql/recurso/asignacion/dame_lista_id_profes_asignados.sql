
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_profes_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los PROFESORES asignados a una plantilla de actividad
--     EJEMPLO: SELECT  dame_lista_id_profes_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_profes_asignados(idplantilla VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM profesor_para_actividad WHERE plantilla = idplantilla LOOP
            arr := array_append(arr,r.profesor);
        END LOOP;

        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';