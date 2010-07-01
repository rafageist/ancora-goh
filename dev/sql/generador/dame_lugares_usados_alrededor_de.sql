

CREATE OR REPLACE FUNCTION dame_lugares_usados_alrededor_de(brigadas VARCHAR[], idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS VARCHAR[] as
$$
    DECLARE arr VARCHAR[];
        cantbrg BIGINT;
        i BIGINT;
        j INTEGER;
        rperiodo record;
        r record;
    BEGIN
        SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;
        
        cantbrg := matrix.get_length_of(brigadas,1);

        i := 0;
        LOOP
            -- buscando el anterior
            j := pturno - 1;
            IF j > 0 THEN
		    LOOP
			SELECT * INTO r FROM actividad WHERE idperiodo = dame_periodo_plantilla_de_actividad(plantilla) AND dia = pdia AND turno = j AND grupo = dame_grupo(brigadas[i], dame_clasif_plantilla_de_actividad(plantilla));

			IF FOUND THEN
			    arr := array_append(arr, r.lugar);
			    EXIT;
			END IF;
			
			EXIT WHEN j <= 1;
			j :=j - 1;
		    END LOOP;
	    END IF;
            -- buscando el siguiente
            j := pturno + 1;
            IF j <= rperiodo.cant_turnos THEN
		    LOOP
			SELECT * INTO r FROM actividad WHERE idperiodo = dame_periodo_plantilla_de_actividad(plantilla) AND dia = pdia AND turno = j AND grupo = dame_grupo(brigadas[i], dame_clasif_plantilla_de_actividad(plantilla));

			IF FOUND THEN
			    arr := array_append(arr, r.lugar);
			    EXIT;
			END IF;
			
			EXIT WHEN j >= rperiodo.cant_turnos;
			j := j + 1;
		    END LOOP;
            END IF;
            
            EXIT WHEN i = cantbrg;
            i := i + 1;
        END LOOP;
        
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';