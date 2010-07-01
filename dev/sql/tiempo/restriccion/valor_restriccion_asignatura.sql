    CREATE or replace function valor_restriccion_asignatura (id VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        BEGIN
            RETURN valor_restriccion_generica('ASIGNATURA',id, idperiodo, pdia, pturno, '*');
        END;
        
    $$
    LANGUAGE 'plpgsql';