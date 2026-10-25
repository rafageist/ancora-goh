-- Function: prioriza.nivelar(numeric[], boolean)

-- DROP FUNCTION prioriza.nivelar(numeric[], boolean);

CREATE OR REPLACE FUNCTION prioriza.nivelar(arr numeric[], criterio boolean)
  RETURNS integer[] AS
$BODY$
    DECLARE niveles int[];
        nivel int;
        ya BOOLEAN[];
        i INTEGER;
        countya INTEGER;
        optimo NUMERIC; -- #23: was INTEGER, so a non-integer value never equalled
                        -- it and the levelling loop never ended (integer input: unchanged)
        primero BOOLEAN;
        cant bigint;
    BEGIN
        -- #23: inputs that used to loop forever. Nothing to level: no levels.
        -- A missing value cannot be ordered: rejected.
        IF arr IS NULL OR cardinality(arr) = 0 THEN
            RETURN '{}'::int[];
        END IF;
        IF array_position(arr, NULL) IS NOT NULL THEN
            RAISE EXCEPTION 'prioriza.nivelar: NULL value at position %', array_position(arr, NULL)
                USING ERRCODE = 'null_value_not_allowed';
        END IF;

        nivel := 1;
        cant := matrix.get_length_of(arr,1);
        niveles := matrix.create_numeric_list(cant);

	--RAISE NOTICE 'Nivelando una fila';
        
        i := 0;
        countya := 0;

        LOOP
            ya[i] = FALSE;
            EXIT WHEN i >= cant;
            i := i + 1;
        END LOOP;

        LOOP
            
            IF criterio = TRUE THEN
		--RAISE NOTICE 'Criterio TRUE buscando minimo...';
                -- buscando minimo
                optimo := 0;
                primero := TRUE;
                i := 1;
                LOOP
                    IF ya[i] = FALSE THEN
                        IF optimo > arr[i] or primero = TRUE THEN 
                            optimo := arr[i];
                            primero := FALSE;
                        END IF;
                    END IF;

                    EXIT WHEN i >= cant;
                    i := i + 1;
                    
                END LOOP;
            ELSE
		--RAISE NOTICE 'Criterio FALSE buscando maximo...';
                -- buscando maximo
                optimo := 0;
                primero := TRUE;
                i := 1;
                LOOP
                    IF ya[i] = FALSE THEN
                        IF optimo < arr[i] or primero = TRUE THEN 
                            optimo := arr[i];
                            primero := FALSE;
                        END IF;
                    END IF;
                    
                    EXIT WHEN i >= cant;
                    i := i + 1;
                    
                END LOOP;
            END IF;

	    --RAISE NOTICE 'Optimo %', optimo;
	    --RAISE NOTICE 'Criterio TRUE buscando minimo... %', countya;
	    
            i := 1;
            LOOP
                IF ya[i] = FALSE THEN
                    IF optimo = arr[i] THEN 
                        niveles[i] := nivel;
                        ya[i] := TRUE;
                        countya := countya + 1;                    
                    END IF;
                END IF;
                
                EXIT WHEN i >= cant;
                i := i + 1;
            END LOOP;
            nivel := nivel + 1;
            EXIT WHEN countya = cant;
        END LOOP;
        RETURN niveles;
    END;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE
  COST 100;

