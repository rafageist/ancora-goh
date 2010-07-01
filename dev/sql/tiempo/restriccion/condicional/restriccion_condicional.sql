
-- RESTRICCIONES CONDICIONALES

-- si ocurre tal cosa restringo tal objeto

/* RESTRICCION CONDICIONAL */
 DROP table IF EXISTS restriccion_condicional CASCADE;
 CREATE TABLE restriccion_condicional(
        id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'restriccion_condicional', 'id', 'restriccion_condicional'),
        planificacion VARCHAR REFERENCES planificacion(id)  ON DELETE CASCADE ON UPDATE CASCADE, 
        activa BOOLEAN default TRUE,
        
        /** CAUSA O PRECONDICION */   

        /** Los param para especificar actividad no llevan NOT NULL pues NULL significa cualquiera */
        /** Si una actividad se identifica con los atributos no nulos entonces es valida la causa  */
        /** y si todos son nulos tambien es valida la causa                                        */
            
        clasif_act VARCHAR REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        asig VARCHAR REFERENCES asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        brigada VARCHAR REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        lugar VARCHAR REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE,
        otro_recurso VARCHAR REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        especialidad VARCHAR REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        nivel VARCHAR REFERENCES nivel(id) ON DELETE CASCADE ON UPDATE CASCADE, 

        /* si la actividad o parte de la actividad cae en este rango */
        
        -- PARA RANGO EXACTO
            
        fecha_ini integer default null,
        fecha_fin integer default null,
        hora_ini double precision default null,
        hora_fin double precision default null,
        discreto_exacto boolean default null,
            
        -- PARA RANGO EXACTO RELATIVO A LA PLANIFICACION
        
        dia_ini_planif integer default null,
        dia_fin_planif integer default null,
        hora_ini_planif double precision default null,
        hora_fin_planif double precision default null,
        discreto_exacto_planif boolean default null,
        
        -- PARA RANGO EXACTO RELATIVO AL PERIODO
        
        periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        dia_ini INTEGER default null, 
        dia_fin INTEGER default null, 
        turno_ini INTEGER default null, 
        turno_fin INTEGER default null, 

        discreto BOOLEAN default null,

        /* si la actividad o parte ocurre en correspondiencia */
        /* con los siguientes parametros de tiempo nominal    */
        
        /** @note: los nulos se ignoran */
        
        -- PARA CASO NOMINAL
         -- tiempo de la causa en forma nominal
                 
        hoy BOOLEAN default null, 
        ahora BOOLEAN default null,
         
        dia_relativo_anterior INTEGER default 0,  -- ayer, hace 2 dias
        cant_dias_relativo_anterior INTEGER default 0,  -- cantidad antes de ese dia
        
        CHECK (dia_relativo_anterior > -1),
        CHECK (cant_dias_relativo_anterior > -1),
        
        dia_relativo_posterior INTEGER default 0, -- manana, dentro de 3 dias
        cant_dias_relativo_posterior INTEGER default 0, -- cantidad despues de ese dia

        CHECK (dia_relativo_posterior > -1),
        CHECK (cant_dias_relativo_posterior > -1),
        
        rango_tiempo_relativo_anterior_ini double precision default null, 
        rango_tiempo_relativo_anterior_tamano double precision default null, 

        turno_relativo_anterior integer default null,
        cant_turnos_relativo_anterior integer default null,

        CHECK (turno_relativo_anterior > -1),
        CHECK (cant_turnos_relativo_anterior > -1),

        turno_relativo_posterior INTEGER default 0, -- esto es despues de se que acabe la actividad
                                -- si es 1, entonces es el turno siguiente a la actividad
                                
        cant_turnos_relativo_posterior INTEGER default 0, -- cuantos despues
        
        CHECK (turno_relativo_posterior > -1),
        CHECK (cant_turnos_relativo_posterior > -1),

        rango_tiempo_relativo_posterior_ini double precision default null, 
        rango_tiempo_relativo_posterior_tamano double precision default null, 
                        
        -- entre x dias antes y z dias despues
        
        entre_dias_antes INTEGER default null,
        entre_dias_despues INTEGER default null,
        
        -- entre n turnos antes y m turno despues
        
        entre_turnos_antes integer default null, 
        entre_turnos_despues integer default null, 
        
        entre_tiempo_antes double precision DEFAULT NULL,
        entre_tiempo_despues double precision DEFAULT NULL,
        
        -- cantidad de actividades que coincidan con la condicion
        
        cant_exa INTEGER default 1, /** @significa exactamente */
        cant_min INTEGER default 1, /** @significa al menos o como minimo */
        cant_max INTEGER default 1, /** @significa no mas de, como maximo */

        /** EFECTO o POSTCONDICION */
        
        -- si la causa existe entonces se restringe a ... en el rango ...
        
        consec_brigada VARCHAR REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        consec_profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        consec_lugar VARCHAR REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        consec_otro_recurso VARCHAR REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 

        consec_periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        consec_dia_ini INTEGER DEFAULT NULL,
        consec_dia_fin INTEGER DEFAULT NULL, 
        consec_turno_ini INTEGER DEFAULT NULL, 
        consec_turno_fin INTEGER DEFAULT NULL, 

        consec_discreto BOOLEAN default TRUE,
        siempre BOOLEAN default null, -- siempre que sea ahora, hoy, hace tres dias, en este periodo....
        valor INTEGER default 1, -- valor de la restriccion

        CHECK (valor > 0)
    );
