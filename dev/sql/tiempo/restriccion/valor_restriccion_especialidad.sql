    -- especialidad
    CREATE or replace function valor_restriccion_especialidad(id VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        BEGIN
            RETURN valor_restriccion_generica('ESPECIALIDAD',id, idperiodo, pdia, pturno, '*');
        END;
        
    $$
    LANGUAGE 'plpgsql';