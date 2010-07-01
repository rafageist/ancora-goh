CREATE OR REPLACE FUNCTION matrix.create_boolean_matrix(filas BIGINT, columnas BIGINT) RETURNS BOOLEAN[][] as
$$
    DECLARE
        fila VARCHAR;
        s VARCHAR;
        matrix BOOLEAN[][];
        i BIGINT;
        j BIGINT;
    BEGIN
        IF filas IS NULL OR columnas is NULL OR filas = 0 OR columnas = 0 THEN
            RETURN '{}'::BOOLEAN[][];
        END IF;
        
        s := '{';
        i := 1;
        LOOP
            j := 1;
            fila := '{';
            LOOP
                fila := fila || 'FALSE';

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

        matrix := s::BOOLEAN[][];
        RETURN matrix;
    END;
$$
LANGUAGE 'plpgsql';