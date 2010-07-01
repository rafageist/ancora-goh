-- Consecutive number of day
CREATE OR REPLACE FUNCTION tiempo.day_number(d INTEGER, m INTEGER, a INTEGER) RETURNS BIGINT as
$$
    DECLARE cd INTEGER;
	    day_began integer[12];
    
    BEGIN
        -- cantidad de dias antes del mes a partir del 1ero de enero
        cd := 0;

        day_began[1] := 1;
        day_began[2] := 31 + 1;
        day_began[3] := 31 + 28 + 1;
        day_began[4] := 31 + 28 + 31 + 1;
        day_began[5] := 31 + 28 + 31 + 30 + 1;
        day_began[6] := 31 + 28 + 31 + 30 + 31 + 1;
        day_began[7] := 31 + 28 + 31 + 30 + 31 + 30 + 1;
        day_began[8] := 31 + 28 + 31 + 30 + 31 + 30 + 31 + 1;
        day_began[9] := 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 1;
        day_began[10] := 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 1;
        day_began[11] := 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31 + 1;
        day_began[12] := 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31 + 30 + 1;

        cd := day_began[m] + d -1;

        -- si es bisiesto y la fecha es despues de febrero se le aumenta un dia
        IF (tiempo.is_leap_year(a) = TRUE and m > 2) THEN
            cd := cd + 1;
        END IF;
        
        RETURN cd;
    END;
$$
LANGUAGE 'plpgsql';    

COMMENT ON FUNCTION tiempo.day_number(INTEGER,INTEGER,INTEGER) is 'RETURNS the consecutive number of day';
