CREATE or replace function esta_dia_en_rango_planificacion(pdia INTEGER, idperiodo VARCHAR, dia_ini INTEGER, dia_fin INTEGER) RETURNS BOOLEAN as
$$
    DECLARE r record;
    BEGIN
        SELECT * into r from periodicidad where periodo = idperiodo and (dia+pdia >=dia_ini AND dia+pdia<=dia_fin);
        IF FOUND THEN
            RETURN TRUE;
        END IF;
        
        RETURN FALSE;
    END;
$$
LANGUAGE 'plpgsql';