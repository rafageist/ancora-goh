-- SELECT matrix.get_pos_of(array['a','b','c'],'c');
CREATE OR REPLACE FUNCTION matrix.get_pos_of(arr anyarray, v ANYELEMENT) RETURNS BIGINT AS
$$
    DECLARE i BIGINT; j BIGINT; 
        x BIGINT;
    BEGIN
        x := matrix.get_length_of(arr,1);
        IF x > 0 THEN
            i := 1;
            LOOP
                IF arr[i] = v THEN
                    RETURN i;
                END IF;
                
                EXIT WHEN i >= x;
                i := i + 1;
            END LOOP;
        END IF;
        RETURN -1;
        
    END;
$$
LANGUAGE 'plpgsql';