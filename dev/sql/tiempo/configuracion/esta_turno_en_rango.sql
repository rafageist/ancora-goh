CREATE or replace function esta_turno_en_rango(dia INTEGER, turno INTEGER, duracion INTEGER, dia_ini INTEGER, dia_fin INTEGER, turno_ini INTEGER, turno_fin INTEGER, discreto BOOLEAN) RETURNS BOOLEAN as
$$
    
    BEGIN
        IF dia < dia_ini THEN
            RETURN FALSE;
        END IF;

        IF dia > dia_fin THEN
            RETURN FALSE;
        END IF;
        
        IF discreto = TRUE THEN
            IF turno + duracion - 1 < turno_ini THEN
                RETURN FALSE;
            END IF;
            IF turno > turno_fin THEN
                RETURN FALSE;
            END IF;
        ELSE
            IF dia = dia_ini AND turno + duracion - 1< turno_ini THEN
                RETURN FALSE;
            END IF;
            IF dia = dia_fin AND turno > turno_fin THEN
                RETURN FALSE;
            END IF;
        END IF;
        RETURN TRUE;
    END;
$$
LANGUAGE 'plpgsql';