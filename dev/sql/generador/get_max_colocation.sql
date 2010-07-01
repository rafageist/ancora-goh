-- -----------------------------------------------------------------------------------
--      NOMBRE: get_max_colocation
--        TIPO: funcion
-- DESCRIPCION: Dado un arreglo de posibles inicios de una actividad devuelve la maxima cantidad 
--              de colocaciones posibles
--     EJEMPLO: SELECT matrix.get_max_colocation(array[FALSE,TRUE,TRUE,TRUE,FALSE], 2);

/**   @note:                                                                                       
        Un arreglo con TRUE al final no debe ocurrir para actividades de m?de 1 turno de           
        duracion. Por ejemplo array[FALSE,TRUE,TRUE,TRUE]  con una actividad de 2 turnos no        
        debe ser un arreglo de posibles inicios, pues en el ??mo turno no se pueden dar dos.       
        De todas maneras la funcion verifica eso para un posible arreglo FALSE de posibles         
        inicios, de modo tal que                                                                   
                                                                                                   
            matrix.get_max_colocation(array[FALSE,TRUE,TRUE,TRUE],2) = 1;                          
                                                                                                   
        y por su puesto                                                                            
            matrix.get_max_colocation(array[FALSE,TRUE,TRUE,TRUE],3) = 1;                          
            matrix.get_max_colocation(array[FALSE,TRUE,TRUE,TRUE],1) = 3;                          
                                                                                                  */

-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION get_max_colocation(arr BOOLEAN[], duracion INTEGER) RETURNS INTEGER AS
$$
    DECLARE crows BIGINT;
        i INTEGER;
        inipi INTEGER;
        ini INTEGER;
        c INTEGER;
    BEGIN
        crows := matrix.get_length_of(arr, 1);

        IF crows = 0 THEN
            RETURN 0;
        END IF;

        inipi := 0;
        c := 0;
        
        i := 1;
        LOOP 
            IF arr[i] = TRUE THEN
                inipi = i;
                c := 1;
            END IF;
            EXIT WHEN i >= crows OR c = 1;
            i := i + 1;
        END LOOP;

        IF inipi > 0 THEN
		LOOP
		    EXIT WHEN ini + duracion > crows;

		    ini := inipi + duracion;
		    
		    IF arr[ini] = FALSE THEN
			inipi = inipi+ 1;
		    ELSE
			IF ini < crows OR duracion = 1 THEN
			    c := c + 1;
			END IF;
			inipi = ini;
		    END IF;
			
		END LOOP;
        END IF;
        
        RETURN c;

    END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION get_max_colocation(arr BOOLEAN[], duracion INTEGER) IS 'Dado un arreglo de posibles inicios de una actividad devuelve la maxima cantidad de colocaciones posibles';
