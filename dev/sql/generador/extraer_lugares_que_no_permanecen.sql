CREATE OR REPLACE FUNCTION extraer_lugares_que_no_permanecen (lugares VARCHAR[], periodo VARCHAR, dia INTEGER, turno INTEGER, duracion INTEGER) RETURNS VARCHAR[] AS
$$
    DECLARE
        arr VARCHAR[];
        cant BIGINT;
        i BIGINT;
        
    BEGIN
        cant := matrix.get_length_Of(lugares,1);
        
        i := 1;
        LOOP
            IF lugar_permanece_en(lugares[i],periodo,dia,turno,duracion) THEN
                arr := array_append(arr, lugares[i]);
            END IF;
            EXIT WHEN i >= cant;
            i := i + 1;
        END LOOP;
        
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';