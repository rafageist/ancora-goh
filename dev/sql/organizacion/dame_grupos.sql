CREATE OR REPLACE FUNCTION dame_grupos(idesp VARCHAR, idnivel VARCHAR, idclasif VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r RECORD;
    BEGIN
        FOR r IN SELECT grupo FROM brigada_de_grupo WHERE dame_especialidad_de_brigada(brigada) = idesp AND dame_nivel_de_brigada(brigada) = idnivel AND dame_clasificacion_de_grupo(grupo) = idclasif GROUP BY grupo LOOP
            arr := array_append(arr,r.grupo);
        END LOOP;
        RETURN arr;
    END;
    
$$
LANGUAGE 'plpgsql';