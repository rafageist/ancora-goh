
CREATE OR REPLACE FUNCTION agrega_actividad(idplantilla VARCHAR, idprofesor VARCHAR, idlugar VARCHAR, pdia INTEGER, pturno INTEGER, idgrupo VARCHAR, idrecursos VARCHAR[], momento DOUBLE PRECISION, pcreador VARCHAR) RETURNS void as
$$
    DECLARE pid VARCHAR;
        listaidbrg VARCHAR[];
        cant BIGINT;
        i BIGINT; j INTEGER;
        rplantilla record;
        r record;
    BEGIN

        SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = idplantilla;

        pid := crear_id_de_replica ('ancoragoh','actividad','id','actividad');
        INSERT INTO actividad (id, plantilla, profesor, lugar, dia, turno, momento_de_colocacion, creador, grupo) VALUES (pid, idplantilla, idprofesor, idlugar, pdia, pturno, momento, pcreador, idgrupo);

        cant := matrix.get_length_of(idrecursos, 1);

        IF cant > 0 THEN
		i := 1;
		LOOP
		    INSERT INTO otros_recursos_usados (otro_recurso, actividad) VALUES (idrecursos[i], pid);
		    EXIT WHEN i = cant;
		    i := i + 1;
		END LOOP;
        END IF;
    END;
$$
LANGUAGE 'plpgsql';