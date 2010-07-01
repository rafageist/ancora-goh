
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_otros_recursos_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los otros de recursos asignados 
--              de un grupo de recursos dado
--     EJEMPLO: SELECT  dame_lista_id_otros_recursos_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_otros_recursos_asignados(idgrupo VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM otro_recurso_para_actividad WHERE grupo_de_recursos = idgrupo LOOP
            arr := array_append(arr,r.otro_recurso);
        END LOOP;

        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';