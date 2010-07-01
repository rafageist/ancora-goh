CREATE OR REPLACE FUNCTION es_lugar_prohibido(pgrupo VARCHAR, plugar VARCHAR) RETURNS BOOLEAN AS
$$
    DECLARE r record;
    BEGIN
        SELECT * INTO r FROM acceso_prohibido WHERE grupo = pgrupo AND lugar = plugar;
        IF r.grupo = pgrupo THEN
            RETURN TRUE;
        END IF;
        RETURN FALSE;
    END;
$$
LANGUAGE 'plpgsql';