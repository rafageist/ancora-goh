
-- CONOCER SI OCURRE LA CAUSA DE UNA RESTRICCION CONDICIONAL

/** <o>   que enredo!  */
/**  |                 */
/** / \                */

CREATE or replace function ocurre_causa_rest_cond(
    rc restriccion_condicional, 
    act actividad, 
    idperiodo VARCHAR, 
    pdia INTEGER, 
    pturno INTEGER, 
    analiza_completo BOOLEAN
) 
RETURNS BOOLEAN AS
$$    
DECLARE 
    r record;
    r1 record;
    tbool boolean;
    duracion_act integer;
    actperiodo varchar;
    hi double precision;
    hf double precision;
    dia_hoy bigint;
BEGIN
	actperiodo := dame_periodo_plantilla_de_actividad(act.plantilla);

    -- analizar que la actividad se identifique con los objetos
    -- y caracteristicas (asig, clasif)
    -- si las propiedades son nulas las ignoro

    -- no analizar completo significa que lo que existen atributos
    -- que se pueden analizar en una clausula WHERE de un SELECT
    -- y queda mas optimo como por ejemplo:
        /**
            SELECT * FROM actividad WHERE 
            (r.clasif_act = dame_clasif_plantilla_de_actividad(act.plantilla) OR rc.clasif_act IS NULL) AND
            (r.asig = dame_asignatura_plantilla_de_actividad(act.plantilla) OR r.asig IS NULL) AND
            (matrix.get_pos_of(rc.brigada, dame_lista_id_brigadas_de_grupo(act.grupo)) > -1 OR r.brigada IS NULL) AND
            (r.profesor = act.profesor OR r.profesor IS NULL) AND
            (r.lugar = act.lugar OR r.lugar IS NULL) AND
            (r.especialidad = dame_especialidad_de_asignatura(dame_asignatura_plantilla_de_actividad(act.plantilla)) OR r.especialidad IS NULL) AND
            (r.nivel = dame_nivel_de_asignatura(dame_asignatura_plantilla_de_actividad(act.plantilla)) OR r.nivel IS NULL) AND
            (r.periodo = actperiodo OR r.periodo IS NULL) LOOP
            
        */
    
    IF analiza_completo = TRUE THEN
        -- clasificacion de actividad
        IF rc.clasif_act IS NOT NULL THEN
            IF rc.clasif_act <> dame_clasif_plantilla_de_actividad(act.plantilla) THEN
                RETURN FALSE;
            END IF;
        END IF;

        -- asignatura
        IF rc.asig IS NOT NULL THEN
            IF rc.asig <> dame_asignatura_plantilla_de_actividad(act.plantilla) THEN
                RETURN FALSE;
            END IF;
        END IF;
        
        -- brigada
        IF rc.brigada IS NOT NULL THEN
            IF matrix.get_pos_of(rc.brigada, dame_lista_id_brigadas_de_grupo(act.grupo)) = -1 THEN
                RETURN FALSE;
            END IF;
        END IF;

        -- profesor
        IF rc.profesor IS NOT NULL THEN
            IF rc.profesor <> act.profesor THEN
                RETURN FALSE;
            END IF;
        END IF;

        -- lugar
        IF rc.lugar IS NOT NULL THEN
            IF rc.lugar <> act.lugar THEN
                RETURN FALSE;
            END IF;
        END IF;
        -- especialidad
        IF rc.especialidad IS NOT NULL THEN
            IF rc.especialidad <> dame_especialidad_de_asignatura(dame_asignatura_plantilla_de_actividad(act.plantilla)) THEN
                RETURN FALSE;
            END IF;
        END IF;

        -- nivel
        IF rc.nivel IS NOT NULL THEN
            IF rc.nivel <> dame_nivel_de_asignatura(dame_asignatura_plantilla_de_actividad(act.plantilla)) THEN
                RETURN FALSE;
            END IF;
        END IF;

        -- analizando periodo
        IF rc.periodo IS NOT NULL THEN
            IF rc.periodo <> actperiodo THEN
                RETURN FALSE;
            END IF;
        END IF;
    END IF;
    
    -- otros recursos
    IF rc.otro_recurso IS NOT NULL THEN
        SELECT * INTO r FROM otros_recursos_usados WHERE r.otro_recurso = rc.otro_recurso AND actividad = act.id;
        
        IF NOT FOUND THEN
            RETURN FALSE;
        END IF;
    END IF;

    /*  ANALIZANDO EL TIEMPO DE LA ACTIVIDAD */
    
    /** RANGO EXACTO */
    
    tbool := false;
    
    duracion_act := dame_duracion_plantilla_de_actividad(act.plantilla); -- creo la variable para calcularla una sola vez 
                                                                         --porque luego hace falta y para calcularla
    
    hi := dame_hora_inicial_turno(actperiodo, act.turno);
    hf := hi + dame_duracion_turno_periodo(actperiodo) * duracion_act;
   
    FOR r IN SELECT * FROM periodicidad WHERE  periodo = idperiodo LOOP
        dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
        
        -- se analiza de forma negativa para analizar los posibles 4*4 casos posibles
        
        /**  @explicacion: no salirse del rango discreto de tiempo por cualquiera de las */
        /**  los lados que no son nulos */
        
        SELECT * INTO r1 FROM restriccion_real WHERE actividad = act.id 
            AND ((rc.discreto_exacto = TRUE 
                    AND NOT (rc.fecha_ini IS NOT NULL AND dia_hoy < rc.fecha_ini)
                    AND NOT (rc.fecha_fin IS NOT NULL AND dia_hoy > rc.fecha_fin)
                    AND NOT (rc.hora_ini IS NOT NULL AND hf < rc.hora_ini)
                    AND NOT (rc.hora_ini IS NOT NULL AND hi > rc.hora_fin)
                  ) OR
                  (rc.discreto_exacto = FALSE 
                    AND NOT (rc.fecha_ini IS NOT NULL AND dia_hoy < rc.fecha_ini)
                    AND NOT (rc.fecha_fin IS NOT NULL AND dia_hoy > rc.fecha_fin)
                    AND NOT (rc.hora_ini IS NOT NULL AND dia_hoy = fecha_ini AND hf < rc.hora_ini)
                    AND NOT (rc.hora_ini IS NOT NULL AND dia_hoy = fecha_fin AND hi > rc.hora_fin)
                  )
                 );
        
        IF FOUND THEN
            tbool := true;
        END IF;
        EXIT WHEN tbool = true;
    END LOOP;

    IF tbool = FALSE AND (
           rc.fecha_ini IS NOT NULL 
        OR rc.fecha_fin IS NOT NULL 
        OR rc.hora_ini IS NOT NULL 
        OR rc.hora_fin IS NOT NULL) THEN
        
        RETURN FALSE;
    END IF;
    
    /** HOY */

    IF rc.hoy IS NOT NULL THEN
    
        -- buscando en todas las periodicidades 
        tbool := false;
        FOR r IN SELECT * FROM periodicidad WHERE  periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            SELECT * INTO r1 FROM restriccion_real WHERE actividad = act.id AND fecha = dia_hoy;
            IF FOUND THEN
                tbool := true;
            END IF;
            EXIT WHEN tbool = true;
        END LOOP;
        
        IF rc.hoy = TRUE THEN
            -- si dice que tiene que ser hoy y si no es hoy me voy
            
            IF tbool = FALSE THEN
                RETURN FALSE;
            END IF;
            
            /** IF dias_se_solapan(actperiodo, act.dia, idperiodo, pdia) = FALSE THEN */
            /**    RETURN FALSE; */
            /** END IF;          */
        ELSE
            -- si dice que no puede ser hoy y si es hoy me voy
            
            IF tbool = TRUE THEN
                RETURN FALSE;
            END IF;
            
            /** IF dias_se_solapan(actperiodo, act.dia, idperiodo, pdia) = TRUE THEN */
            /**    RETURN FALSE;  */
            /** END IF;           */
        END IF;
    END IF;
   
    /** AHORA */
    IF rc.ahora IS NOT NULL THEN
        tbool := false;
        hi := dame_hora_inicial_turno(idperiodo, pturno);
        hf := hi + dame_duracion_turno_periodo(idperiodo);
        
        FOR r IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            
            SELECT * INTO r1 FROM restriccion_real WHERE actividad = act.id AND fecha = dia_hoy AND NOT (hora_inicial > hf OR hora_final < hi);
            IF FOUND THEN
                tbool := true;
            END IF;
            EXIT WHEN tbool = true;
        END LOOP;
        
        IF rc.ahora = TRUE THEN
            -- se dice que tiene que ser ahora y si no es ahora me voy
            
            IF tbool = FALSE THEN
                RETURN FALSE;
            END IF;
            
            /*IF turnos_se_solapan(actperiodo, act.dia, act.turno, idperiodo, pdia, pturno <> pturno) = FALSE  THEN
                RETURN FALSE;
            END IF;*/
        ELSE
            -- se dice que no puede ser ahora y si es ahora me voy
            
            IF tbool = TRUE THEN
                RETURN FALSE;
            END IF;
            
            /*IF turnos_se_solapan(actperiodo, act.dia, act.turno, idperiodo, pdia, pturno <> pturno) = TRUE THEN
                RETURN FALSE;
            END IF;*/
        END IF;
    END IF;

    /** Dias anteriores */

    IF rc.dia_relativo_anterior IS NOT NULL THEN
        tbool := false;
        FOR r IN SELECT * FROM periodicidad WHERE  periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            SELECT * INTO r1 FROM restriccion_real WHERE actividad = act.id AND (fecha >= dia_hoy - rc.dia_relativo_anterior - rc.cant_dias_relativo_anterior + 1 AND fecha <= dia_hoy - rc.dia_relativo_anterior);
            IF FOUND THEN
                tbool := true;
            END IF;
            EXIT WHEN tbool = true;
        END LOOP;
        
        -- si no encuentra actividad en dias anteriores entonces me voy
        IF tbool = FALSE THEN
            RETURN FALSE;
        END IF;
    END IF;

    /** Dias posteriores */
    
    IF rc.dia_relativo_posterior IS NOT NULL THEN
        tbool := false;
        FOR r IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            SELECT * INTO r1 FROM restriccion_real WHERE actividad = act.id AND (fecha >= dia_hoy + rc.dia_relativo_posterior AND fecha <= dia_hoy + rc.cant_dias_relativo_posterior - 1);
            IF FOUND THEN
                tbool := true;
            END IF;
            EXIT WHEN tbool = true;
        END LOOP;
        
        IF tbool = FALSE THEN
            RETURN FALSE;
        END IF;
    END IF;
    
    /**                                                                          */ 
    /**  o   @nota Los turnos anteriores y posteriores son referentes al periodo */--
    /** <|>  No se utilizan para especificar tiempo ante o despues relativo      */--
    /** / \  al dia que se pregunta                                              */--
    /**                                                                          */--
        --                                                                           
    
    /** turnos anteriores */
    IF rc.turno_relativo_anterior IS NOT NULL THEN
        IF act.turno < pturno - rc.turno_relativo_anterior - rc.cant_turnos_relativo_anterior + 1 AND (actperiodo = rc.periodo OR rc.periodo IS NULL) THEN
            RETURN FALSE;
        END IF;
    END IF;

    /** Turnos posteriores */
    IF rc.turno_relativo_posterior IS NOT NULL THEN
        IF act.turno < pturno + rc.turno_relativo_posterior - rc.cant_turnos_relativo_posterior - 1 AND (actperiodo = rc.periodo OR rc.periodo IS NULL) THEN
            RETURN FALSE;
        END IF;
    END IF;
        
    /** Entre dias */
    IF NOT (rc.entre_dias_antes IS NULL AND rc.entre_dias_despues IS NULL) THEN -- al menos  uno de los dos debe ser no nulo
        tbool := false;
        FOR r IN SELECT * FROM periodicidad WHERE  periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            
            SELECT * INTO r1 FROM restriccion_real 
                WHERE actividad = act.id 
                AND (
                     (fecha >= dia_hoy - rc.entre_dias_antes OR rc.entre_dias_antes IS NULL) 
                      AND 
                     (fecha <= dia_hoy + rc.entre_dias_despues OR rc.entre_dias_despues IS NULL)
                    );
            
            IF FOUND THEN
                tbool := true;
            END IF;
            EXIT WHEN tbool = true;
        END LOOP;
        
        -- si no encuentra actividad entre dias
        IF tbool = FALSE THEN
            RETURN FALSE;
        END IF;
    END IF;

    /** Entre turnos */
    IF NOT (rc.entre_turnos_antes IS NULL AND rc.entre_turnos_despues IS NULL) THEN
        IF (act.turno < pturno - rc.entre_turnos_antes OR act.turno > pturno + rc.entre_turnos_despues) AND (actperiodo = rc.periodo OR rc.periodo IS NULL) THEN
            RETURN FALSE;
        END IF;
    END IF;

        -- el rango de dias y turnos real no tiene por que tener periodo
        -- se pregunta de forma general, puede que existan varios periodos
        -- con la misma configuracion de tiempo

        -- tampoco es obligatorio que exista el rango, puede que sea en todo el periodo

    /** En rango en el periodo */
    
    IF rc.periodo IS NOT NULL AND actperiodo <> rc.periodo THEN
        RETURN FALSE;
    END IF;
    
    IF (rc.discreto = TRUE) THEN
      IF (rc.dia_ini IS NOT NULL AND act.dia < rc.dia_ini) 
          OR (rc.dia_fin IS NOT NULL AND act.dia > rc.dia_fin)
          OR (rc.turno_ini IS NOT NULL AND act.turno < rc.turno_ini)
          OR (rc.turno_fin IS NOT NULL AND rc.turno_fin < act.turno + duracion_act) THEN
          RETURN FALSE;
      END IF;
    ELSE
      IF (rc.dia_ini IS NOT NULL AND act.dia < rc.dia_ini) 
          OR (rc.dia_fin IS NOT NULL AND act.dia > rc.dia_fin)
          OR (rc.turno_ini IS NOT NULL AND act.dia = rc.dia_ini AND act.turno < rc.turno_ini)
          OR (rc.turno_fin IS NOT NULL AND act.dia = rc.dia_fin AND rc.turno_fin < act.turno + duracion_act) THEN
          RETURN FALSE;
      END IF;
    END IF;

    /** En rango en la planificacion */
    
    hi := dame_hora_inicial_turno(actperiodo, act.turno);
    hf := hi + dame_duracion_turno_periodo(actperiodo) * duracion_act;
    
    tbool := false;
    FOR r IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP
        dia_hoy := act.dia + pdia - 1;
        
        IF rc.discreto_exacto_planif = TRUE THEN
            IF NOT ((rc.dia_ini_planif IS NOT NULL AND dia_hoy < rc.dia_ini_planif)
                    OR (rc.dia_fin_planif IS NOT NULL AND dia_hoy > rc.dia_fin_planif)
                    OR (rc.hora_ini_planif IS NOT NULL AND hf < rc.hora_ini_planif)
                    OR (rc.hora_fin_planif IS NOT NULL AND hi > rc.hora_fin_planif)
                    ) THEN
            
            tbool := TRUE;
            
            END IF;
        ELSE
             IF NOT ((rc.dia_ini_planif IS NOT NULL AND dia_hoy < rc.dia_ini_planif)
                    OR (rc.dia_fin_planif IS NOT NULL AND dia_hoy > rc.dia_fin_planif)
                    OR (rc.hora_ini_planif IS NOT NULL AND dia_hoy = rc.dia_ini_planif AND hf < rc.hora_ini_planif)
                    OR (rc.hora_fin_planif IS NOT NULL AND dia_hoy = rc.dia_fin_planif AND hi > rc.hora_fin_planif)
                    ) THEN
            
            tbool := TRUE;
            
            END IF;
        END IF;
           
        EXIT WHEN tbool = true;
     END LOOP;
    
     IF tbool = FALSE THEN
         RETURN FALSE;
     END IF;
    
     /** Tiempo relativo anterior */

     hi := dame_hora_inicial_turno(idperiodo, pturno);
     hf := hi + dame_duracion_turno_periodo(idperiodo);
     
     IF rc.rango_tiempo_relativo_anterior_ini IS NOT NULL THEN
         tbool := false;
         FOR r IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            
            IF rc.rango_tiempo_relativo_anterior_tamano IS NOT NULL THEN
                SELECT * INTO r1 FROM restriccion_real 
                    WHERE actividad = act.id 
                    AND NOT tiempo.moment_number(fecha,hora_fin) < tiempo.moment_number(dia_hoy,hi) - rc.rango_tiempo_relativo_anterior_ini - rango_tiempo_relativo_anterior_tamano 
                    AND NOT tiempo.moment_number(fecha,hora_ini) > tiempo.moment_number(dia_hoy,hi) - rc.rango_tiempo_relativo_anterior_ini;
            ELSE
                SELECT * INTO r1 FROM restriccion_real 
                    WHERE actividad = act.id 
                    AND NOT tiempo.moment_number(fecha,hora_ini) > tiempo.moment_number(dia_hoy,hi) - rc.rango_tiempo_relativo_anterior_ini;
            END IF;
            
            IF FOUND THEN
                tbool := true;
            END IF;
            
            EXIT WHEN tbool = true;
          END LOOP;

          IF tbool = FALSE THEN
              RETURN FALSE;
          END IF;
      END IF;
    
    /** Tiempo relativo posterior */
    
     IF rc.rango_tiempo_relativo_posterior_ini IS NOT NULL THEN
         tbool := false;
         FOR r IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            
            IF rc.rango_tiempo_relativo_posterior_tamano IS NOT NULL THEN
                SELECT * INTO r1 FROM restriccion_real 
                    WHERE actividad = act.id 
                    AND NOT tiempo.moment_number(fecha,hora_fin) < tiempo.moment_number(dia_hoy,hi) + rc.rango_tiempo_relativo_posterior_ini
                    AND NOT tiempo.moment_number(fecha,hora_ini) > tiempo.moment_number(dia_hoy,hi) + rc.rango_tiempo_relativo_posterior_ini + rango_tiempo_relativo_posterior_tamano;
            ELSE
                SELECT * INTO r1 FROM restriccion_real 
                    WHERE actividad = act.id 
                    AND NOT tiempo.moment_number(fecha,hora_fin) < tiempo.moment_number(dia_hoy,hi) + rc.rango_tiempo_relativo_posterior_ini;
            END IF;
            
            IF FOUND THEN
                tbool := true;
            END IF;
            
            EXIT WHEN tbool = true;
          END LOOP;

          IF tbool = FALSE THEN
              RETURN FALSE;
          END IF;
      END IF;
      
     /** Entre tiempos */
    
     IF NOT (rc.entre_tiempo_antes IS NULL AND rc.entre_tiempo_despues IS NULL) THEN
         tbool := false;
         FOR r IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            
            SELECT * INTO r1 FROM restriccion_real 
                WHERE actividad = act.id AND
                (
                 (tiempo.moment_number(fecha,hora_fin) >= tiempo.moment_number(dia_hoy,hi) - rc.entre_tiempo_antes OR rc.entre_tiempo_antes IS NULL) 
                  AND 
                 (tiempo.moment_number(fecha,hora_ini) <= tiempo.moment_number(dia_hoy,hf) + rc.entre_tiempo_despues OR rc.entre_tiempo_despues IS NULL) 
                ); 
                    
            IF FOUND THEN
                tbool := true;
            END IF;
            
            EXIT WHEN tbool = true;
          END LOOP;

          IF tbool = FALSE THEN
              RETURN FALSE;
          END IF;
      END IF;
      
    RETURN TRUE;
END;
$$
LANGUAGE 'plpgsql';