-- Function: and_mpi(character varying[], integer, integer, character varying, bigint, boolean)

-- DROP FUNCTION and_mpi(character varying[], integer, integer, character varying, bigint, boolean);

CREATE OR REPLACE FUNCTION and_mpi(listaidbrg character varying[], cd integer, ct integer, idplantilla character varying, zona bigint, excepto boolean)
  RETURNS boolean[] AS
$BODY$
    DECLARE mpi BOOLEAN[][];
        r RECORD;
        i INTEGER;
        j INTEGER;
        k INTEGER;
        cantBrg BIGINT;
        pi BOOLEAN;
        lugares VARCHAR[];
        cantLugares BIGINT;
        profesores VARCHAR[];
        cantProfes BIGINT;
        grupos VARCHAR[];
    BEGIN

	--RAISE NOTICE '     [INFO] AND MPI ...';
	
        cantBrg := matrix.get_length_of(listaidbrg, 1);

	--RAISE NOTICE '          [INFO] AND MPI: obtengo los lugares y profesores asignados a la plantilla ...';
	
        -- obtengo los lugares y profesores asignados a la plantilla 
        lugares := dame_lista_id_lugares_asignados(idplantilla);
        cantLugares := matrix.get_length_of(lugares, 1);

        profesores := dame_lista_id_profes_asignados(idplantilla);
        cantProfes := matrix.get_length_of(profesores, 1);

	--RAISE NOTICE '          [INFO] AND MPI: obtengo los grupos involucrados segun la plantilla y las brigadas ...';
        -- obtengo los grupos involucrados segun la plantilla y las brigadas
        -- generalmente debe ser uno solo

        grupos := dame_grupos_de_brigadas (listaidbrg, idplantilla);

        --RAISE NOTICE '          [INFO] AND MPI: creo la mpi ...';
        -- creo la mpi
        mpi := matrix.create_boolean_matrix(cd,ct);

	--RAISE NOTICE '          [INFO] AND MPI: inicializo la mpi con TRUE en todas las casillas ...';
        -- inicializo la mpi con TRUE en todas las casillas
        i := 1;

        LOOP
            j := 1;
            LOOP
                mpi[i][j] := TRUE;
                EXIT WHEN j = ct;
                j := j + 1;
            END LOOP;
            
            EXIT WHEN i = cd;
            i := i + 1;
        END LOOP;

        --RAISE NOTICE '          [INFO] AND MPI: recorro cada brigada comprobando la disponibilidad en cada turno ...';
        -- recorro cada brigada comprobando la disponibilidad en cada turno
        k := 1;
        LOOP
	    --RAISE NOTICE '          [INFO] AND MPI: % verifico para cada turno si es posible inicio ...', k;
            -- verifico para cada turno si es posible inicio
            i := 1;
            LOOP
                j := 1;
                LOOP
                    pi := posible_inicio(idplantilla, i, j, listaidbrg[k], lugares, profesores);

                    IF excepto = TRUE THEN
                        SELECT * INTO r FROM actividad WHERE plantilla = idplantilla AND dia = i AND turno = j;
                        IF r.plantilla = idplantilla THEN
                            pi = TRUE;
                        END IF;
                    END IF;

		    --RAISE NOTICE '          [INFO] AND MPI: %  me quedo con los lugares que tengan capacidad ...', k;
                    -- me quedo con los lugares que tengan capacidad
                    lugares := quita_segun_capacidad(listaidbrg, lugares);
                    
		    --RAISE NOTICE '          [INFO] AND MPI: %  me quedo con los lugares que no estan prohibidos ...', k;
                    -- me quedo con los lugares que no estan prohibidos

                    lugares := quita_segun_prohibidos(grupos, lugares);

                    IF matrix.get_length_of(lugares, 1) = 0 THEN
                        pi := FALSE;
                    END IF;

                    mpi[i][j] := mpi[i][j] AND pi;

                    EXIT WHEN j = ct;
                    j := j + 1;
                END LOOP;
                
                EXIT WHEN i = cd;
                i := i + 1;
            END LOOP;

            EXIT WHEN k = cantBrg;
            k := k + 1;
        END LOOP;
        
        RETURN mpi;
    END;
    
$BODY$
  LANGUAGE 'plpgsql' VOLATILE
  COST 100;

