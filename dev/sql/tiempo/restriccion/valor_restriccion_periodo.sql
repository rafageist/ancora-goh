    CREATE or replace function valor_restriccion_periodo(idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        BEGIN
            RETURN valor_restriccion_generica('PERIODO', idperiodo, '', pdia, pturno, '*');            
        END;
    $$
    LANGUAGE 'plpgsql';