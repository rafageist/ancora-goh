CREATE or replace function tiempo.today_number() RETURNS BIGINT as
$$
    DECLARE ahora timestamp;
        dia INTEGER;    
        mes INTEGER;
        anno INTEGER;
        f BIGINT;
        
    BEGIN
        ahora := now();
        dia := EXTRACT(DAY FROM ahora);
        mes := EXTRACT(MONTH FROM ahora);
        anno := EXTRACT(YEAR FROM ahora);
        
        f := tiempo.date_to_number(dia,mes,anno);
    
        RETURN f;

    END;
$$
LANGUAGE 'plpgsql';