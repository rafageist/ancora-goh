CREATE OR REPLACE FUNCTION lugar_permanece_en(plugar VARCHAR, pperiodo VARCHAR, pdia INTEGER, pturno INTEGER, duracion INTEGER) RETURNS BOOLEAN AS
$$
    DECLARE i INTEGER;
	v INTEGER;
    BEGIN
	i := 1;
	LOOP
		v := valor_restriccion_lugar(plugar, pperiodo, pdia, pturno);
		IF v > 0 THEN
			RETURN FALSE;
		END IF;
		EXIT WHEN i >= pturno + duracion;
		i := i + 1;
	END LOOP;
	
        RETURN TRUE;
    END;
$$
LANGUAGE 'plpgsql';