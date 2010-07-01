
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_profes_asignados_al_grupo
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los PROFESORES asignados a una plantilla 
--              de actividad y a un grupo determinado
--     EJEMPLO: SELECT  dame_lista_id_profes_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_profes_asignados_al_grupo(idplantilla VARCHAR, idgrupo VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM profesor_para_actividad 
		WHERE plantilla = idplantilla AND EXISTS(SELECT * FROM profesor_para_grupo 
							  WHERE profesor_para_grupo.profesor_para_actividad = profesor_para_actividad.id 
							  AND profesor_para_grupo.grupo = idgrupo) ORDER BY prioridad 
	LOOP
            arr := array_append(arr,r.profesor);
        END LOOP;

        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';

