CREATE OR REPLACE FUNCTION matrix.create_numeric_matrix(filas BIGINT, columnas BIGINT) RETURNS NUMERIC[][] as
$$
    DECLARE
        fila VARCHAR;
        s VARCHAR;
        matrix NUMERIC[][];
        i BIGINT;
        j BIGINT;
    BEGIN
        IF filas IS NULL OR columnas is NULL or filas = 0 or columnas = 0 THEN
            RETURN '{}'::NUMERIC[][];
        END IF;
        s := '{';
        i := 1;
        LOOP
            j := 1;
            fila := '{';
            LOOP
                fila := fila || '0';

                IF j <> columnas THEN
                    fila := fila || ',';
                END IF;
                
                EXIT WHEN j >= columnas;
                j := j + 1;
            END LOOP;
            
            fila := fila || '}';

            s := s || fila;
            IF i <> filas THEN
                s := s || ',';
            END IF;

            
            EXIT WHEN i >= filas;
            i := i + 1;
        END LOOP;
        
        s := s || '}';

        matrix := s::NUMERIC[][];
        RETURN matrix;
    END;
$$
LANGUAGE 'plpgsql';