-- -----------------------------------------------------------------------------------
--      NOMBRE: matrix.get_count_of_dims
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de dimensiones de una arreglo (1 - lista, 2 - matrix o 3 - cubo...)
--     EJEMPLO: SELECT matrix.get_count_of_dims(array[1,2,3]) = 1;
--        SELECT matrix.get_count_of_dims(array[[1,2],[1,2]]) = 2;
--        SELECT matrix.get_count_of_dims(array[[[1],[2]],[[1],[2]]]) = 3;
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION matrix.get_count_of_dims(arr ANYELEMENT) RETURNS INTEGER AS
$$
    DECLARE c INTEGER;
    BEGIN
        c := 0;
        LOOP
            IF split_part(array_dims(arr),'][',c + 1) <> '' THEN
                c:= c + 1;
            ELSE 
                EXIT;
            END IF;
            EXIT WHEN c = 36000;
        END LOOP;
        
        RETURN c;
    END;
$$
LANGUAGE 'plpgsql';