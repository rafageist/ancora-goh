-- Month from consecutive number of date
CREATE OR REPLACE FUNCTION tiempo.month_from_day_number(n INTEGER, a INTEGER) RETURNS BIGINT as
$$
    DECLARE cd INTEGER;
        x INTEGER;
    BEGIN
        x := bool_to_int(tiempo.is_leap_year(a));
        
        cd := 1 + bool_to_int(n > 31) + bool_to_int(n > 59 + x) + bool_to_int(n > 90 + x) + bool_to_int(n > 120 + x);
        cd := cd + bool_to_int(n > 151 + x) + bool_to_int(n > 181 + x) + bool_to_int(n > 212 + x) + bool_to_int(n > 243 + x);
        cd := cd + bool_to_int(n > 273 + x) + bool_to_int (n > 304 + x) + bool_to_int(n > 334 + x);

        RETURN cd;
    END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION tiempo.month_from_day_number(INTEGER,INTEGER) is 'RETURNS month from consecutive number of date';
