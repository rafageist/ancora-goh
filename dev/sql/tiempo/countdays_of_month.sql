-- Count days of month
CREATE OR REPLACE FUNCTION tiempo.countdays_of_month(m INTEGER) RETURNS INTEGER as
$$
    DECLARE d INTEGER;
    BEGIN
        d:=31;
        
        IF (m=4 or m=6 or m=9 or m=11) THEN
            d:=30;
        END IF;
        
        IF (m=2) THEN
            d:=28;
        END IF;
        
        RETURN d;
    END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION tiempo.countdays_of_month(INTEGER) is 'RETURNS count of days of specificic month';
