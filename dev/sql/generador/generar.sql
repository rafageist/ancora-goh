-- drop function  generar(idesp VARCHAR, idnivel VARCHAR, idperiodo VARCHAR, forma BIGINT, parche boolean, compactar BIGINT, parche1 BOOLEAN, buscalugar BOOLEAN);
-- -----------------------------------------------------------------------------------
--      NOMBRE: generar
--        TIPO: funcion
-- DESCRIPCION: Genera el horario de una especialidad, nivel y periodo dados
--     EJEMPLO: SELECT  dame_cant_clasif();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION generar(idesp VARCHAR, idnivel VARCHAR, idperiodo VARCHAR, forma BIGINT, parche boolean, compactar BIGINT, parche1 BOOLEAN, buscalugar BOOLEAN) RETURNS void as
$$
    DECLARE
	v integer;
	ddia integer;
	tturno integer;
	dduracion integer;

	sePudoColocar boolean;
        cantAsigEnCurso INTEGER;
        cantActEnCurso INTEGER;
        cantCriterios INTEGER;
        cantMaxAct INTEGER;
        matrixAct VARCHAR[][];
        plantillasEnCurso VARCHAR[];
        asigEnCurso VARCHAR[];
        cantActs BIGINT[];
        temp VARCHAR[];
        idprofe VARCHAR;
        
        kk INTEGER;
        idxOrdenAct BIGINT[];

        -- contadores
        i BIGINT;
        j BIGINT;
        k INTEGER;
        l BIGINT;
        ll BIGINT;
        r RECORD;
        dia INTEGER;
        turno INTEGER;
        
        voyact BIGINT;
        cantCiclo1 BIGINT;
        cantCiclo2 BIGINT;
        varCiclo1 BIGINT;
        varCiclo2 BIGINT;
        listaid_clasif VARCHAR[];
        cantClasif BIGINT;
        nivel_actual BIGINT;
        clasif_actual BIGINT;
        TablaPrioriza NUMERIC[][];
        cantPriorizando BIGINT;
        CD INTEGER;
        CT INTEGER;

        -- parametros
        profesAsignados VARCHAR[];
        lugaresAsignados VARCHAR[];
        param_cantProfesAsignados BIGINT;
        param_cantLugaresAsignados BIGINT;
        param_cantRestProfe BIGINT;
        param_cantRestLugar BIGINT;
        idxAsigEnPriori BIGINT[];
        param_cantActAsig BIGINT;
        param_cantRestAsig BIGINT;
        param_cantRecursosImprescindibles BIGINT;
        param_cantRecursosAsignados BIGINT;
        niveles NUMERIC[][];
        criterios BOOLEAN[];

        -- colocando actividad
        grupos VARCHAR[];
        cantGrupos BIGINT;
        faltan BIGINT;
        rasignatura record;
        rdesglose record;
        rplantilla record;
        rperiodo record;
        rclasificacion record;
        ztodo INTEGER;
        fatan INTEGER;
        gruposEnCurso BOOLEAN[][][];
        listaidbrg VARCHAR[];
        grupompi BOOLEAN[][];
        idMaxPIGrupo BIGINT[][];
        maxColoca BIGINT;
        diaBooleano BOOLEAN[];
        posibXDia BIGINT[];

        ya BOOLEAN[];
        zp INTEGER[][];

        -- para la optimizacion profesor
        actual_profe VARCHAR[];
        actual_profe_cant BIGINT[][];
        actual_profe_priori BIGINT[][];
        actual_profe_grupos BIGINT[][];
        actual_profe_cgrupos BIGINT[];
        
        -- cada profesor tiene un rango posible de colocacion de la actividad distinto
        -- para cada grupoXclasif
        
        actual_profe_rango INTEGER[][][]; -- profe i, grupo j, actual_profe[i][j][1] = ini actual_profe[i][j][2] = fin
        actual_profe_misact BOOLEAN[][][];

        miDiaSegunProfe INTEGER[];
        
        g BIGINT;
        min INTEGER;
        gmin BIGINT;
        c INTEGER;
        tt INTEGER;
        xasig BOOLEAN[];

        cantActualProfe BIGINT;

        rangobusqueda TRango;

        temp_rango TRango;
        grupos_rangos INTEGER[][];
        actual_profe_gastos INTEGER[];

        idClasifSgte VARCHAR;
        dia_anterior INTEGER;
        esta_colocada BOOLEAN;

        sentido INTEGER;
        ini_sentido INTEGER;
        fin_sentido INTEGER;
        step_sentido INTEGER;
        zona INTEGER;

        etapa INTEGER;
        maxDia INTEGER;
        CicloIni INTEGER;
        CicloFin INTEGER;
        
        puedo BOOLEAN;
        t INTEGER;

        idlugar varchar;
        pos BIGINT;
        lugs VARCHAR[];
        
        lugs_priori BIGINT[];
        cantLugs BIGINT;
        lgi VARCHAR;
        
        grupo_anterior BIGINT;

        mejor_profe BIGINT;
        minim BIGINT;

        dias_buenos_profe INTEGER[];
        -- brgsEnCurso VARCHAR[];
        -- cantBrgsEnCurso BIGINT;

        lugares VARCHAR[];
        cantLugares BIGINT;
        maxpriori BIGINT;

        lugsarround VARCHAR[];
        idrecursos VARCHAR[];
        gruporecursos VARCHAR[];
        cantGrupoRecursos BIGINT;
        gr BIGINT;
        rgruporecurso record;

        cantListaIdBrg BIGINT;

    ih INTEGER;
    es_higienico_profe BOOLEAN;
    es_higienico_brg BOOLEAN;
	
    BEGIN

        cantCriterios := 9;
        cantActEnCurso := 0;

	RAISE NOTICE '[INFO] Comenzando...';
	
        -- brgsEnCurso := dame_lista_id_brigadas_de_esp(idesp, idnivel);
        -- cantBrgsEnCurso := matrix.get_length_of(brgsEnCurso);
                
        -- aqui se obtienen los ids de las asignaturas implicadas en la generacion
        -- ordenadas segun la cantidad de actividades que tienen en el periodo
        -- especificado "idperiodo"

        RAISE NOTICE '[INFO] Obteniendo listado de asignaturas implicadas...';
        
        asigEnCurso := dame_asig_segun_esp_anno_per(idesp, idnivel, idperiodo);

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------

        cantAsigEnCurso := matrix.get_length_of(asigEnCurso,1 );
        
        CD := dame_cant_dias(idperiodo);
        CT := dame_cant_turnos(idperiodo);
        
        SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;
        CD := rperiodo.cant_dias;
        CT := rperiodo.cant_turnos;

        cantMaxAct := max_cant_act(idesp, idnivel, idperiodo);
        matrixAct := matrix.create_string_matrix(cantAsigEnCurso, cantMaxAct);


        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------

        RAISE NOTICE '[INFO] Preparando matrix de actividades ...';
        -- preparando la matrix de actividades
        i := 1;
        LOOP
            j := 1;
            
            k := dame_cant_act(asigEnCurso[i],idperiodo);

            cantActs[i] := k;
            
            cantActEnCurso := cantActEnCurso + k;

            temp := dame_clasif_de_asignaturas(asigEnCurso[i],idperiodo);
            LOOP
                matrixAct[i][j] := temp[j];
                EXIT WHEN j = cantActs[i];
                j := j + 1;
            END LOOP;
            EXIT WHEN i = cantAsigEnCurso;
            i := i + 1;
        END LOOP;
        
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
        
	RAISE NOTICE '[INFO] Cantidad de actividades en curso % ...', cantActEnCurso;
        -- hasta este momento se ha obtenido lo siguiente

        /*
         i  asigEnCurso[i]  cantActs[i]  matrixAct[i]
         -----------------------------------------
         1  matematica      3            {"conf","conf","cp"}
         2  fisica          2            {"conf","cp"}
         3  ingles          1            {"conf"} 
        */
        
        voyact := 0;

        listaid_clasif := dame_lista_id_clasif();
        cantClasif := matrix.get_length_of(listaid_clasif, 1);
        
        IF forma = 1 THEN
            cantCiclo2 := cantMaxAct;
            cantCiclo1 := cantClasif;
        ELSE
            cantCiclo1 := cantMaxAct;
            cantCiclo2 := cantClasif;        
        END IF;


        RAISE NOTICE '[INFO] Generando ...';
        -- !!! GENERANDO !!!


        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------    

        LOOP
            varCiclo1 := 0;
            LOOP
		varCiclo1 := varCiclo1 + 1;
		EXIT WHEN varCiclo1 > cantCiclo1;
		
                varCiclo2 :=0;
                LOOP
		    varCiclo2 := varCiclo2 + 1;
		    EXIT WHEN varCiclo2 > cantCiclo2;
		    
                    IF forma = 1 THEN
                         nivel_actual := varCiclo2;
                        clasif_actual := varCiclo1;
                    ELSE
                         nivel_actual := varCiclo1;
                        clasif_actual := varCiclo2;    
                    END IF;


		    RAISE NOTICE '[INFO] Calculando cantidad de actividades a priorizar ...';
                    -- calcular la cantidad de actividades a priorizar

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
        
                    cantPriorizando :=0;
                    i := 1;
                    plantillasEnCurso := '{}'::VARCHAR[];
                    idxAsigEnPriori := '{}'::BIGINT[];
                    LOOP
                        IF matrixAct[i][nivel_actual] = listaid_clasif[clasif_actual] THEN
                            cantPriorizando := cantPriorizando + 1;
                            plantillasEnCurso := array_append(plantillasEnCurso,dame_id_plantilla(asigEnCurso[i], idperiodo, nivel_actual));
                            idxAsigEnPriori := array_append(idxAsigEnPriori,i);
                        END IF;
                        EXIT WHEN i = cantAsigEnCurso;
                        i := i + 1;
                    END LOOP;

		    RAISE NOTICE '[INFO] Llenando la tabla prioriza con % actividades...', cantPriorizando;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
		    CONTINUE WHEN cantPriorizando = 0;
		    
                    -- Llenando la tabla prioriza
                    TablaPrioriza := matrix.create_numeric_matrix(cantPriorizando, 9);

                    i := 1;
                    LOOP
                        -- Cantidad de profesores asignados
                        param_cantProfesAsignados := dame_cant_profes_asignados(plantillasEnCurso[i]);
                        
                        -- Cantidad de restricciones de los profesores asignados
                        profesAsignados := dame_lista_id_profes_asignados(plantillasEnCurso[i]);
                        param_cantRestProfe := 0;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
                        j := 0;
                        LOOP
                            EXIT WHEN j = param_cantProfesAsignados;
                            param_cantRestProfe := param_cantRestProfe + dame_cant_rest_profe(profesAsignados[j], idperiodo);
                            j := j + 1;
                        END LOOP;

                        -- Cantiad de lugares asignados
                        param_cantLugaresAsignados := dame_cant_lugares_asignados(plantillasEnCurso[i]);

                        -- Cantidad de restricciones de los profesores asignados
                        lugaresAsignados := dame_lista_id_lugares_asignados(plantillasEnCurso[i]);
                        param_cantRestLugar := 0;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
                        j := 0;
                        LOOP
                            EXIT WHEN j = param_cantLugaresAsignados;
                            param_cantRestLugar := param_cantRestLugar + dame_cant_rest_profe(lugaresAsignados[j], idperiodo);
                            j := j + 1;
                        END LOOP;

                        -- Cantidad de actividades de la asignatura
                        param_cantActAsig := cantActs[idxAsigEnPriori[i]];
                        
                        -- Cantidad de otros recursos imprescindibles
                        param_cantRecursosImprescindibles := dame_cant_otros_recursos_imprescindibles(plantillasEnCurso[i]);
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------                        
                        -- Cantidad de otros recursos asignados
                        param_cantRecursosAsignados := dame_total_otros_recursos_asignados(plantillasEnCurso[i]);
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------                        
                        -- Cantidad de restricciones de la asignatura
                        param_cantRestAsig := dame_cant_rest_asignatura(asigEnCurso[idxAsigEnPriori[i]], idperiodo);

                        -- Rellenando la tabla
                        
                        TablaPrioriza[i][1] := param_cantProfesAsignados;          
                        TablaPrioriza[i][2] := param_cantRestProfe;
                        TablaPrioriza[i][3] := param_cantLugaresAsignados;
                        TablaPrioriza[i][4] := param_cantRestLugar;
                        TablaPrioriza[i][5] := param_cantActAsig;
                        TablaPrioriza[i][6] := param_cantRecursosImprescindibles;
                        TablaPrioriza[i][7] := param_cantRecursosAsignados;
                        TablaPrioriza[i][8] := param_cantRestAsig;
                        TablaPrioriza[i][9] := 0;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
			RAISE NOTICE '% - % % % % % % % % - %',i,TablaPrioriza[i][1],TablaPrioriza[i][2],TablaPrioriza[i][3],TablaPrioriza[i][4],TablaPrioriza[i][5],TablaPrioriza[i][6],TablaPrioriza[i][7],TablaPrioriza[i][8],TablaPrioriza[i][9];
                        EXIT WHEN i = cantPriorizando;
                        i := i + 1;
                    END LOOP;

		    RAISE NOTICE '[INFO] Tabla obtenida:';
		    EXECUTE matrix.echo(TablaPrioriza);
		    RAISE NOTICE '[INFO] Priorizando actividad a colocar ...';	
		    	

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------		    
                    -- Priorizando la actividad a colocar
                    criterios := '{t,f,t,f,f,f,t,f}'::BOOLEAN[];
                    niveles:= prioriza.nivelar_matrix(TablaPrioriza, criterios);

		    RAISE NOTICE '[INFO] Calculando totales horizontales ...';	
		    
                    -- calculando totales horizontales
                    -- nota: los totales se colocan en la ??ma columna de la TablaPrioriza
                    i := 1;
                    LOOP
                        k := 0; -- va a llevar la suma
                        j := 1;
                        LOOP
                            k := k + niveles[i][j];
                            EXIT WHEN j=8;
                            j := j + 1;
                        END LOOP;
                        -- La ??ma columna puede variar si se agregan nuevos par?tros
                        TablaPrioriza[i][9] := k;
                        EXIT WHEN i = cantPriorizando;
                        i := i + 1;
                    END LOOP;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------                    
                    -- Determino el orden de prioridad para colocar las actividades que entraron en juego
                    -- El orden se determina seg??os totales horizontales calculados, de menor a mayor

                    -- Inicializo el arreglo que va a tener el orden
                    
                    idxOrdenAct := '{}'::INTEGER[];

		    RAISE NOTICE '[INFO] Ordenando totales horizontales ...';
                    -- Al principio idxOrdenAct[i] = i
                    i := 1;
                    LOOP
                        idxOrdenAct := array_append(idxOrdenAct,i);
                        EXIT WHEN i = cantPriorizando;
                        i := i + 1;
                    END LOOP;

                    -- Ordeno la novena columna
                    i := 1;
                    LOOP
                        EXIT WHEN i = cantPriorizando;

                        j := i + 1;

                        LOOP
                            IF TablaPrioriza[idxOrdenAct[i]][9] > TablaPrioriza[idxOrdenAct[j]][9] THEN
                                -- Intercambio
                                                               k := TablaPrioriza[idxOrdenAct[j]][9];
                                TablaPrioriza[idxOrdenAct[j]][9] := TablaPrioriza[idxOrdenAct[i]][9];
                                TablaPrioriza[idxOrdenAct[i]][9] := k;

                                             k := idxOrdenAct[j];
                                idxOrdenAct[j] := idxOrdenAct[i];
                                idxOrdenAct[i] := k;
                            END IF;
                            
                            EXIT WHEN j = cantPriorizando;
                            j := j + 1;
                        END LOOP;
                        
                        i := i + 1;
                    END LOOP;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
		    RAISE NOTICE '[INFO] Determinando clasificacion siguiente ...';

                    -- Determinando la clasificacion de actividad siguiente
                    
                    idClasifSgte := '';

                    i := clasif_actual + 1;
                    LOOP
                        EXIT WHEN i > cantClasif;
                        
                        j :=1;
                        LOOP
                            IF matrixAct[j][nivel_actual] = listaid_clasif[i] THEN
                                idClasifSgte := listaid_clasif[i];
                                i := cantclasif;
                                EXIT;
                            END IF;
                            EXIT WHEN j = cantAsigEnCurso;
                            j := j + 1;
                        END LOOP;
                        
                        i := i + 1;
                    END LOOP;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
                    RAISE NOTICE '[INFO] Colocando actividad ...';
                    -- -------------------------------------------
                    -- Colocando la actividad 

                    SELECT * INTO rclasificacion FROM clasificacion_de_actividad WHERE id = listaid_clasif[clasif_actual];
                    SELECT valor INTO r FROM zpriori WHERE clasificacion = listaid_clasif[clasif_actual] AND periodo = idperiodo ORDER By valor DESC LIMIT 1 OFFSET 0;

		    ztodo := r.valor;
            
                    posibXDia := matrix.create_numeric_list(CD);

		    RAISE NOTICE '      [INFO] obteniendo zpriori ...';
		    
                    zp := dame_zpriori(rclasificacion.id, idperiodo);

                    IF ztodo IS NULL THEN
                        ztodo := 1;
                    END IF;

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------		    
                    i := 1;
                                        
                    LOOP   

			RAISE NOTICE '      [INFO] colocando actividad i = % ',i;

			
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------                        
                        SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = plantillasEnCurso[idxOrdenAct[i]];
                        SELECT * INTO rdesglose FROM desglose WHERE id = rplantilla.desglose;
                        SELECT * INTO rasignatura FROM asignatura WHERE id = rdesglose.asignatura;


			maxDia := CD -((cantActs[i] - nivel_actual) - compactar) * IIf(rdesglose.respetar_orden, 1, 0);

                        sentido := 2;
                        dia_anterior := 0;

                        grupos := dame_grupos(idesp, idnivel, rplantilla.clasificacion);
                        
                        cantGrupos := matrix.get_length_of(grupos,1);

                        idMaxPIGrupo := matrix.create_numeric_matrix(cantGrupos,CD);
                        
                        faltan := cantGrupos;

                        gruposEnCurso := matrix.create_boolean_cube(cantGrupos, CD, CT);

                        j := 1;
                        LOOP
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------                        
			    RAISE NOTICE '      [INFO] colocando por grupo j = % ',j;

			    RAISE NOTICE '      [INFO] obteniendo listaidbrg';
                            listaidbrg := dame_lista_id_brigadas_de_grupo(grupos[j]);

			    RAISE NOTICE '      [INFO] obteniendo grupompi';
                            grupompi := and_mpi(listaidbrg,CD,CT,rplantilla.id,0,TRUE);

			    RAISE NOTICE '      [INFO] obteniendo gruposEnCurso';
                            gruposEnCurso := matrix.set_matrix_in_cube(grupompi, j);

                            RAISE NOTICE '      [INFO] and mpi a grupompi';
                            grupompi := and_mpi(listaidbrg,CD,CT,rplantilla.id,0,FALSE);

			    RAISE NOTICE '      [INFO] calculando idmaxpigrupo';

                            k := 1;

                            LOOP
				--RAISE NOTICE '      [INFO] calculando idmaxpigrupo CD = % k = %', CD, k;
				--RAISE NOTICE '            obteniendo dia booleano';
                                diaBooleano := matrix.extract_boolean_column(grupompi, k);
                                --RAISE NOTICE '            obteniendo maxColoca';
                                --RAISE NOTICE '            	diaBooleano = ';

                                --execute matrix.echo(diaBooleano);
                                
                                maxColoca := get_max_colocation(diaBooleano, rclasificacion.duracion);

                                --RAISE NOTICE '            maxColoca = %', maxColoca;

                                idMaxPIGrupo[j][k] := maxColoca;

                                IF posibXDia[k] > maxColoca or k = 1 THEN
                                    posibXDia[k] = maxColoca;
                                END IF;
                                
                                EXIT WHEN k = CD;
                                k := k + 1;
                            END LOOP;
                            
                            EXIT WHEN j = cantGrupos;
                            j := j + 1;
                        END LOOP;
                        
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
			RAISE NOTICE '       [INFO] calculo de los posibles turnos optimos en zonapriori ...';
                        -- calculo de los posibles turnos optimos en zonapriori

                        IF parche1 = TRUE THEN
				RAISE NOTICE '       [INFO] busco los turnos optimos de los grupos por dias ...';
                            -- busco los turnos optimos de los grupos por dias

                            j := 1;
                            LOOP
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------                            
				RAISE NOTICE '       [INFO] calculo las posibilidades para todos los grupos ...';
                                -- calculo las posibilidades para todos los grupos

                                -- busco el m?mo
                                ya := matrix.create_boolean_list(cantGrupos);

                                k := 1;
                                LOOP
                                    
                                    l := 1;
                                    LOOP
                                        min := CT + 1;
                                        gmin := 0;

					RAISE NOTICE '       [INFO] busco el grupo con el minimo de posibilidades optimas (en zpriori = 1) ...';
                                        -- busco el grupo con el minimo de posibilidades optimas (en zpriori = 1)

                                        g := 1;
                                        LOOP
                                            c := 0;
                                            IF ya[g] = FALSE THEN

                                                t:=1;
                                                LOOP 
                                                    IF gruposEnCurso[g][j][t] = TRUE and zp[j][t] = 1 THEN
                                                        c := c + 1;
                                                    END IF;
                                                    
                                                    EXIT WHEN t = CT;
                                                    t := t + 1;
                                                END LOOP;

                                                IF min > c THEN
                                                    min := c;
                                                    gmin := g;
                                                END IF;
                                            END IF;
                                            
                                            EXIT WHEN g = cantGrupos;
                                            g := g + 1;
                                        END LOOP;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
					RAISE NOTICE '       [INFO] busco la primera posibilidad para el grupo encontrado ...';
                                        -- busco la primera posibilidad para el grupo encontrado

                                        t := 1;
                                        LOOP 
                                            IF gruposEnCurso[gmin][j][t] = TRUE and zp[j][t] = 1 THEN
                                                idMaxPIGrupo[gmin][j] := t;
                                                ya[gmin] = TRUE;

                                                -- deshabilito para todos los grupos, el tiempo ocupado por el grupo encontrado
                                                -- para que los dem?turnos ??mos no se solapen con los encontrados
                                                g := 1;
                                                LOOP
                                                    tt := 1;
                                                    LOOP

                                                        gruposEnCurso[g][t + tt - 1] = FALSE;
                                                        
                                                        EXIT WHEN tt = rclasificacion.duracion;
                                                        tt := tt + 1;
                                                    END LOOP;
                                                    
                                                    EXIT WHEN g = cantGrupos;
                                                    g := g + 1;
                                                END LOOP;
                                                
                                                t := CT;
                                            END IF;

                                            EXIT WHEN t = CT;
                                            t := t + 1;
                                        END LOOP;
                                        
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
                                        EXIT WHEN l = cantGrupos;
                                        l := l + 1;
                                    END LOOP;


                                    EXIT WHEN k = cantGrupos;
                                    k := k + 1;
                                END LOOP;
                                
                                EXIT WHEN j = CD;
                                j := j + 1;
                            END LOOP;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
			    RAISE NOTICE '       [INFO] actualizo posibXDias ...';
                            -- actualizo posibXDias
                            
                            j := 1;
                            LOOP
                                posibXDia[j] := 0;

                                g := 1;
                                
                                LOOP
                                    IF idMaxPIGrupo[g][j] <> 0 THEN
                                        posibXDia[j] := posibXDia[j] + 1;
                                    END IF;
                                    
                                    EXIT WHEN g = cantGrupos;
                                    g := g + 1;
                                END LOOP;
                                
                                EXIT WHEN j = CD;
                                j := j + 1;
                            END LOOP;

                        END IF;

                        xasig := matrix.create_boolean_list(CT);

			RAISE NOTICE '       [INFO] empieza la colocacion ...';
                        -- empieza la colocacion                      
                        grupo_anterior := 0; -- verificar que el grupo_anterior puede ser el mismo que j

                        
                        
                        j := 1;
                        LOOP
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
			    sePudoColocar := false;

			    RAISE NOTICE '[INFO] Asig: %,%', rasignatura.descripcion, rasignatura.id;
			    RAISE NOTICE '[INFO] Clasif: %,%', rclasificacion.descripcion, rclasificacion.id;
			    RAISE NOTICE '[INFO] Colocando plantilla %', rplantilla.id;
			    
			    RAISE NOTICE '[INFO] Colocando grupo %: %',j,grupos[j];
			    
                            listaidbrg := dame_lista_id_brigadas_de_grupo(grupos[j]);

                            cantListaIdBrg := matrix.get_length_of(listaidbrg, 1);
                            
                            grupompi := and_mpi(listaidbrg,CD,CT,rplantilla.id,0,FALSE);

			    RAISE NOTICE ' grupompi = ';
                            execute matrix.echo(grupompi);

			    RAISE NOTICE '       [INFO] calculo el rango de busqueda ...';
                            -- calculo el rango de busqueda
                            
                            rangobusqueda := rango(rplantilla.id, grupos[j], rdesglose.min_dias, rdesglose.max_dias);

			    IF rplantilla.exige_profesor THEN
			    
			    RAISE NOTICE '       [INFO] OPTIMIZACION DEL PROFESOR ...';
                            -- ----------------------------------------------------------------------------
                            -- OPTIMIZACION DEL PROFESOR
                            -- ----------------------------------------------------------------------------
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
				RAISE NOTICE '       [INFO] Obtengo los profesores asignados al grupo actual para la actividad actual ...';
                                -- Obtengo los profesores asignados al grupo actual para la actividad actual

                                actual_profe := dame_lista_id_profes_asignados_al_grupo(rplantilla.id, grupos[j]);
                                
                                cantActualProfe := matrix.get_length_of(actual_profe, 1);
                                
				RAISE NOTICE '       [INFO] cantActualProfe = % ', cantActualProfe;
				
				IF cantActualProfe > 0 THEN
					actual_profe_cant := matrix.create_numeric_matrix(cantActualProfe,CD);
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
					-- cantidad de turnos ocupados en actividades por dia

					RAISE NOTICE '       [INFO] se calcula el gasto total en el periodo para cada profesor ...';
					  
					-- se calcula el gasto total en el periodo para cada profesor

					l := 1;
					LOOP
					    actual_profe_gastos[l] := 0;
					    FOR r IN SELECT * FROM actividad WHERE dame_periodo_plantilla_de_actividad(plantilla) = rperiodo.id AND profesor = actual_profe[l] LOOP
						k := dame_duracion_clasif_de_actividad(dame_clasif_plantilla_de_actividad(rplantilla.id));
						actual_profe_cant[l][r.dia] := actual_profe_cant[l][r.dia] + k;
						actual_profe_gastos[l] := actual_profe_gastos[l] + k;
					    END LOOP;

					    EXIT WHEN l >= cantActualProfe;
					    l := l + 1;
					END LOOP;
					
					-- si hay mas de un profesor se selecciona el mejor con el metodo prioriza 
					-- dado los criterios:
					--      gasto (min)
					--      prioridad segun el seguridad.usuario (min)
					-- pero como la lista de profesores ya viene ordenada por prioridad
					-- entonces basta con buscar el primer profesor con el minimo gasto
					
					RAISE NOTICE '       [INFO] buscar el primer profesor con el minimo gasto ...';
					
					mejor_profe := 1;
					minim := -1;
					IF cantActualProfe > 1 THEN
					    l := 1;
					    LOOP
						IF minim = -1 OR minim > actual_profe_gastos[l] THEN
						    minim := actual_profe_gastos[l];
						    mejor_profe := l;
						END IF;
						
						EXIT WHEN l >= cantActualProfe;
						l := l + 1;
					    END LOOP;
					    
					END IF;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------					
					-- una vez obtenido el mejor profesor, entonces se crea una lista
					-- con los mejores dias para el profesor en el rango posible de colocacion
					-- el dia optimo esta dado por el dia de mas gasto
					-- pues la idea es minimizar la cantidad de dias que debe venir
					-- el profesor a dar clases (este parametro puede ser configurable)

					RAISE NOTICE '       [INFO] determinando los mejores dias para el profesor en el rango posible de colocacion ...';
								 
					dias_buenos_profe := matrix.create_numeric_list(CD);
					
					-- la principio es consecutivo
					l := 1;
					LOOP
					    dias_buenos_profe[l] := l;
					    EXIT WHEN l = CD;
					    l := l + 1;
					END LOOP;

					RAISE NOTICE '       [INFO] luego ordeno en el rango, de mayor a menor ...';
					
					-- luego ordeno en el rango, de mayo a menor
					
					l := rangobusqueda.ini;
					LOOP
					    k := l + 1;
					    LOOP
						IF actual_profe_cant[mejor_profe][l] < actual_profe_cant[mejor_profe][k]  THEN
						    t := actual_profe_cant[mejor_profe][l];
						    actual_profe_cant[mejor_profe][l] := actual_profe_cant[mejor_profe][k];
						    actual_profe_cant[mejor_profe][k] := t;
						    
						    t:= dias_buenos_profe[l];
						    dias_buenos_profe[l] := dias_buenos_profe[k];
						    dias_buenos_profe[k] := t;
						END IF;
											
						EXIT WHEN k >= rangobusqueda.fin;
						k := k + 1;
					    END LOOP;
					    EXIT WHEN l >= rangobusqueda.fin - 1;
					    l := l +1;
					END LOOP;
                                END IF;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
				RAISE NOTICE 'dias_buenos_profe =';
				execute matrix.echo(dias_buenos_profe);
                                
                                RAISE NOTICE '       [INFO] terminada la OPTIMIZACION DEL PROFESOR ...';
				-- ----------------------------------------------------------------------------

				-- deshabilitar los turnos donde el profesor no puede dar clases

				ddia :=1;
				LOOP
					tturno :=1;
					LOOP
						dduracion := 1;
						LOOP
							EXIT WHEN grupompi[ddia][tturno] = false;
							
							v := valor_restriccion_profesor(actual_profe[mejor_profe], rperiodo.id, ddia, tturno + dduracion - 1);
							IF v > 0 THEN
								grupompi[ddia][tturno] := false;
								EXIT;
							END IF;
							EXIT WHEN dduracion >= rclasificacion.duracion;
							dduracion := dduracion + 1;
						END LOOP;
						EXIT WHEN tturno >= CT;
						tturno := tturno + 1;
					END LOOP;
					EXIT WHEN ddia >= CD;
					ddia := ddia + 1;
				END LOOP;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
                            
                            END IF;
                            
                            IF j = 1 AND idClasifSgte <> '' AND idClasifSgte <> rclasificacion.id THEN
                                IF parche = FALSE OR dia_anterior <= (CD / 2) -1 THEN
                                    dia_anterior := dia_minimo_impacto (idClasifSgte, idesp, idnivel, rperiodo.id, rangobusqueda.ini, rangobusqueda.fin);
                                    -- dia_anterior := dia_minimo_impacto (idClasifSgte, idesp, idnivel, rperiodo.id, grupos_rangos[j][1], grupos_rangos[j][2]);
                                END IF;
                            END IF;

                            RAISE NOTICE '       [INFO] verificando que la actividad no este asignada ...';
                            -- verificando que la actividad no este asignada
                            
                            SELECT count(id) as cant INTO r FROM actividad WHERE plantilla = rplantilla.id AND grupo = grupos[j];

                            esta_colocada := FALSE;
                            IF r.cant > 0 THEN
                                esta_colocada := TRUE;
                                sePudoColocar := TRUE;
                            END IF;

                            IF esta_colocada = FALSE THEN
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
				RAISE NOTICE '       [INFO] actualiza sentido para mayor combinacion ...';
                                -- actualiza sentido para mayor combinacion

                                IF sentido = 2 THEN
                                    sentido := 1;
                                    ini_sentido := 1;
                                    fin_sentido := CT;
                                    step_sentido := 1;
                                ELSE
                                    sentido = 2;
                                    ini_sentido := CT;
                                    fin_sentido := 1;
                                    step_sentido := -1;
                                END IF;


                                /*
                                'BUSQUEDA POR ZONAS
                                    'Polietapas
                                    'zona:
                                    '1-Zona priori
                                    '2-Zona todo
                                    'ciclo por cada etapa
                                    '1- Mismo Dia
                                    '2- Posib>=faltan
                                    '3- Cualquier dia
                                */

				RAISE NOTICE '       [INFO] busqueda por zonas ...';
                                zona := 1;
                                LOOP
                                
                                    etapa := 1;
                                    LOOP
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------                                    
					-- preparo el rango de busqueda por dia
					IF etapa = 1 And dia_anterior > rangobusqueda.ini THEN
					    CicloIni := dia_anterior;
					    CicloFin := dia_anterior;
					ELSE
					    CicloIni := rangobusqueda.ini;
					    CicloFin := rangobusqueda.fin;

					    IF CicloFin > maxDia THEN 
						CicloFin := maxDia;
					    END IF;
					END IF;

                                        kk := CicloIni;
                                        LOOP
						k := kk;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
						idprofe = NULL;

						es_higienico_profe := TRUE;

						RAISE NOTICE '       [INFO] optimizando al profesor e higiene para profesor ...';
						IF rplantilla.exige_profesor = TRUE THEN
						    
						    -- optimizando al profesor
						    k := dias_buenos_profe[kk];
						    idprofe := actual_profe[mejor_profe];
							
						    -- higiene para profesor
						    es_higienico_profe := es_higienico_profesor(idprofe, rplantilla.id, k);
							
						END IF;

						IF es_higienico_profe = TRUE THEN
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
							IF rplantilla.exige_profesor = FALSE OR NOT (rplantilla.exige_profesor = TRUE AND idprofe IS NULL) THEN 
											
								RAISE NOTICE '       [INFO] higiene para brigada ...';
								-- ----------------------
								-- higiene para brigadas

								es_higienico_brg := TRUE;
								
								ih := 1;
								LOOP
								    es_higienico_brg := es_higienico_brigada(listaidbrg[ih], rplantilla.id, k);
									
								    EXIT WHEN ih >= cantListaIdBrg OR es_higienico_brg = FALSE;
								    ih := ih + 1;
								END LOOP;

								IF es_higienico_brg = TRUE THEN
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
									-- ----------------------
									RAISE NOTICE '       [INFO] colocando ...';
											    
									puedo := TRUE;

									IF rdesglose.respetar_orden = FALSE AND rdesglose.mismo_dia = FALSE THEN

										SELECT * INTO r FROM actividad WHERE grupo = grupos[j] AND dame_desglose_de_plantilla(plantilla) = rdesglose.id;

										IF r.grupo = grupos[j] THEN
										    puedo := FALSE;
										END IF;

									END IF;

									RAISE NOTICE ' [------] k = % puedo = %', k, puedo;
												
									IF k <> 0 AND puedo = TRUE THEN

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------

										IF posibXDia[k] >= faltan OR etapa = 3 THEN

											t := ini_sentido;

											LOOP

											    IF parche1 = TRUE THEN
												IF etapa = 1 AND idMaxPIGrupo[j][k] <> 0 THEN
												    IF grupompi[k][idMaxPiGrupo[j][k]] = TRUE AND zp[k][idMaxPiGrupo[j][k]] = zona THEN
													t := idMaxPiGrupo[j][k];
												    END IF;
												END IF;
											    END IF;
							    
											    
											    IF NOT (grupompi[k][t] = TRUE AND etapa = 1 AND xasig[t] = TRUE) THEN 
											      -- CONTINUE WHEN grupompi[k][t] = TRUE AND etapa = 1 AND xasig[t] = TRUE;
											    
												    IF grupompi[k][t] = TRUE THEN
												    
													IF etapa = 2 AND dia_anterior = 0 THEN
													    xasig[t] = TRUE;
													END IF;
												  

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------

													    IF rplantilla.exige_lugar = TRUE THEN

														RAISE NOTICE '       [INFO] asignando lugar ...';

														-- asignando lugar
													    
														idlugar := '';
														
														-- preparando...
														
														lugares := dame_lista_id_lugares_asignados(rplantilla.id);				

														RAISE NOTICE '            --------- % lugares posibles para la plantilla %',matrix.get_length_of(lugares, 1), rplantilla.id;
														RAISE NOTICE '       [INFO] lugares filtrados, extraer los que no permanecen en el turno ';

														-- extraer los que no permanecen en el turno

														lugares := extraer_lugares_que_no_permanecen(lugares, rperiodo.id, k, t, rclasificacion.duracion);

														RAISE NOTICE '       [INFO] lugares filtrados, extraer los lugares que no tienen capacidad ';
														
														-- extraer los lugares que no tienen capacidad

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------														lugares := quita_segun_capacidad(listaidbrg, lugares);

														RAISE NOTICE '       [INFO] lugares filtrados, extraer los lugares prohibidos ';
														
														-- extraer los lugares prohibidos

														lugares := quita_segun_prohibidos(('{' || quote_literal(grupos[j]) || '}')::VARCHAR[], lugares);

														cantLugares := matrix.get_length_of(lugares, 1);

														RAISE NOTICE '       [INFO] lugares filtrados!!!, quedaron % ', cantLugares;

														IF cantLugares > 0 THEN
															
															-- colocando ...   
															-- tratar de colocar en el aula fija
															pos := -1;
															
															IF EXISTS(SELECT * FROM lugar_fijo WHERE grupo = grupos[j]) THEN
																SELECT * INTO r FROM lugar_fijo WHERE grupo = grupos[j];
																pos := matrix.get_pos_of(lugares, r.lugar);
																IF pos > -1 THEN
																    idlugar := lugares[pos];
																END IF;
															END IF;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------															    
															-- buscar el mejor lugar

															-- buscar segun prioridades
																									
															IF pos = -1 THEN

															    -- obtengo la maxima prioridad dada a los lugares para la plantilla de actividad actual
															    
															    SELECT * INTO r FROM lugar_para_actividad WHERE plantilla = rplantilla.id ORDER BY prioridad DESC LIMIT 1 OFFSET 0;
															   
															    maxpriori := r.prioridad;
															    
															    -- tanto para la busqueda por el lugar mas utilizado como para el mas cercano
															    -- se busca segun las prioridades dadas por el seguridad.usuario a los lugares 
															    
															    -- obtengo la prioridad de cada lugar
															    
															    lugs_priori := matrix.create_numeric_list(cantLugares);
															    
															    ll := 1;
															    LOOP
																SELECT * INTO r FROM lugar_para_actividad WHERE lugar = lugares[ll] AND plantilla = rplantilla.id;
																
																lugs_priori[ll] := r.prioridad;
																
																EXIT WHEN ll = cantLugares;
																ll := ll + 1;
															    END LOOP;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
															    -- comienza la colocacion
															    l := 1;
															    LOOP
																RAISE NOTICE '    seleccionando lugar prioridad % ', l;
																-- seleccionar los lugares que tienen la prioridad actual
																
																lugs := '{}'::VARCHAR[];
																
																ll := 1;
																LOOP
																    IF lugs_priori[ll] = l THEN
																	lugs := array_append(lugs, lugares[ll]);
																    END IF;
																    
																    EXIT WHEN ll = cantLugares;
																    ll := ll + 1;
																END LOOP;

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------																
																cantLugs := matrix.get_length_of(lugs, 1);

																RAISE NOTICE ' ------ % lugares con prioridad % ',cantLugs, l;
																EXECUTE matrix.echo(lugs);
																
																idlugar := '';
																
																
																IF cantLugs > 0 THEN
																	
																    -- asignando...
																    -- TODO: pensar en una variante conjunta entre el mas usado y mas cercano

																    IF buscalugar = FALSE THEN

																	RAISE NOTICE '- buscando lugar por prioridades ';
																    
																	-- el mas utilizado por el grupo actual
																	idlugar := busca_lugar_mas_utilizado_por(lugs, grupos[j], FALSE);

																	RAISE NOTICE '- lugar mas utilizado % ', idlugar;
																	
																	IF idlugar = '' THEN
																	    
																	    -- el menos utilizado por los demas
																	    idlugar := busca_lugar_menos_utilizado_por(lugs, grupos[j], TRUE);

																	    RAISE NOTICE '- lugar menos utilizado % ', idlugar;
																	    
																	    IF idlugar = '' THEN
																		-- se coge el primero
																		idlugar := lugs[1];
																		RAISE NOTICE '- .....EL PRIMERO !!!! % ', idlugar;
																	    END IF;
																	END IF;

																	RAISE NOTICE '- mejor lugar = %', idlugar;
																	
																    ELSE
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------																    
																	RAISE NOTICE '- buscando lugar mas cercano ';
																	-- ... el mas cercano
																	RAISE NOTICE '- dame_lugares_usados_alrededor_de ';
																	lugsarround := dame_lugares_usados_alrededor_de(listaidbrg, rperiodo.id, k, t);
																	RAISE NOTICE '- busca_lugar_menos_costoso ';
																	idlugar := busca_lugar_menos_costoso(lugs, lugsarround);
																	RAISE NOTICE '- lugar mas cercano = %', idlugar;
																    END IF;
																END IF;

																EXIT WHEN l = maxpriori OR idlugar <> '';
																l := l + 1;
															    END LOOP;
															END IF;
														END IF;
													END IF;   

													IF rplantilla.exige_lugar = FALSE OR NOT (rplantilla.exige_lugar = TRUE AND idlugar = '') THEN
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------														

														RAISE NOTICE '       [INFO] asignando otros recursos ...';
														-- asignando otros recursos
														
														idrecursos := '{}'::VARCHAR[];
														
														-- comprobar la disponibilidad de otros recursos

														gruporecursos := dame_lista_id_grupos_de_recursos_asignados(rplantilla.id);
														cantGrupoRecursos := matrix.get_length_of(gruporecursos,1);

														gr := 0;
														LOOP
														    EXIT WHEN gr = cantGrupoRecursos;
														    
														    SELECT * INTO rgruporecurso FROM otro_grupo_de_recursos_para_actividad WHERE id = gruporecursos[i];
														    
														    l := 0;
														    FOR r IN SELECT *, dame_uso_de_otro_recurso(otro_recurso) as uso, 
																	dame_costo_trasladar_recurso(otro_recurso, idlugar, rperiodo.id, k, t) as costo 
															     FROM otro_recurso_para_actividad WHERE grupo_de_recursos = gruporecursos[gr] 
															     ORDER BY prioridad, uso, costo 
															LOOP
															
															idrecursos := array_append(idrecursos, r.otro_recurso);
															l := l + 1;
															EXIT WHEN l = rgruporecurso.necesarios;
														    END LOOP;
														    
														    EXIT WHEN gr = cantGrupoRecursos;
														    gr := gr + 1;
														END LOOP;
													
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------													
														RAISE NOTICE '       [INFO] agregando la actividad ...';

														-- agregando la actividad
														IF rplantilla.exige_lugar = FALSE THEN
														    idlugar := NULL;
														END IF;  
														
														-- agregando la actividad
														IF rplantilla.exige_profesor = FALSE THEN
														    idprofe := NULL;
														END IF;                        

														execute matrix.echo(lugs);
														RAISE NOTICE '       plantilla = %',rplantilla.id;
														RAISE NOTICE '       profe = %',idprofe;
														RAISE NOTICE '       lugar = %',idlugar;
														RAISE NOTICE '       k = %',k;
														RAISE NOTICE '       t = %',t;
														RAISE NOTICE '       grupo = %',grupos[j];
														
														execute matrix.echo(idrecursos);

														RAISE NOTICE '	     momento = %', tiempo.moment_now();
														RAISE NOTICE '       creador = %', rasignatura.creador;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------																		
														EXECUTE agrega_actividad(rplantilla.id, idprofe, idlugar, k, t, grupos[j], idrecursos, tiempo.moment_now(), rasignatura.creador);

														sePudoColocar := true;
														
														-- --------------------------------------------
													ELSE
														RAISE NOTICE ' ---------------- no se pudo colocar por falta de lugar';
													END IF;
												END IF;
											END IF;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------											
										    EXIT WHEN t = fin_sentido or sePudoColocar = true;
										    t := t + step_sentido;
										END LOOP;
									    END IF;

									END IF;
								END IF;
							    RAISE NOTICE ' ****** [INFO CICLO] kk = % de CicloFin = % ', kk , CicloFin;
							
							ELSE
								RAISE NOTICE ' ---------------- no se pudo colocar por falta de profesor';
							END IF;
						END IF;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------						    
					    EXIT WHEN kk = CicloFin or sePudoColocar = true;
					    kk := kk + 1;
					    
					END LOOP;

					RAISE NOTICE ' ****** [INFO CICLO] etapa = % de 3  ', etapa;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------					
					EXIT WHEN etapa = 3 or sePudoColocar = true;
					etapa := etapa + 1;
				    END LOOP;

				    RAISE NOTICE ' ****** [INFO CICLO] zona = % de ztodo = %  ', zona, ztodo;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------				    
				    EXIT WHEN zona = ztodo or sePudoColocar = true;
				    zona := zona + 1;
				END LOOP;
			    END IF;
		    
			    IF sePudoColocar = false THEN
				RAISE NOTICE ' no se pudo colocar!!!!! ';
				-- TODO: registrar
			    END IF;
			    
			    RAISE NOTICE ' ****** [INFO CICLO] j = % de cantGrupos = %  ', j, cantGrupos;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------			    
			    EXIT WHEN j >= cantGrupos;
			    
			    grupo_anterior := j;
			    
			    j := j + 1;
			END LOOP;
			
			-- --------------------------------------------
			RAISE NOTICE ' ****** [INFO CICLO] i = % de cantPriorizando = %  ', i, cantPriorizando;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------			
			EXIT WHEN i = cantPriorizando;
			i := i + 1;
		    END LOOP;

		    RAISE NOTICE ' ****** [INFO CICLO] varCiclo2 = % de cantCiclo2 = %  ', varCiclo2, cantCiclo2;
		    
		    --EXIT WHEN varCiclo2 >= cantCiclo2;
		    
		    --varCiclo2 := varCiclo2 + 1;
		END LOOP;

                RAISE NOTICE ' ****** [INFO CICLO] varCiclo1 = % de cantCiclo1 = %  ', varCiclo1, cantCiclo1;
                
                --EXIT WHEN varCiclo1 >= cantCiclo1;
                --varCiclo1 := varCiclo1 + 1;
            END LOOP;

            RAISE NOTICE ' ****** [INFO CICLO] voyact = % de cantActEnCurso = %  ', voyAct, cantActEnCurso;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
	    voyAct := voyAct + 1;
	    
            EXIT WHEN voyact >= cantActEnCurso;
            
        END LOOP;
        
    END;
$$
LANGUAGE 'plpgsql'; 

/*
select generar('especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e',
'nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s',
'periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29',
1,
true,
3,
false,
false);
*/