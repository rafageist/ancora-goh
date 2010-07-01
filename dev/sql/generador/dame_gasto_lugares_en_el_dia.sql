
CREATE OR REPLACE FUNCTION dame_gasto_lugares_en_el_dia(lugares VARCHAR[], periodo VARCHAR, pdia INTEGER) RETURNS BIGINT[] AS
$$
    DECLARE
        arr BIGINT[];
        u BIGINT;
        i BIGINT;
        cant BIGINT;
    BEGIN
        cant := matrix.get_length_of(lugares, 1);

        arr := matrix.create_numeric_list(cant);
        
        i := 1;
        LOOP            
            SELECT * FROM actividad WHERE dia = pdia AND lugar = lugares[i] AND dame_periodo_de_plantilla(plantilla) = periodo GROUP BY lugar;

            IF NOT FOUND THEN
                u:=0;
            ELSE
                GET DIAGNOSTICS u := ROW_COUNT;
            END IF;

            arr[i] := u;
            
            EXIT WHEN i = cant;
            i:= i + 1;
        END LOOP;
    
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';