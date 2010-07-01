-- select dame_zpriori(id,'periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29'), * from clasificacion_de_actividad;

-- Function: dame_zpriori(character varying, character varying)

-- DROP FUNCTION dame_zpriori(character varying, character varying);

CREATE OR REPLACE FUNCTION dame_zpriori(clasif character varying, idperiodo character varying)
  RETURNS integer[] AS
$BODY$
    DECLARE zp INTEGER[][];
        i BIGINT; j BIGINT;
        CD INTEGER; CT INTEGER;
        r record;
    BEGIN 
        
        CD := dame_cant_dias(idperiodo);
        CT := dame_cant_turnos(idperiodo);
        
        zp := matrix.create_numeric_matrix(CD,CT);

	i:= 1;
	LOOP
		j:=1;
		LOOP
			zp[i][j] := 1;
			EXIT WHEN j = CT;
			j:=j+1;
		END LOOP;
		EXIT WHEN i = CD;
		i := i + 1;
	END LOOP;
    
        FOR r IN SELECT * FROM zpriori WHERE clasificacion = clasif AND periodo = idperiodo LOOP
            zp[r.dia][r.turno] := r.valor;
        END LOOP;
        
        RETURN zp;
    END;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE
  COST 100;
