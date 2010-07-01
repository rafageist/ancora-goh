CREATE OR REPLACE FUNCTION tiempo.is_leap_year(pyear INTEGER) RETURNS BOOLEAN AS
$$
    BEGIN
        IF mod(pyear, 4) = 0 and mod(pyear, 100) <> 0 or mod(pyear, 400) = 0 THEN 
            RETURN TRUE;
        END IF;
        RETURN FALSE;
    END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION tiempo.is_leap_year(INTEGER) is 'RETURN TRUE IF is a leap year';
