
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_clasif_de_asignaturas
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de clasificaciones de actividades de una 
--        asignatura en un periodo dado
--     EJEMPLO: SELECT  dame_asig_segun_esp_anno('mat','si');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_clasif_de_asignaturas(asignatura VARCHAR, periodo VARCHAR) RETURNS VARCHAR[] as
$$
#variable_conflict use_variable
-- #22: a name that is both a variable and a column resolves to the variable,
-- as PostgreSQL 8.4 (2010) always did; PostgreSQL >= 9.0 raises 42702 instead.
-- Here: parameters "asignatura" and "periodo" vs. the desglose/plantilla_de_actividad columns.
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT plantilla_de_actividad.clasificacion as clasif 
             FROM plantilla_de_actividad 
             INNER JOIN desglose ON plantilla_de_actividad.desglose = desglose.id 
             WHERE desglose.periodo = periodo AND desglose.asignatura = asignatura 
             ORDER BY plantilla_de_actividad.orden LOOP
            arr := array_append(arr, r.clasif);
        END LOOP;
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';