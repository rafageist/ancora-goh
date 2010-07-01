CREATE OR REPLACE FUNCTION matrix.get_max(arr anyarray) RETURNS anyelement as 
$$
    BEGIN
        RETURN arr[matrix.get_max_pos(arr)];
    END;
$$
LANGUAGE  'plpgsql';