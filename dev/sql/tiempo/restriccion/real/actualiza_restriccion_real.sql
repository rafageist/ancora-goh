
--                     Calculo de la fecha exacta de un dia relativo de un periodo                
/*                                                                                               */
/*     o/  Supongamos una planificacion de 10 dias que comienza el dia real 20.                  */
/*    <|   Si el dia nominal es el 4to del periodo que se encuentra a partir del dia real 3,     */
/*    / \  entonces el dia exacto o fecha exacta es el 25.                                       */
/*                                                                                               */
/*                          dia_inicial                 fecha                                    */
/*                               |                        |                                      */
/*                               v                        v                                      */
/* *//** ---------------------------------------------------------------------------------  *//* */
/* *//** fecha exacta                  | 20 | 21 | 22 | 23 | 24 | 25 | 26 | 27 | 28  | 29 | *//* */
/* *//** ---------------------------------------------------------------------------------- *//* */
/* *//** numero consecutivo del dia    |  1 |  2 | 3  |  4 |  5 | 6  |  7 |  8 |  9  | 10 | *//* */
/* *//** ---------------------------------------------------------------------------------- *//* */
/* *//** periodicidad 1                |    |    |####|####|####|####|####|    |     |    | *//* */
/* *//** ---------------------------------------------------------------------------------- *//* */
/*                                                 ^              ^                              */
/*                                                 |              |                              */
/*                                     rperiodicidad.dia   rnominal.dia                          */
/*                                                                                               */
/*          *//** -----------------------------------------------------------------*//*          */
/*          *//** @formula: fecha = dia_inicial + rperiodicidad.dia - 1 + pdia - 1 *//*          */
/*          *//** @ejemplo: fecha = 20 + 3 - 1 + 4 - 1 = 23 - 1 + 3  = 22 + 3 = 25 *//*          */
/*          *//** -----------------------------------------------------------------*//*          */
/*                                                                                               */
/* ----------------------------------------------------------------------------------------------*/
    
CREATE OR REPLACE FUNCTION actualiza_restriccion_real(
    idobjeto varchar,
    idactividad varchar,
    idnominal varchar,
    idperiodo varchar,
    pdia integer,
    pturno integer,
    por_actividad boolean,
    tabla_real varchar
)
RETURNS VOID AS
$$
DECLARE 
    rperiodicidad RECORD;
    rreal RECORD;
    rplanificacion RECORD;
    idact varchar;
    rperiodo RECORD;
    c bigint;
    
    -- campos a actualizar
    fecha bigint;
    dia_inicial bigint;
    hora_inicial double precision;
    hora_final double precision;
BEGIN   
    
    
    IF idactividad IS NULL THEN
        idact := 'NULL';
    ELSE
        idact := quote_literal(idactividad);
    END IF;  

    -- se obtienen otros datos de la restriccion nominal
    -- nota: la tabla de restricciones de periodos no es igual a las demas pero 
        -- todas contiene un campo llamado 'periodo' por lo que no hay problema
        -- en utilizarlo
    SELECT * INTO rplanificacion FROM planificacion WHERE id = dame_planificacion_de_periodo(idperiodo);
    SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;
    
    -- se calculan los demas datos de la fecha real
    dia_inicial := dame_dia_real_planificacion(rplanificacion.id, 1);
    hora_inicial := dame_hora_inicial_turno(idperiodo, pturno); 
    hora_final := hora_inicial + rperiodo.duracion_del_turno;

    -- en rnominal se tiene el registro de la restriccion nominal
    -- ahora hay que actualizar o insertar las restricciones reales correspondientes
    -- para eso se recorren todas la periodicidades de la restriccion nominal y si no se encuentra
    -- se inserta y si ya existe se actualiza
    
    FOR rperiodicidad IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP
       
       -- se busca si existe
       EXECUTE 'SELECT * FROM ' || tabla_real || ' WHERE restriccion_nominal = ' || quote_literal(idnominal) || ' AND periodicidad = ' || quote_literal(rperiodicidad.id) || ';' INTO rreal;
       GET DIAGNOSTICS c = ROW_COUNT;
       
       fecha := dia_inicial + rperiodicidad.dia - 1 + pdia - 1;
       
       IF c > 0 THEN
            -- actualizar
            EXECUTE 'UPDATE ' || tabla_real || ' SET fecha = ' || fecha || ', hora_inicial = ' || hora_inicial || ', hora_final = ' || hora_final || ', por_actividad = ' || bool_to_str(por_actividad) || ', actividad = ' || idact || ', objeto = ' || quote_literal(idobjeto) || ' WHERE restriccion_nominal = ' || quote_literal(idnominal) || ' AND periodicidad = ' || quote_literal(rperiodicidad.id) || ';';
            
       ELSE
            -- insertar
        
           EXECUTE 'INSERT INTO ' || tabla_real || '(restriccion_nominal, periodicidad, fecha, hora_inicial, hora_final, por_actividad, actividad, objeto) VALUES (' || quote_literal(idnominal) || ',' || quote_literal(rperiodicidad.id) || ',' || fecha || ',' || hora_inicial || ',' || hora_final || ',' || bool_to_str(por_actividad) || ',' || idact || ',' || quote_literal(idobjeto) || ');';
           
       END IF;
    END LOOP;
END;
$$
LANGUAGE 'plpgsql';