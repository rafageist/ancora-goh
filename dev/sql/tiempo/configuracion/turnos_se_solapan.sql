-- conocer si dos turnos de diferentes periodos se solapan
    CREATE OR REPLACE FUNCTION turnos_se_solapan(idperiodo1 VARCHAR, dia1 INTEGER, turno1 INTEGER, duracion1 INTEGER, idperiodo2 VARCHAR, dia2 INTEGER, turno2 INTEGER, duracion2 INTEGER) RETURNS BOOLEAN as
    $$
        DECLARE r1 record;
            r2 record;
            dia1 BIGINT;
            dia2 BIGINT;
            planificacion1 VARCHAR;
            planificacion2 VARCHAR;
            dia_real1 BIGINT;
            dia_real2 BIGINT;
            duracionper2 DOUBLE PRECISION;
            hora_inicial2 DOUBLE PRECISION;
            ti1 INTEGER;
            tf1 INTEGER;
            ti2 INTEGER;
            tf2 INTEGER;
            hi DOUBLE PRECISION;
            hf DOUBLE PRECISION;
        BEGIN
            planificacion1 := dame_planificacion_de_periodo(idperiodo1);
            planificacion2 := dame_planificacion_de_periodo(idperiodo2);
            dia_real1 := dame_dia_real_planificacion(planificacion1, 1);
            dia_real2 := dame_dia_real_planificacion(planificacion2, 1);

            hi := dame_hora_inicial_turno(idperiodo1, turno);
            hf := hi + dame_duracion_turno_periodo(idperiodo1) * duracion1;

            duracionper2 := dame_duracion_turno_periodo(idperiodo2);
            hora_inicial2 := dame_hora_inicial_periodo(idperiodo2);
            
            ti2 := (hi - hora_inicial2) / duracionper2 + 1;
            tf2 := (hf - hora_inicial2) / duracionper2 + 1;

            ti1 := turno2;
            tf1 := turno2 + duracion2 - 1; 

            -- verifico que se solpen segun el horario, esto es mas eficiente que recorrer
            -- cada periodicidad y preguntar lo mismo n veces

            IF tf1 < ti2 OR tf2 < ti1 THEN
                RETURN FALSE;
            END IF;
            
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