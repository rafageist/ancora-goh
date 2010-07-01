-- -----------------------------------------------------------------------------------
--      NOMBRE: generar
--        TIPO: funcion
-- DESCRIPCION: Genera el horario de una especialidad, nivel y periodo dados
--     EJEMPLO: select  dame_cant_clasif();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION generar(idesp varchar, idnivel varchar, idperiodo varchar, forma bigint, parche bigint, compactar bigint, parche1 boolean, buscalugar boolean) returns void as
$$
    DECLARE
        cantAsigEnCurso integer;
        cantActEnCurso integer;
        cantCriterios integer;
        cantMaxAct integer;
        matrixAct varchar[][];
        plantillasEnCurso varchar[];
        asigEnCurso varchar[];
        cantActs bigint[];
        temp varchar[];
        idprofe varchar;
        
        kk integer;
        idxOrdenAct integer[];

        -- contadores
        i bigint;
        j bigint;
        k bigint;
        l bigint;
        ll bigint;
        r RECORD;
        dia integer;
        turno integer;
        
        voyact bigint;
        cantCiclo1 bigint;
        cantCiclo2 bigint;
        varCiclo1 bigint;
        varCiclo2 bigint;
        listaid_clasif varchar[];
        cantClasif bigint;
        nivel_actual bigint;
        clasif_actual bigint;
        TablaPrioriza numeric[][];
        cantPriorizando bigint;
        CD integer;
        CT integer;

        -- parametros
        profesAsignados varchar[];
        lugaresAsignados varchar[];
        param_cantProfesAsignados bigint;
        param_cantLugaresAsignados bigint;
        param_cantRestProfe bigint;
        param_cantRestLugar bigint;
        idxAsigEnPriori bigint[];
        param_cantActAsig bigint;
        param_cantRestAsig bigint;
        param_cantRecursosImprescindibles bigint;
        param_cantRecursosAsignados bigint;
        niveles numeric[][];
        criterios boolean[];

        -- colocando actividad
        grupos varchar[];
        cantGrupos bigint;
        faltan bigint;
        rasignatura record;
        rdesglose record;
        rplantilla record;
        rperiodo record;
        rclasificacion record;
        ztodo integer;
        fatan integer;
        gruposEnCurso boolean[][][];
        listaidbrg varchar[];
        grupompi boolean[][];
        idMaxPIGrupo bigint[][];
        maxColoca bigint;
        diaBooleano boolean[];
        posibXDia bigint[];

        ya boolean[];
        zp integer[][];

        -- para la optimizacion profesor
        actual_profe varchar[];
        actual_profe_cant bigint[][];
        actual_profe_priori bigint[][];
        actual_profe_grupos bigint[][];
        actual_profe_cgrupos bigint[];
        
        -- cada profesor tiene un rango posible de colocacion de la actividad distinto
        -- para cada grupoXclasif
        
        actual_profe_rango integer[][][]; -- profe i, grupo j, actual_profe[i][j][1] = ini actual_profe[i][j][2] = fin
        actual_profe_misact boolean[][][];

        miDiaSegunProfe integer[];
        
        g bigint;
        min integer;
        gmin bigint;
        c integer;
        tt integer;
        xasig boolean[];

        cantActualProfe bigint;

        rangobusqueda TRango;

        temp_rango TRango;
        grupos_rangos integer[][];
        actual_profe_gastos integer[];

        idClasifSgte varchar;
        dia_anterior integer;
        esta_colocada boolean;

        sentido integer;
        ini_sentido integer;
        fin_sentido integer;
        step_sentido integer;
        zona integer;

        etapa integer;
        maxDia integer;
        CicloIni integer;
        CicloFin integer;
        
        puedo boolean;
        t integer;

        idlugar bigint;
        pos bigint;
        lugs varchar[];
        
        lugs_priori bigint[];
        cantLugs bigint;
        lgi varchar;
        
        grupo_anterior bigint;

        mejor_profe bigint;
        minim bigint;

        dias_buenos_profe integer[];
        -- brgsEnCurso varchar[];
        -- cantBrgsEnCurso bigint;

        lugares varchar[];
        cantLugares bigint;
        maxpriori bigint;

        lugsarround varchar[];
        idrecursos varchar[];
        gruporecursos varchar[];
        cantGrupoRecursos bigint;
        gr bigint;
        rgruporecurso record;

        cantListaIdBrg bigint;

	ih integer;
	es_higienico boolean;

 
    BEGIN
        cantCriterios := 9;
        cantActEnCurso := 0;

        -- brgsEnCurso := dame_lista_id_brigadas_de_esp(idesp, idnivel);
        -- cantBrgsEnCurso := matrix.get_length_of(brgsEnCurso);
                
        -- aqui se obtienen los ids de las asignaturas implicadas en la generación
        -- ordenadas según la cantidad de actividades que tienen en el período
        -- especificado "idperiodo"
        
        asigEnCurso := dame_asig_segun_esp_anno_per(idesp, idnivel, idperiodo);

        cantAsigEnCurso := matrix.get_length_of(asigEnCurso,1 );
        
        CD := dame_cant_dias(idperiodo);
        CT := dame_cant_turnos(idperiodo);
        
        SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;
        CD := rperiodo.cant_dias;
        CT := rperiodo.cant_turnos;

        cantMaxAct := max_cant_act(idesp, idnivel, idperiodo);
        matrixAct := new_string_matrix(cantAsigEnCurso, cantMaxAct);
                
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

        
        -- !!! GENERANDO !!!
               
        LOOP
            varCiclo1 := 1;
            LOOP
                varCiclo2 :=1;
                LOOP

                    IF forma = 1 THEN
                         nivel_actual := varCiclo2;
                        clasif_actual := varCiclo1;
                    ELSE
                         nivel_actual := varCiclo1;
                        clasif_actual := varCiclo2;    
                    END IF;


                    -- calcular la cantidad de actividades a priorizar

                    cantPriorizando :=0;
                    i := 1;
                    plantillasEnCurso := '{}'::varchar;
                    idxAsigEnPriori := '{}'::bigint;
                    LOOP
                        IF matrixAct[i][nivel_actual] = listaid_clasif[clasif_actual] THEN
                            cantPriorizando := cantPriorizando + 1;
                            plantillasEnCurso := array_append(plantillasEnCurso,dame_id_plantilla(asigEnCurso[i], idperiodo, nivel_actual));
                            idxAsigEnPriori := array_append(idxAsigEnPriori,i);
                        END IF;
                        EXIT WHEN i = cantAsigEnCurso;
                        i := i + 1;
                    END LOOP;

                    -- Llenando la tabla prioriza
                    TablaPrioriza := matrix.create_numeric_matrix(cantPriorizando, 9);

                    i := 1;
                    LOOP
                        -- Cantidad de profesores asignados
                        param_cantProfesAsignados := dame_cant_profes_asignados(plantillasEnCurso[i]);
                        
                        -- Cantidad de restricciones de los profesores asignados
                        profesAsignados := dame_lista_id_profes_asignados(plantillasEnCurso[i]);
                        param_cantRestProfe := 0;

                        j := 0;
                        LOOP
                            EXIT WHEN j = param_cantProfesAsignados;
                            param_cantRestProfe := param_cantRestProfe + dame_cant_rest_profe(profesAsignados[j], idperiodo);
                            j := j + 1;
                        END LOOP;

                        -- Cantiad de lugares asignados
                        param_cantLugaresAsignados := dame_cant_lugares_asignados(idplantilla);

                        -- Cantidad de restricciones de los profesores asignados
                        lugaresAsignados := dame_lista_id_lugares_asignados(plantillasEnCurso[i]);
                        param_cantRestLugar := 0;

                        j := 0;
                        LOOP
                            EXIT WHEN j = param_cantLugaresAsignados;
                            param_cantRestLugar := param_cantRestLugar + dame_cant_rest_profe(lugaresAsignados[j], idperiodo);
                            j := j + 1;
                        END LOOP;

                        -- Cantidad de actividades de la asignatura
                        param_cantActAsig := cantActs[idxAsigEnPriori[i]];
                        
                        -- Cantidad de otros recursos imprescindibles
                        param_cantRecursosImprescindibles := dame_cant_otros_recursos_imprescindibles(idplantilla);
                        
                        -- Cantidad de otros recursos asignados
                        param_cantRecursosAsignados := dame_total_otros_recursos_asignados(idplantilla);
                        
                        -- Cantidad de restricciones de la asignatura
                        param_cantRestAsig := dame_cant_rest_asignatura(asigEnCurso[idxAsigEnPriori[i]]);

                        -- Rellenando la tabla
                        
                        TablaPrioriza[i][1] := param_cantProfesAsignados;          
                        TablaPrioriza[i][2] := param_cantRestProfe;
                        TablaPrioriza[i][3] := param_cantLugaresAsignados;
                        TablaPrioriza[i][4] := param_cantRestLugar;
                        TablaPrioriza[i][5] := param_cantActAsig;
                        TablaPrioriza[i][6] := param_cantRecursosImprescindibles;
                        TablaPrioriza[i][7] := param_cantRecursosAsignados;
                        TablaPrioriza[i][8] := param_cantRestAsig;

                        EXIT WHEN i = cantPriorizando;
                        i := i + 1;
                    END LOOP;

                    -- Priorizando la actividad a colocar
                    criterios := '{t,f,t,f,f,f,t,f}'::boolean;
                    niveles:= prioriza(TablaPrioriza, cantPriorizando, 8, criterios);

                    -- calculando totales horizontales
                    -- nota: los totales se colocan en la última columna de la TablaPrioriza
                    i := 1;
                    LOOP
                        k := 0; -- va a llevar la suma
                        j := 1;
                        LOOP
                            k := k + niveles[i][j];
                            EXIT WHEN j=8;
                            j := j + 1;
                        END LOOP;
                        -- La última columna puede variar si se agregan nuevos parámetros
                        TablaPrioriza[i][9] := k;
                        EXIT WHEN i = cantPriorizando;
                        i := i + 1;
                    END LOOP;
                    
                    -- Determino el orden de prioridad para colocar las actividades que entraron en juego
                    -- El orden se determina según los totales horizontales calculados, de menor a mayor

                    -- Inicializo el arreglo que va a tener el orden
                    
                    idxOrdenAct := '{}'::integer;

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

                    -- Calculando la clasificacion de actividad siguiente
                    
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

                    
                    -- -------------------------------------------
                    -- Colocando la actividad 

                    SELECT * INTO rclasificacion FROM clasificacion_de_actividad WHERE id = listaid_clasif[clasif_actual];
                    SELECT valor INTO r FROM zpriori WHERE clasificacion = listaid_clasif[clasif_actual] AND periodo = idperiodo ORDER By valor DESC LIMIT 1 OFFSET 0;

		    ztodo := r.valor;
		    
                    posibXDia := matrix.create_numeric_list(CD);

                    zp := dame_zpriori(rclasificacion.id, idperiodo);

                    IF ztodo IS NULL THEN
                        ztodo := 1;
                    END IF;

                    i := 1;
                                        
                    LOOP
                        maxDia := CD -((cantAct[fila] - nivel_actual) - compactar) * IIf(rdesglose.respeta, 1, 0);
                        
                        SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = plantillasEnCurso[idxOrdenAct[i]];
                        SELECT * INTO rdesglose FROM desglose WHERE id = rplantilla.desglose;
                        SELECT * INTO rasignatura FROM asignatura WHERE id = rdesglose.asignatura;

                        sentido := 2;
                        dia_anterior := 0;

                        grupos := dame_grupos(idesp, idnivel, rplantilla.clasificion);
                        
                        cantGrupos := matrix.get_length_of(grupos,1);

                        idMaxPIGrupo := matrix.create_numeric_matrix(cantGrupos,CD);
                        
                        faltan := cantGrupos;

                        gruposEnCurso := matrix.create_boolean_cube(cantGrupos, CD, CT);

                        j := 1;
                        LOOP
                            listaidbrg := dame_lista_id_brigadas_de_grupo(grupos[j]);
                            
                            grupompi := and_mpi(listaidbrg,CD,CT,rplantilla.id,0,true);

                            gruposEnCurso := matrix.set_matrix_in_cube(gruposEnCurso, grupompi, j);
                            
                            grupompi := and_mpi(listaidbrg,CD,CT,rplantilla.id,0,false);

                            k := 1;

                            LOOP
                                diaBooleano := matrix.extract_booolean_column(grupompi, k);
                                maxColoca := matrix.get_max_colocation(diaBooleano, rclasificacion.duracion);
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
                        


                        -- calculo de los posibles turnos optimos en zonapriori

                        IF parche1 = TRUE THEN
                            -- busco los turnos optimos de los grupos por dias

                            j := 1;
                            LOOP
                                -- calculo las posibilidades para todos los grupos

                                -- busco el mínimo
                                ya := matrix.create_boolean_list(cantGrupos);

                                k := 1;
                                LOOP
                                    
                                    l := 1;
                                    LOOP
                                        min := CT + 1;
                                        gmin := 0;

                                        -- busco el grupo con el minimo de posibilidades optimas (en zpriori = 1)

                                        g := 1;
                                        LOOP
                                            c := 0;
                                            IF ya[g] = false THEN

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

                                        -- busco la primera posibilidad para el grupo encontrado

                                        t := 1;
                                        LOOP 
                                            IF gruposEnCurso[gmin][j][t] = TRUE and zp[j][t] = 1 THEN
                                                idMaxPIGrupo[gmin][j] := t;
                                                ya[gmin] = true;

                                                -- deshabilito para todos los grupos, el tiempo ocupado por el grupo encontrado
                                                -- para que los demás turnos óptimos no se solapen con los encontrados
                                                g := 1;
                                                LOOP
                                                    tt := 1;
                                                    LOOP

                                                        gruposEnCurso[g][t + tt - 1] = false;
                                                        
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
                                        

                                        EXIT WHEN l = cantGrupos;
                                        l := l + 1;
                                    END LOOP;


                                    EXIT WHEN k = cantGrupos;
                                    k := k + 1;
                                END LOOP;
                                
                                EXIT WHEN j = CD;
                                j := j + 1;
                            END LOOP;

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

                        -- empieza la colocación
                        
                        grupo_anterior := 0; -- verificar que el grupo_anterior puede ser el mismo que j
                        
                        j := 1;
                        LOOP

                            listaidbrg := dame_lista_id_brigadas_de_grupo(grupos[j]);

                            cantListaIdBrg := matrix.get_length_of(listaidbrg, 1);
                            
                            grupompi := and_mpi(listaidbrg,CD,CT,rplantilla.id,0,false);

                            -- calculo el rango de búsqueda
                            
                            rangobusqueda := rango(idplantilla, grupos[j], rdesglose.min_dias, rdesglose.max_dias);

			   IF rplantilla.exige_profesor THEN
                            -- ----------------------------------------------------------------------------
                            -- OPTIMIZACION DEL PROFESOR
                            -- ----------------------------------------------------------------------------
                                -- Obtengo los profesores asignados al grupo actual para la actividad actual

                                actual_profe := dame_lista_id_profes_asignados_al_grupo(idplantilla, grupos[j]);
                                
                                cantActualProfe := matrix.get_length_of(actual_profe);

                                actual_profe_cant := matrix.create_numeric_matrix(cantActualProfe,CD);

                                -- cantidad de turnos ocupados en actividades por dia
                                -- se calcula el gasto total en el periodo para cada profesor

                                l := 1;
                                LOOP
                                    actual_profe_gastos[l] := 0;
                                    FOR r IN SELECT * FROM actividad WHERE dame_periodo_de_desglose(dame_desglose_de_plantilla(plantilla)) = rperiodo.id AND profesor = actual_profe[l] LOOP
                                        k := dame_duracion_clasif_de_actividad(dame_clasif_plantilla_de_actividad(idplantilla));
                                        actual_profe_cant[l][r.dia] := actual_profe_cant[l][r.dia] + k;
                                        actual_profe_gastos[l] := actual_profe_gastos[l] + k;
                                    END LOOP;

                                    EXIT WHEN l = cantActualProfe;
                                    l := l + 1;
                                END LOOP;
                                
                                -- si hay mas de un profesor se selecciona el mejor con el método prioriza 
                                -- dado los criterios:
                                --      gasto (min)
                                --      prioridad segun el usuario (min)
                                -- pero como la lista de profesores ya viene ordenada por prioridad
                                -- entonces basta con buscar el primer profesor con el minimo gasto
                                
                                mejor_profe = 1;
                                minim := -1;
                                IF cantActualProfe > 1 THEN
                                    l := 1;
                                    LOOP
                                        IF minim = -1 OR minim > actual_profe_gastos[l] THEN
                                            minim := actual_profe_gastos[l];
                                            mejor_profe := l;
                                        END IF;
                                        
                                        EXIT WHEN l = cantActualProfe;
                                        l := l + 1;
                                    END LOOP;
                                    
                                END IF;
                                
                                -- una vez obtenido el mejor profesor, entonces se crea una lista
                                -- con los mejores dias para el profesor en el rango posible de colocacion
                                -- el dia optimo esta dado por el dia de mas gasto
                                -- pues la idea es minimizar la cantidad de dias que debe venir
                                -- el profesor a dar clases (este parametro puede ser configurable)
                                                                
                                dias_buenos_profe := matrix.create_numeric_list(CD);
                                -- la principio es consecutivo
                                l := 1;
                                LOOP
                                    dias_buenos_profe[l] := l;
                                    EXIT WHEN l = CD;
                                    l := l + 1;
                                END LOOP;
                                
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
                                                                                
                                        EXIT WHEN k = rangobusqueda.fin;
                                        k := k + 1;
                                    END LOOP;
                                    EXIT WHEN l = rangobusqueda.fin - 1;
                                    l := l +1;
                                END LOOP;
                                
                            -- ----------------------------------------------------------------------------
                            END IF;
                            
                            IF j = 1 AND idClasifSgte <> '' AND idClasifSgte <> rclasificacion.id THEN
                                IF parche = FALSE OR dia_anterior <= (CD / 2) -1 THEN
                                    dia_anterior := dia_minimo_impacto (idClasifSgte, idesp, idnivel, rperiodo.id, grupos_rangos[j][1], grupos_rangos[j][2]);
                                END IF;
                            END IF;
                            
                            -- verificando que la actividad no esté asignada
                            SELECT count(id) as cant INTO r FROM actividad WHERE plantilla = rplantilla.id AND grupo = grupos[j];

                            esta_colocada := false;
                            IF r.cant > 0 THEN
                                esta_colocada := true;
                            END IF;

                            IF esta_colocada = false THEN

                                -- actualiza sentido para mayor combinacion

                                If sentido = 2 Then
                                    sentido := 1;
                                    ini_sentido := 1;
                                    fin_sentido := CT;
                                    step_sentido := 1;
                                Else
                                    sentido = 2;
                                    ini_sentido := CT;
                                    fin_sentido := 1;
                                    step_sentido := -1;
                                End If;


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

                                zona := 1;
                                LOOP
                                
                                    etapa := 1;
                                    LOOP
                                        -- preparo el rango de busqueda por dia
                                        IF etapa = 1 And dia_anterior > rangobusqueda.ini THEN
                                            CicloIni := dia_anterior;
                                            CicloFin := dia_anterior;
                                        ELSE
                                            CicloIni := rangobusqueda.ini;
                                            CicloFin := rangobusqueda.fin;

                                            If CicloFin > maxDia Then 
						CicloFin := maxDia;
				            end if;
                                        END IF;

                                        kk := CicloIni;
                                        LOOP
					    k := kk;

					    idprofe = NULL;

					    es_higienico := true;
					    
					    IF rplantilla.exige_profesor = TRUE THEN
						    -- optimizando al profesor
						    k := dias_buenos_profe[kk];
						    idprofe := actual_profe[mejor_profe];
						    	
						    -- higiene para profesor
						    IF es_higienico_para_profesor(idprofe, idplantilla, k) = FALSE THEN
							es_higienico := false;
						    END IF;
					    END IF;

					    CONTINUE WHEN es_higienico = false;

					    -- ----------------------
					    -- higiene para brigadas
					    
					    ih := 1;
					    LOOP
					        IF es_higienico_para_brigada(listaidbrg[ih], idplantilla, k) = false THEN
							es_higienico := false;
					        END IF;
					        
					        EXIT WHEN ih >= cantListaIdBrg;
						ih := ih + 1;
					    END LOOP;

					    CONTINUE WHEN es_higienico = false;

                                            -- ----------------------
                                            
                                            puedo := true;
                                            
                                            IF respeta = FALSE AND mismodia = FALSE THEN
                                            
                                                SELECT * INTO r FROM actividades WHERE grupo = grupos[j] AND dame_desglose_de_plantilla(plantilla) = rdesglose.id;

                                                IF r.grupo = grupos[j] THEN
                                                    puedo := false;
                                                END IF;

                                                IF k <> 0 AND puedo = TRUE THEN

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
                                                            
                                                            CONTINUE WHEN grupompi[k][t] = TRUE AND etapa = 1 AND xasig[t] = TRUE;
                                                            
                                                            IF grupompi[k][t] = TRUE THEN
                                                                IF etapa = 2 AND dia_anterior = 0 THEN
                                                                    xasig[t] = TRUE;
                                                                END IF;
                                                                
                                                            END IF;

                                                            IF rplantilla.exige_lugar = TRUE THEN
                                                            -- asignando lugar
                                                            
                                                                idlugar := 1;
                                                                
                                                                -- preparando...
                                                                    lugares := dame_lista_id_lugares_asignados(idplantilla);
                                                                    
                                                                    cantLugares := matrix.get_length_of(lugares, 1);
                                                                    
                                                                    -- extraer los que no permanecen en el turno
                                                                    
                                                                    lugares := extraer_lugares_que_no_permanecen(lugares, rperiodo.id, k, t, rplantilla.duracion);
                                                                
                                                                    -- extraer los lugares que no tienen capacidad
                                                                    
                                                                    lugares := quita_segun_capacidad(listaidbrg, lugares);
                                                                    
                                                                    -- extraer los lugares prohibidos
                                                                
                                                                    lugares := quita_segun_prohibidos(('{' || quote_literal(grupos[j]) || '}')::varchar[], lugares);
                                                                
                                                                -- colocando ...   
                                                                    -- tratar de colocar en el aula fija
                                                                    IF EXISTS(SELECT * FROM lugar_fijo WHERE grupo = grupos[j]) THEN
                                                                        SELECT * INTO r FROM lugar_fijo WHERE grupo = grupos[j];
                                                                        pos := matrix.get_pos(lugares, r.lugar);
                                                                        IF pos > -1 THEN
                                                                            idlugar := pos;
                                                                        END IF;
                                                                    END IF;
                                                                    
                                                                    -- buscar el mejor lugar
                                                                    
                                                                        -- buscar segun prioridades
                                                                                                                                                
                                                                        IF pos = -1 THEN
                                                                        
                                                                            -- obtengo la maxima prioridad dada a los lugares para la plantilla de actividad actual
                                                                            
                                                                            SELECT * INTO r FROM lugar_para_actividad WHERE plantilla = rplantilla.id ORDER BY priodidad DESC LIMIT 1 OFFSET 0;
                                                                           
                                                                            maxpriori := r.prioridad;
                                                                            
                                                                            -- tanto para la busqueda por el lugar mas utilizado como para el mas cercano
                                                                            -- se busca segun las prioridades dadas por el usuario a los lugares 
                                                                            
                                                                            -- obtengo la prioridad de cada lugar
                                                                            
                                                                            lugs_priori := matrix.create_numeric_list(cantLugares);
                                                                            
                                                                            ll := 1;
                                                                            LOOP
                                                                                SELECT * INTO r FROM lugar_para_actividad WHERE lugar = lugares[ll] AND plantilla = rplantilla.id;
                                                                                
                                                                                lugs_priori[ll] := r.prioridad;
                                                                                
                                                                                EXIT WHEN ll = cantLugares;
                                                                                ll := ll + 1;
                                                                            END LOOP;
                                                                            
                                                                            -- comienza la colocacion
                                                                            l := 1;
                                                                            LOOP
                                                                                -- seleccionar los lugares que tienen la prioridad actual
                                                                                
                                                                                lugs := '{}'::varchar[];
                                                                                
                                                                                ll := 1;
                                                                                LOOP
                                                                                    IF lugs_priori[ll] = l THEN
                                                                                        lugs := array_append(lugs, lugares[ll]);
                                                                                    END IF;
                                                                                    
                                                                                    EXIT WHEN ll = cantLugares;
                                                                                    ll := ll + 1;
                                                                                END LOOP;
                                                                                
                                                                                cantLugs := matrix.get_length_of(lugs);
                                                                                
                                                                                idlugar := '';
                                                                                
                                                                                IF cantLugs > 0 THEN
                                                                                    -- asignando...
                                                                                    -- TODO: pensar en una variante conjunta entre el mas usado y mas cercano
                                                                                    IF buscalugar = FALSE THEN
                                                                                        -- el mas utilizado por el grupo actual
                                                                                        idlugar := busca_lugar_mas_utilizado_por(lugs, grupos[j], FALSE);
                                                                                        
                                                                                        IF idlugar = '' THEN
                                                                                            -- el menos utilizado por los demas
                                                                                            idlugar := busca_lugar_menos_utilizado_por(lugs, grupos[j], TRUE);
                                                                                            
                                                                                            IF idlugar = '' THEN
                                                                                                -- se coge el primero
                                                                                                idlugar := lugs[1];
                                                                                            END IF;
                                                                                        END IF;
                                                                                    ELSE
                                                                                        -- ... el mas cercano
                                                                                        lugsarround := dame_lugares_usados_alrededor_de(listaidbrg, rperiodo.id, k, t);
                                                                                        
                                                                                        idlugar := busca_lugar_menos_costoso(lugs, lugsarraound);
                                                                                        
                                                                                    END IF;
                                                                                END IF;

                                                                                EXIT WHEN l = maxpriori OR idlugar <> '';
                                                                                l := l + 1;
                                                                            END LOOP;
                                                                        END IF;
                                                                END IF;   
                                                                -- asignando otros recursos
                                                                
                                                                idrecursos := '{}'::varchar[];
                                                                
                                                                -- comprobar la disponibilidad de otros recursos

                                                                gruporecursos := dame_lista_id_grupos_de_recursos_asignados(idplantilla);
                                                                cantGrupoRecursos := matrix.get_length_of(gruporecursos,1);

                                                                gr := 0;
                                                                LOOP
                                                                    EXIT WHEN gr = cantGrupoRecursos;
                                                                    
                                                                    SELECT * INTO rgruporecurso FROM otro_grupo_de_recursos_para_actividad WHERE id = gruporecursos[i];
                                                                    
                                                                    l := 0;
                                                                    FOR r IN SELECT *, dame_uso_de_otro_recurso(otro_recurso) as uso, dame_costo_trasladar_recurso(otro_recurso, idlugar, rperiodo.id, k, t) as costo FROM otro_recurso_para_actividad WHERE grupo_de_recursos = gruporecursos[gr] ORDER BY prioridad, uso, costo LOOP
                                                                        idrecursos := array_append(idrecursos, r.otro_recurso);
                                                                        l := l + 1;
                                                                        EXIT WHEN l = rgruporecurso.necesarios;
                                                                    END LOOP;
                                                                    
                                                                    EXIT WHEN gr = cantGrupoRecursos;
                                                                    gr := gr + 1;
                                                                END LOOP;
                                                                
                                                                -- agregando la actividad
                                                                IF rplantilla.exige_lugar = false THEN
									idlugar = NULL;
								END IF;						
								
                                                                SELECT agrega_actividad(rplantilla.id, idprofe, idlugar, k, t, grupos[j], idrecursos, util.moment_now(), creador);
                                                                
                                                                -- --------------------------------------------
                                                            EXIT WHEN t = fin_sentido;
                                                            t := t + step_sentido;
                                                        END LOOP;
                                                    END IF;
            
                                                END IF;
                                                
                                            END IF;

                                            EXIT WHEN kk = CicloFin;
                                            kk := kk + 1;
                                        END LOOP;
                                        
                                        EXIT WHEN etapa = 3;
                                        etapa := etapa + 1;
                                    END LOOP;

                                    EXIT WHEN zona = ztodo.valor;
                                    zona := zona + 1;
                                END LOOP;
                            END IF;

                            EXIT WHEN j = cantGrupos;
                            
                            grupo_anterior := j;
                            
                            j := j + 1;
                        END LOOP;
                        
                        -- --------------------------------------------
                        EXIT WHEN i = cantPriorizando;
                        i := i + 1;
                    END LOOP;

                    EXIT WHEN varCiclo2 = cantCiclo2;
                    varCiclo2 := varCiclo2 + 1;
                END LOOP;
                EXIT WHEN varCiclo1 = cantCiclo1;
                varCiclo1 := varCiclo1 + 1;
            END LOOP;
            
            EXIT WHEN voyact = cantActEnCurso;
        END LOOP;
        
    END;
$$
language 'plpgsql';