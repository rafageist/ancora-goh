
CREATE OR REPLACE FUNCTION quita_segun_prohibidos(grupos VARCHAR[], lugares VARCHAR[]) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r RECORD;
        cantLug BIGINT; cantGrupos BIGINT;
        i BIGINT; j BIGINT;
        puede BOOLEAN;
    BEGIN
        
        cantLug := matrix.get_length_of(lugares, 1);
        cantGrupos := matrix.get_length_of(grupos, 1);

        IF cantLug > 0 THEN
		i := 1;
		LOOP
		    puede := TRUE;

		    IF cantGrupos >0 THEN
		    
			    j := 1;

			    LOOP
				IF es_lugar_prohibido (grupos[j], lugares[i]) THEN
				    puede := FALSE;
				    EXIT;
				END IF;
				
				EXIT WHEN j >= cantGrupos;
				j := j + 1;
			    END LOOP;

		    END IF;
		    IF puede = TRUE THEN
			arr := array_append(arr, lugares[i]);
		    END IF;
		    
		    EXIT WHEN i = cantLug;
		    i := i + 1;
		END LOOP;
        END IF;
        
        RETURN arr;
    END;
    
$$
LANGUAGE 'plpgsql';