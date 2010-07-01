-- BUSCAR MINIMO EN UN ARREGLO
CREATE OR REPLACE FUNCTION matrix.get_min_pos(arr anyarray) RETURNS bigint as 
$$
    DECLARE 
        pos bigint;
        i bigint;
        cant bigint;
    BEGIN
        cant := matrix.get_length_of(arr, 1);
        
        IF cant < 1 THEN
            RETURN -1;
        END IF;
        
        pos := 1;
        i := 1;
        LOOP
            EXIT WHEN i > cant;
            IF arr[pos] > arr[i] THEN 
                pos := i;
            END IF;

            EXIT WHEN i = cant;
            i := i + 1;
        END LOOP;

        RETURN pos;
    END;
$$
LANGUAGE  'plpgsql';