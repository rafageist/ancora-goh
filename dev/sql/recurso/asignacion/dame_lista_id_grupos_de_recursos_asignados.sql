
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_grupos_de_recursos_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los grupos de recursos asignados a una plantilla de actividad
--     EJEMPLO: SELECT  dame_lista_id_grupos_de_recursos_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_grupos_de_recursos_asignados(idplantilla VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = idplantilla LOOP
            arr := array_append(arr,r.id);
        END LOOP;

        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';
