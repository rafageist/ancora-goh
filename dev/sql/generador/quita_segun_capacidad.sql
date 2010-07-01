CREATE OR REPLACE FUNCTION quita_segun_capacidad(brigadas VARCHAR[], lugares VARCHAR[]) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r RECORD;
        cantLug BIGINT;
        i BIGINT;
        matricula BIGINT;
        capacidad INTEGER;
    BEGIN
        matricula := dame_matricula_total(brigadas);
        cantLug := matrix.get_length_of(lugares, 1);

	IF cantLug > 0 THEN
		i := 1;
		LOOP
		    capacidad := dame_capacidad(lugares[i]); 

		    -- si la capacidad es 0 significa que no importa o que es infinita

		    IF capacidad = 0 OR capacidad >= matricula THEN
			arr := array_append(arr, lugares[i]);
		    END IF;
		    
		    EXIT WHEN i >= cantLug;
		    i := i + 1;
		END LOOP;
        END IF;
        
        RETURN arr;
    END;
    
$$
LANGUAGE 'plpgsql';