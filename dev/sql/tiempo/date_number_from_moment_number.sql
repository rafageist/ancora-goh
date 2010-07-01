CREATE or replace function tiempo.date_number_from_moment_number(m DOUBLE PRECISION) RETURNS BIGINT as
$$
    BEGIN
        RETURN trunc(m / 864) + 1;
    END;
$$
LANGUAGE 'plpgsql';

-- SELECT now();
-- SELECT tiempo.number_to_date(tiempo.date_number_from_moment_number(tiempo.moment_now()));
