


--
-- Devuelve el id del lugar seg??rigenes[] que es menos costoso de llegar a los destinos[]
--
/*

INSERT INTO lugar (id) VALUES ('201'),('202'),('203'),('204');
INSERT INTO costo_de_traslado (origen,destino,costo) VALUES 
    ('201','202',10),
    ('202','201',10),
    ('201','203',10),
    ('203','201',10),
    ('201','204',5),
    ('204','201',5),
    ('202','203',20),
    ('203','202',20),
    ('202','204',15),
    ('204','202',15),
    ('203','204',6),
    ('204','203',6);
*/

-- SELECT busca_lugar_menos_costoso(array['203','202'],array['201','202','203']);
CREATE OR REPLACE FUNCTION busca_lugar_menos_costoso(origenes VARCHAR[], destinos VARCHAR[]) RETURNS VARCHAR as
$$
    DECLARE i BIGINT; j BIGINT;
        min BIGINT; pos BIGINT;
        cant_origenes BIGINT; 
        cant_destinos BIGINT;
        costo BIGINT;
        r record;
    BEGIN
        cant_origenes := matrix.get_length_of(origenes, 1);
        cant_destinos := matrix.get_length_of(destinos, 1);
    
        min := 0;
        pos := -1;
        
        i := 1;
        IF cant_origenes > 0 THEN
		LOOP
		    costo := 0;
		    j := 1;
		    IF cant_destinos > 0 THEN
			    LOOP
				SELECT * INTO r FROM costo_de_traslado WHERE origen = origenes[i] AND destino = destinos[j];
				
				costo := costo + r.costo;
				
				EXIT WHEN j >= cant_destinos;
				j := j + 1;
			    END LOOP;
		    END IF;
		    IF min > costo OR pos = -1 THEN
			min = costo;
			pos = i;
		    END IF;
		    
		    EXIT WHEN i >= cant_origenes;
		    i := i + 1;
		END LOOP;
        END IF;

        IF pos = -1 THEN
            RETURN '';
        END IF;
        
        RETURN origenes[pos];
    END;
$$
LANGUAGE 'plpgsql';