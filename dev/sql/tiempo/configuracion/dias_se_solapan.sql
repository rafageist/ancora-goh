CREATE or replace function dias_se_solapan (idperiodo1 VARCHAR, dia1 INTEGER, idperiodo2 VARCHAR, dia2 INTEGER) RETURNS BOOLEAN as
    $$
        DECLARE r1 record;
            r2 record;
            dia1 BIGINT;
            dia2 BIGINT;
            planificacion1 VARCHAR;
            planificacion2 VARCHAR;
            dia_real1 BIGINT;
            dia_real2 BIGINT;

        BEGIN
            planificacion1 := dame_planificacion_de_periodo(idperiodo1);
            planificacion2 := dame_planificacion_de_periodo(idperiodo2);
            dia_real1 := dame_dia_real_planificacion(planificacion1,1);
            dia_real2 := dame_dia_real_planificacion(planificacion2,1);

            -- recorro cada periodicidad del segundo por cada periodicidad del primero
            FOR r1 IN SELECT * FROM periodicidad WHERE periodo = idperiodo1 LOOP
                dia1 := dia_real1 + r1.dia - 1;
                FOR r2 IN SELECT * FROM periodicidad WHERE periodo = idperiodo2 LOOP
                    dia2 := dia_real2 + r2.dia - 1;    
                    IF dia1 = dia2 THEN
                        RETURN TRUE;
                    END IF;
                END LOOP;
            END LOOP;

            RETURN FALSE;
        END;
    $$
    LANGUAGE 'plpgsql';