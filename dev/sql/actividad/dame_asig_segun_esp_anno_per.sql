
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_asig_segun_esp_anno_per
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de asignaturas de la esp y anno dados
--        ordebados de mayor a menor por la cantidad de actividades en un per?o dado
--     EJEMPLO: SELECT  dame_asig_segun_esp_anno_per('info','1','sp');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_asig_segun_esp_anno_per(idesp VARCHAR, idnivel VARCHAR, idperiodo VARCHAR) RETURNS VARCHAR[] as
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM asignatura WHERE especialidad = idesp AND nivel = idnivel ORDER BY dame_cant_act(asignatura.id, idperiodo) DESC LOOP
            arr := array_append(arr, r.id);
        END LOOP;
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';