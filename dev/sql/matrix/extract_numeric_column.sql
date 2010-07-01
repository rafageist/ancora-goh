CREATE OR REPLACE FUNCTION matrix.extract_numeric_column(arr ANYELEMENT, c BIGINT) RETURNS NUMERIC[] AS
$$
    DECLARE narr NUMERIC[];
        i BIGINT;
        crows BIGINT;
    BEGIN
        i := 1;
        crows := matrix.get_length_of(arr,2);
        IF crows > 0 THEN
            LOOP
                narr := array_append(narr, arr[i][c]);
                EXIT WHEN i >= crows;
                i := i + 1;
            END LOOP;
        END IF;
        RETURN narr;
    END;
$$
LANGUAGE 'plpgsql';