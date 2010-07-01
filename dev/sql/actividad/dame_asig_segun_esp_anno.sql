-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_asig_segun_esp_anno
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de asignaturas de la esp y anno dados
--     EJEMPLO: SELECT  dame_asig_segun_esp_anno('info','1');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_asig_segun_esp_anno(idesp VARCHAR, idnivel VARCHAR) RETURNS VARCHAR[] as
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM asignatura WHERE especialidad = idesp AND nivel = idnivel LOOP
            arr := array_append(arr, r.id);
        END LOOP;
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';