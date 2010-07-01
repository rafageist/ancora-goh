-- Consecutive number of date
CREATE OR REPLACE FUNCTION tiempo.date_to_number(d INTEGER, m INTEGER, a INTEGER) RETURNS BIGINT as
$$
    DECLARE cd INTEGER;
    
    BEGIN
        -- cantidad de dias antes del mes a partir del 1ero de enero
        cd := tiempo.day_number(d,m,a);

        -- cantidad de dias a partir del 1ero enero del 2000
        RETURN (a - 2000) * 365 + cd + (a - 2000) / 4 + bool_to_int(tiempo.is_leap_year(a) = FALSE);
    END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION tiempo.date_to_number(INTEGER,INTEGER,INTEGER) is 'RETURNS consecutive number of date';
