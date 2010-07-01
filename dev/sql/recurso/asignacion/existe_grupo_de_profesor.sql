CREATE OR REPLACE FUNCTION existe_grupo_de_profesor(profe_para_act VARCHAR, pgrupo VARCHAR) RETURNS BOOLEAN as
$$
    DECLARE r record;
    BEGIN
        SELECT * INTO r FROM profesor_para_grupo WHERE profesor_para_actividad = profe_para_act AND grupo = pgrupo;    
        IF r.grupo = pgrupo THEN
            RETURN TRUE;
        END IF;
        RETURN FALSE;
    END;
$$
LANGUAGE 'plpgsql';