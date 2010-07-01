    -- clasificacion de actividad
    CREATE or replace function valor_restriccion_clasif_act (id VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        BEGIN
            RETURN valor_restriccion_generica('CLASIFACT',id, idperiodo, pdia, pturno, '*');
        END;
        
    $$
    LANGUAGE 'plpgsql';