-- Function: valor_restriccion_generica(character varying, character varying, character varying, integer, integer, character varying)

-- DROP FUNCTION valor_restriccion_generica(character varying, character varying, character varying, integer, integer, character varying);

CREATE OR REPLACE FUNCTION valor_restriccion_generica(ptipo character varying, id character varying, idperiodo character varying, pdia integer, pturno integer, tipo_restricc character varying)
  RETURNS integer AS
$BODY$
        DECLARE r record;
            rperiodo record;
            rper record;
            r1 record;
            r2 record;
            dia_analizando INTEGER;
            hi DOUBLE PRECISION; hf DOUBLE PRECISION;
            ti INTEGER; tf INTEGER;
            i INTEGER;
            per1 record;
            tipo VARCHAR;
        BEGIN
            
            -- restricciones en el periodo

            tipo := upper(ptipo);

            IF tipo = 'PERIODO' THEN
                SELECT valor INTO r from restriccion_de_periodo where periodo = id and dia = pdia and turno = pturno; 
            END IF;
            
            IF tipo = 'ASIGNATURA' THEN
                SELECT valor INTO r from restriccion_de_asignatura where asignatura = id AND periodo = idperiodo and dia = pdia and turno = pturno;
            END IF;

            IF tipo = 'BRIGADA' THEN
                SELECT valor INTO r from restriccion_de_brigada where brigada = id AND periodo = idperiodo and dia = pdia and turno = pturno AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
            END IF;

            IF tipo = 'CLASIFACT' THEN
                SELECT valor INTO r from restriccion_de_clasif_act where clasificacion = id AND periodo = idperiodo and dia = pdia and turno = pturno;
            END IF;

            IF tipo = 'ESPECIALIDAD' THEN
                SELECT valor INTO r from restriccion_de_especialidad where especialidad = id AND periodo = idperiodo and dia = pdia and turno = pturno;
            END IF;

            IF tipo = 'LUGAR' THEN
                SELECT valor INTO r from restriccion_de_lugar where lugar = id AND periodo = idperiodo and dia = pdia and turno = pturno AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
            END IF;

            IF tipo = 'OTRORECURSO' THEN
                SELECT valor INTO r from restriccion_de_otro_recurso where otro_recurso = id AND periodo = idperiodo and dia = pdia and turno = pturno AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
            END IF;

            IF tipo = 'PROFESOR' THEN
                SELECT valor INTO r from restriccion_de_profesor where profesor = id AND periodo = idperiodo and dia = pdia and turno = pturno AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
            END IF;

            IF FOUND THEN
                RETURN r.valor;
            END IF;
            
            /** @nota: Ahora se busca en las restricciones reales. El codigo anterior puede */
            /** quitarse y entonces recorrer todas las restricciones reales,                */
            /** pero se deja para optimizar la busqueda y se recrre entonces despues las    */
            /** demas periodicidades, pues basta preguntar por el turno de un periodo y no  */
            /** por todas sus periodicidades para saber si esta restringido                 */
                        
            -- restricciones en otros periodos
            
            SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;

            hi := dame_hora_inicial_turno(rperiodo.id, pturno);
            hf := hi + rperiodo.duracion_del_turno;

            -- se analizan las periodicidades (recorriendo la temporada)
            FOR r IN SELECT * FROM periodicidad WHERE periodo <> idperiodo LOOP

                -- se calcula la fecha real
                dia_analizando := dame_dia_real_planificacion(rperiodo.planificacion, r.dia + pdia - 1);
                
                -- se busca en las restricciones reales
                
                IF tipo = 'PERIODO' THEN
                    SELECT * INTO r 
                        FROM restriccion_real_de_periodo 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi);
                        IF FOUND THEN
				RETURN 1;
			END IF;
                END IF;
                
                IF tipo = 'ASIGNATURA' THEN
                    SELECT * INTO r 
                        FROM restriccion_real_de_asignatura
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi);             
                        IF FOUND THEN
				RETURN 1;
			END IF; 
                END IF;

                IF tipo = 'BRIGADA' THEN
                    SELECT * INTO r 
                        FROM restriccion_real_de_brigada 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi)
                        AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        IF FOUND THEN
				RETURN 1;
			END IF;
                END IF;

                IF tipo = 'CLASIFACT' THEN
                    SELECT * INTO r 
                        FROM restriccion_real_de_clasif_act
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi); 
                        IF FOUND THEN
				RETURN 1;
			END IF;                
                END IF;

                IF tipo = 'ESPECIALIDAD' THEN
                    SELECT * INTO r 
                        FROM restriccion_real_de_especialidad
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi);  
                        IF FOUND THEN
				RETURN 1;
			END IF;               
                END IF;

                IF tipo = 'LUGAR' THEN
                    SELECT * INTO r 
                        FROM restriccion_real_de_lugar 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi)
                        AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        IF FOUND THEN
				RETURN 1;
			END IF;
                END IF;

                IF tipo = 'OTRORECURSO' THEN
                    SELECT * INTO r 
                        from restriccion_real_de_otro_recurso 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi)
                        AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        IF FOUND THEN
				RETURN 1;
			END IF;
                END IF;

                IF tipo = 'PROFESOR' THEN
                    SELECT * INTO r 
                        from restriccion_real_de_profesor 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi)
                        AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        IF FOUND THEN
				RETURN 1;
			END IF;
                END IF;

                /*IF FOUND THEN
                    RETURN r2.valor;
                END IF;*/
            END LOOP;    

            RETURN 0;
            /**
            -- se analizan las periodicidades (recorriendo la temporada)
            FOR r IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP

                dia_analizando := r.dia + pdia - 1;
                 
                -- se analizan los otros periodos

                FOR r1 IN SELECT * FROM periodicidad WHERE periodo <> idperiodo AND dia_analizando >= dia AND dia_analizando <= dia + dame_cant_dias(periodo) - 1 LOOP


                    -- hay que normalizar las configuraciones de tiempo de ambos periodos ...
                    
                    SELECT * INTO per1 FROM periodo WHERE id = r1.periodo;
                    
                    hi := dame_hora_inicial_turno(rperiodo.id, pturno);
                    hf := hi + rperiodo.duracion_del_turno;

                    ti := (hi - per1.hora_inicial) / per1.duracion + 1;
                    tf := (hf - per1.hora_inicial) / per1.duracion + 1;

                    -- para despues poder questionar las restricciones del segundo

                    -- nota: el dia a questionar es:  dia_analizando - r1.dia + 1
                    
                    i := ti;
                    LOOP

                        IF tipo = 'PERIODO' THEN
                            SELECT valor INTO r from restriccion_de_periodo where periodo = r1.periodo and dia = dia_analizando - r1.dia + 1  and turno = i;
                        END IF;
                        
                        IF tipo = 'ASIGNATURA' THEN
                            SELECT valor INTO r from restriccion_de_asignatura where asignatura = id AND periodo = r1.periodo and dia = dia_analizando - r1.dia + 1 and turno = i;
                        END IF;

                        IF tipo = 'BRIGADA' THEN
                            SELECT valor INTO r from restriccion_de_brigada where brigada = id AND periodo = r1.periodo and dia = dia_analizando - r1.dia + 1 and turno = i AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        END IF;

                        IF tipo = 'CLASIFACT' THEN
                            SELECT valor INTO r from restriccion_de_clasif_act where clasif_act = id AND periodo = r1.periodo and dia = dia_analizando - r1.dia + 1 and turno = i;
                        END IF;

                        IF tipo = 'ESPECIALIDAD' THEN
                            SELECT valor INTO r from restriccion_de_especialidad where especialidad = id AND periodo = r1.periodo and dia = dia_analizando - r1.dia + 1 and turno = i;
                        END IF;

                        IF tipo = 'LUGAR' THEN
                            SELECT valor INTO r from restriccion_de_lugar where lugar = id AND periodo = r1.periodo and dia = dia_analizando - r1.dia + 1 and turno = i AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        END IF;

                        IF tipo = 'OTRORECURSO' THEN
                            SELECT valor INTO r from restriccion_de_otro_recurso where otro_recurso = id AND periodo = r1.periodo and dia = dia_analizando - r1.dia + 1 and turno = i AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        END IF;

                        IF tipo = 'PROFESOR' THEN
                            SELECT valor INTO r from restriccion_de_profesor where profesor = id AND periodo = r1.periodo and dia = dia_analizando - r1.dia + 1 and turno = i AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        END IF;

                        IF FOUND THEN
                            RETURN r2.valor;
                        END IF;
                        
                        EXIT WHEN i = tf;
                        i := i + 1;
                    END LOOP;
                    
                END LOOP;
            
            END LOOP;*/
            
            RETURN 0;
            
        END;
    $BODY$
  LANGUAGE 'plpgsql' VOLATILE
  COST 100;

