-- Convert number to hour
CREATE OR REPLACE FUNCTION tiempo.number_to_hour(n float) RETURNS tiempo.ancora_hour as
$$
    DECLARE t tiempo.ancora_hour;
    BEGIN
        t.h := trunc(n / 36);
        t.m := trunc((n - t.h * 36) /  0.6);
        t.s := trunc((n - (t.h * 36 + t.m * 0.6)) / 0.01);
        
        RETURN t;
    END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION tiempo.number_to_hour(float) is 'Conert number to hour';
-- SELECT tiempo.number_to_hour(863.99);