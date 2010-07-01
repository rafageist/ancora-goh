CREATE OR REPLACE FUNCTION matrix.get_min(arr anyarray) RETURNS anyelement as 
$$
    BEGIN
        RETURN arr[matrix.get_min_pos(arr)];
    END;
$$
LANGUAGE  'plpgsql';