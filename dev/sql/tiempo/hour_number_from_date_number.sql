CREATE or replace function tiempo.hour_number_from_date_number(m DOUBLE PRECISION) RETURNS DOUBLE PRECISION as
$$
    BEGIN
        RETURN m - (trunc(m / 864)) * 864;
    END;
$$
LANGUAGE 'plpgsql';

-- SELECT tiempo.moment_now();
-- SELECT tiempo.hour_number_from_date_number(tiempo.moment_now());
-- SELECT tiempo.number_to_hour(tiempo.hour_number_from_date_number(tiempo.moment_now()));
