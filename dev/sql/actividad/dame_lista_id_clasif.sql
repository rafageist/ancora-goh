
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_clasif
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de la clasificaciones
--     EJEMPLO: SELECT  dame_lista_id_clasif();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_clasif() RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM clasificacion_de_actividad ORDER BY prioridad LOOP
            arr := array_append(arr, r.id);
        END LOOP;
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';