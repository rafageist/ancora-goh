
-- -----------------------------------------------------------------------------------
--      NOMBRE: posible_inicio
--        TIPO: funcion
-- DESCRIPCION: Dice si es posible o no colocar una actividad en el dia turno para una brigada
--     EJEMPLO: SELECT  posible_inicio();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION posible_inicio(idplantilla VARCHAR, dia INTEGER, turno INTEGER, idbrigada VARCHAR, plugares VARCHAR[], pprofesores VARCHAR[]) RETURNS BOOLEAN AS
$$
    DECLARE c BIGINT;
        i INTEGER;
        j INTEGER;
        k INTEGER := 0; -- #23: was left NULL when neither teacher nor place is
                        -- required; the essential-resource loop then never ended
        t BIGINT;
        v INTEGER;
        duracion INTEGER;
        periodo VARCHAR;
        clasif VARCHAR;
        asignatura VARCHAR;
        profesores VARCHAR[];
        lugares VARCHAR[];
        cantProfes BIGINT;
        cantLugares BIGINT;
        gruporecursos VARCHAR[];
        recursos VARCHAR[];
        cantGrupoRecursos BIGINT;
        cantRecursos BIGINT;
        r record;
        malo BOOLEAN;
        puede BOOLEAN;
        exige_lugar BOOLEAN;
        exige_profesor BOOLEAN;
    BEGIN

	--RAISE NOTICE '[INFO] POSIBLE INICIO:  dia % y turno % ', dia, turno;
	
        duracion := dame_duracion_plantilla_de_actividad(idplantilla);

        --RAISE NOTICE '[INFO] Duracion obtenida % ', duracion;
        
        periodo := dame_periodo_plantilla_de_actividad(idplantilla);
        clasif := dame_clasif_plantilla_de_actividad(idplantilla);
        asignatura := dame_asignatura_plantilla_de_actividad(idplantilla);

        exige_lugar := plantilla_exige_lugar(idplantilla);
        exige_profesor := plantilla_exige_profesor(idplantilla);

        IF exige_profesor = TRUE THEN
            IF pprofesores is null THEN
                profesores := dame_lista_id_profes_asignados(idplantilla);
            ELSE
                profesores := pprofesores;
            END IF;
            
            cantProfes := matrix.get_length_of(profesores, 1);
        END IF;
        

        IF exige_lugar = TRUE THEN
            IF plugares is null THEN
                lugares := dame_lista_id_lugares_asignados(idplantilla);    
            ELSE
                lugares := plugares;
            END IF;
            cantLugares := matrix.get_length_of(lugares, 1);
        END IF;
        
        
        i := 0;
        LOOP
	    --RAISE NOTICE '[INFO] POSIBLE INICIO: comprobando restricciones para turno % ', turno + i;
	    
            EXIT WHEN i = duracion;
            
            -- comprobando que el periodo no est?estringido
            
            v:= valor_restriccion_periodo(periodo, dia, turno + i);

            IF v > 0 THEN
                RETURN FALSE;
            END IF;

            -- comprobando la disponibilidad de la clasificacion

            v:= valor_restriccion_clasif_act(clasif, periodo, dia, turno + i);

            IF v > 0 THEN
                RETURN FALSE;
            END IF;

            -- comprobando la disponibilidad de la asignatura

            v:= valor_restriccion_asignatura(asignatura, periodo, dia, turno + i);

            IF v > 0 THEN
                RETURN FALSE;
            END IF;
            
            -- compoobando la disponibilidad de la brigada

            v:= valor_restriccion_brigada(idbrigada, periodo, dia, turno + i);

            IF v > 0 THEN
                RETURN FALSE;
            END IF;
            
            i := i + 1;
        END LOOP;


        IF exige_profesor = TRUE THEN

	    --RAISE NOTICE '[INFO] POSIBLE INICIO: comprobar la disponibilidad de profesores ';
            -- comprobar la disponibilidad de profesores
            
            j := 0;

            k := 0;
            
            LOOP
                EXIT WHEN j = cantProfes;

                puede := TRUE;
                
                i := 0;
                LOOP
                    EXIT WHEN i = duracion;
                                
                    v:= valor_restriccion_profesor(profesores[j], periodo, dia, turno + i);

                    IF v > 0 THEN
                        puede := FALSE;
                        EXIT;
                    END IF;
                    i := i + 1;
                END LOOP;

                IF puede = TRUE THEN
                    k := k + 1;
                    EXIT;
                END IF;
                
                j := j + 1;
            END LOOP;

            IF k = 0 THEN
                RETURN FALSE;
            END IF;
        END IF;

        IF exige_lugar = TRUE THEN    
		--RAISE NOTICE '[INFO] POSIBLE INICIO: comprobar la disponibilidad de lugares ';
            -- comprobar la disponibilidad de lugares
            j := 0;

            k := 0;
            
            LOOP
                EXIT WHEN j = cantLugares;

                puede := TRUE;
                
                i := 0;
                LOOP
                    EXIT WHEN i = duracion;
                                
                    v := valor_restriccion_lugar(lugares[j], periodo, dia, turno + i);

                    IF v > 0 THEN
                        puede := FALSE;
                        EXIT;
                    END IF;
                    i := i + 1;
                END LOOP;

                IF puede = TRUE THEN
                    k := k + 1;
                    EXIT;
                END IF;
                
                j := j + 1;
            END LOOP;

            IF k = 0 THEN
                RETURN FALSE;
            END IF;
        END IF;

        --RAISE NOTICE '[INFO] POSIBLE INICIO: comprobar la disponibilidad de otros recursos ';
        -- comprobar la disponibilidad de otros recursos

        gruporecursos := dame_lista_id_grupos_de_recursos_asignados(idplantilla);
        cantGrupoRecursos := matrix.get_length_of(gruporecursos,1);

	-- execute matrix.echo(gruporecursos);
	
	--RAISE NOTICE '[INFO] POSIBLE INICIO: cantGrupoRecursos = % ', cantGrupoRecursos;

        i := 0;
        LOOP
	    
            EXIT WHEN i = cantGrupoRecursos;

	    --RAISE NOTICE '[INFO] POSIBLE INICIO: ... grupo recursos %', i;

            recursos := dame_lista_id_otros_recursos_asignados(gruporecursos[i]);
            cantRecursos := matrix.get_length_of(recursos, 1);

            SELECT * INTO r FROM otro_grupo_de_recursos_para_actividad WHERE id = gruporecursos[i];

            -- me preocupo solo por lo imprescindible
            
            IF r.imprescindibles = TRUE THEN
            
                j := 0;
                t := 0; -- va a contar cuantos hay disponibles para luego compararlo contra los necesarios
                
                LOOP

                    EXIT WHEN j = cantRecursos;

                    malo := FALSE;
                    
                    LOOP
                        EXIT WHEN k = duracion;
                        v := valor_restriccion_otro_recurso(recursos[j], periodo, dia, turno + k);
                    
                        IF v > 0 THEN 
                            malo := TRUE;
                            EXIT;
                        END IF;
                        
                        k:= k + 1;
                    END LOOP;

                    IF malo = FALSE THEN
                        t := t + 1;
                    END IF;
                    
                    j := j + 1;
                END LOOP;

                -- si t no alcanza los necesarios entonces no se puede,
                -- y solo basta con que uno de los grupos de recursos 
                -- no sea satisfecho para decir que no se puede

                IF t < r.necesarios THEN
                    RETURN FALSE;
                END IF;
            END IF;
            
            i := i + 1;
        END LOOP;

        RETURN 1;
    END;
$$
LANGUAGE 'plpgsql';