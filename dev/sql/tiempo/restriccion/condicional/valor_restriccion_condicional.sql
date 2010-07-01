-- CONOCER RESTRICCIONES CONDICIONALES
    CREATE or replace function valor_restriccion_condicional(tipo_consec VARCHAR, id_consec VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        DECLARE r record;
            ract record;
            c BIGINT;
        BEGIN
            -- se analizan las restricciones condicionales
            -- donde el implicado se encuentren en las consecuencias o
            -- postcondiciones de la restriccion (tipo regla)

            FOR r IN SELECT * FROM restriccion_condicional 
                            WHERE activa = TRUE AND dame_tipo_consec_rest_cond(id) = tipo_consec AND (consec_periodo = idperiodo OR consec_periodo IS NULL) LOOP
              
                IF siempre = FALSE THEN
                    IF (r.discreto = TRUE) THEN
                      CONTINUE WHEN (rcdia_ini IS NOT NULL AND act.dia < r.dia_ini) 
                          OR (r.dia_fin IS NOT NULL AND act.dia > r.dia_fin)
                          OR (r.turno_ini IS NOT NULL AND act.turno < r.turno_ini)
                          OR (r.turno_fin IS NOT NULL AND r.turno_fin < act.turno + duracion_act);
                    ELSE
                      CONTINUE WHEN (r.dia_ini IS NOT NULL AND act.dia < r.dia_ini) 
                          OR (r.dia_fin IS NOT NULL AND act.dia > r.dia_fin)
                          OR (r.turno_ini IS NOT NULL AND act.dia = r.dia_ini AND act.turno < r.turno_ini)
                          OR (r.turno_fin IS NOT NULL AND act.dia = r.dia_fin AND r.turno_fin < act.turno + duracion_act);
                    END IF;
                END IF;
                
                -- ahora se busca si la causa existe

                c := 0;
                
                FOR ract IN SELECT * FROM actividad WHERE 
                    (r.clasif_act = dame_clasif_plantilla_de_actividad(act.plantilla) OR rc.clasif_act IS NULL) AND
                    (r.asig = dame_asignatura_plantilla_de_actividad(act.plantilla) OR r.asig IS NULL) AND
                    (matrix.get_pos_of(rc.brigada, dame_lista_id_brigadas_de_grupo(act.grupo)) > -1 OR r.brigada IS NULL) AND
                    (r.profesor = act.profesor OR r.profesor IS NULL) AND
                    (r.lugar = act.lugar OR r.lugar IS NULL) AND
                    (r.especialidad = dame_especialidad_de_asignatura(dame_asignatura_plantilla_de_actividad(act.plantilla)) OR r.especialidad IS NULL) AND
                    (r.nivel = dame_nivel_de_asignatura(dame_asignatura_plantilla_de_actividad(act.plantilla)) OR r.nivel IS NULL) AND
                    (r.periodo = actperiodo OR r.periodo IS NULL) LOOP
                    
                
                    IF ocurre_causa_rest_cond(r, ract, pdia, pturno, FALSE) THEN
                        c := c + 1;
                    END IF;
                    
                END LOOP;

                -- uno de los siguientes 3 IF debe entrar
                IF r.cant_exa IS NOT NULL THEN
                    CONTINUE WHEN c <> r.cant_exa;            
                END IF;

                IF r.cant_min IS NOT NULL THEN
                    CONTINUE WHEN c < r.cant_exa;            
                END IF;

                IF r.cant_max IS NOT NULL THEN
                    CONTINUE WHEN c > r.cant_exa;            
                END IF;

                -- si no se cumplen la cantidad de casos 
                -- en la causa debe saltarse lo siguiente
                -- y continuar a la proxima restriccion condicional
                
                -- si al menos una restriccion se cumple ...

                RETURN r.valor;

            END LOOP;
            
            RETURN 0;
        END;
    $$
    LANGUAGE 'plpgsql';