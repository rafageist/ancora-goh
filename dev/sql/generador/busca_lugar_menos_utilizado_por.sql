
-- SELECT busca_lugar_menos_utilizado(array['lug33','lug2','lug3'],'grupo1');
CREATE OR REPLACE FUNCTION busca_lugar_menos_utilizado_por(lugares VARCHAR[], idgrupo VARCHAR, los_demas BOOLEAN) RETURNS VARCHAR as
$$
    DECLARE i BIGINT;
        cant BIGINT;
        m INTEGER;
        cantr INTEGER;
        pos BIGINT;
        r record;
    BEGIN
        cant := matrix.get_length_of(lugares, 1);

        m := 0;
        pos := 0;
        
        i := 1;
        
        LOOP
            SELECT * INTO r FROM actividad WHERE lugar = lugares[i] AND ((grupo = idgrupo AND los_demas = FALSE) OR (grupo <> idgrupo AND los_demas = TRUE)) ;

            IF NOT FOUND THEN
                cantr :=0;
            ELSE
                GET DIAGNOSTICS cantr = ROW_COUNT;
            END IF;

            IF cantr <m OR i = 1 THEN
                m := cantr;
                pos := i;
            END IF;
            
            EXIT WHEN i = cant;
            i := i + 1;
        END LOOP;

        -- al meno uno de los lugares debe ser utilizado
        IF m = 0 THEN 
            RETURN '';
        END IF;
        
        RETURN lugares[pos];
    END;
$$
LANGUAGE 'plpgsql';