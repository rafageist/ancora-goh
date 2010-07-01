CREATE or replace function tiempo.current_hour_number() RETURNS DOUBLE PRECISION as
$$
    DECLARE ahora timestamp;
        hora INTEGER;
        minuto INTEGER;
        segundo INTEGER;
        h DOUBLE PRECISION;
        
    BEGIN
        ahora := now();
        hora := EXTRACT(HOUR FROM ahora);
        minuto := EXTRACT(MINUTE FROM ahora);
        segundo := trunc(EXTRACT(SECOND FROM ahora));

        h := tiempo.hour_to_number(hora,minuto,segundo);
        
        RETURN h;

    END;
$$
LANGUAGE 'plpgsql';