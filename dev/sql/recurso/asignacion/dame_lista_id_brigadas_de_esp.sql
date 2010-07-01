CREATE OR REPLACE FUNCTION dame_lista_id_brigadas_de_esp(esp VARCHAR, niv VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM brigada WHERE especialidad = esp AND nivel = niv LOOP
            arr := array_append(arr,r.id);
        END LOOP;

        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';