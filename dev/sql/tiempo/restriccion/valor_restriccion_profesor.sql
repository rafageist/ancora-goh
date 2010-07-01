CREATE or replace function valor_restriccion_profesor(id VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        DECLARE v INTEGER;
        BEGIN
            v := valor_restriccion_generica('PROFESOR', id, idperiodo, pdia, pturno, '*');

            IF v = 0 THEN
                v := valor_restriccion_heredada('PROFESOR', id, idperiodo, pdia, pturno);
            END IF;

            IF v = 0 THEN
                v := valor_restriccion_condicional('PROFESOR', id, idperiodo, pdia, pturno);
            END IF;
            
            RETURN v;
        END;
        
    $$
    LANGUAGE 'plpgsql';