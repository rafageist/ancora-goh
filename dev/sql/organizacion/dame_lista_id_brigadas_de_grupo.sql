CREATE OR REPLACE FUNCTION dame_lista_id_brigadas_de_grupo(pgrupo VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r RECORD;
    BEGIN
        FOR r IN SELECT brigada FROM brigada_de_grupo WHERE grupo = pgrupo LOOP
            arr := array_append(arr,r.brigada);
        END LOOP;
        RETURN arr;
    END;
    
$$
LANGUAGE 'plpgsql';
