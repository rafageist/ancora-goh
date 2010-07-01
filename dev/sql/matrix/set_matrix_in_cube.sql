-- --------------------------------
--          NOMBRE: 
--            TIPO:
--     DESCRIPCION:
--         EJEMPLO:
-- ---------------------------------
CREATE OR REPLACE FUNCTION matrix.set_matrix_in_cube(out pcube anyarray, pmatrix anyarray, pos BIGINT) RETURNS anyarray AS
$$
    DECLARE x BIGINT; y BIGINT; z BIGINT; r BIGINT; c BIGINT; i BIGINT; j BIGINT; 
    BEGIN
        x := matrix.get_length_of(pcube,1);
        y := matrix.get_length_of(pcube,2);
        z := matrix.get_length_of(pcube,3);

        r := matrix.get_length_of(pmatrix,1);
        c := matrix.get_length_of(pmatrix,2);

        IF x > 0 AND y > 0 AND z > 0 AND r > 0 AND c > 0 THEN
                    
            i := 0;
            LOOP
                j := 0;
                LOOP
                    
                    pcube[pos][i][j] := pmatrix[i][j];
                    
                    EXIT WHEN j >= c;
                    j := j + 1;
                END LOOP;
                EXIT WHEN i >= r;
                i := i + 1;
            END LOOP;
        END IF;
    END;
$$
LANGUAGE 'plpgsql';