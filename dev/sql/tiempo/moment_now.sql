CREATE or replace function tiempo.moment_now() RETURNS DOUBLE PRECISION as
$$
    DECLARE ahora timestamp;
        f BIGINT;
        h DOUBLE PRECISION;
        
    BEGIN

        f := tiempo.today_number();
        h := tiempo.current_hour_number();
        
        RETURN tiempo.moment_number(f, h);

    END;
$$
LANGUAGE 'plpgsql';