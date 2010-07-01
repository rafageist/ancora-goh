-- --------------------------------
--          NOMBRE: 
--            TIPO:
--     DESCRIPCION:
--         EJEMPLO:
-- ---------------------------------
CREATE OR REPLACE FUNCTION matrix.echo(arr anyarray) RETURNS void AS
$$
    BEGIN
        RAISE NOTICE '[INFO] array = %', array_to_string(arr,',');
    END;
$$
LANGUAGE 'plpgsql';