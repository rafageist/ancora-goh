CREATE OR REPLACE FUNCTION rango(idplantilla VARCHAR, idgrupo VARCHAR, minimo INTEGER, maximo INTEGER) RETURNS TRango AS
$$    
    DECLARE ran TRango;
        i INTEGER; 
        rplantilla record;
        rperiodo record;
        rdesglose record;
        n INTEGER;
        arr INTEGER[];
        CD INTEGER;
        r record;
        w record;
        c INTEGER;
        da INTEGER; db INTEGER; dif INTEGER;
    BEGIN

        SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = idplantilla;
        SELECT * INTO rdesglose FROM desglose WHERE id = rplantilla.desglose;
        SELECT * INTO rperiodo FROM periodo WHERE id = rdesglose.periodo;

        ran.ini = 1;
        CD := rperiodo.cant_dias;
        ran.fin = CD;
        n := rplantilla.orden;
        
        IF rdesglose.respetar_orden = FALSE THEN
            RETURN ran;
        END IF;
        
        arr := matrix.create_numeric_list(CD);

        FOR r IN SELECT * FROM actividad WHERE grupo = idgrupo AND dame_desglose_de_plantilla(plantilla) = rdesglose.id LOOP
            SELECT * INTO w FROM plantilla_de_actividad WHERE id = r.plantilla;
            arr[r.dia] := w.orden;
        END LOOP;

        c := matrix.get_count_of_value_in(arr, 0);

        -- SI c = 0 ninguna actividad, el rango es todo el periodo

        IF c = 1 THEN
            -- una actividad
            i := 1;
            LOOP
                IF arr[i]<>0 THEN
                    IF arr[i] < n THEN
                        ran.ini = i + minimo;
                        ran.fin = CD;
                    ELSE
                        ran.ini = 1;
                        ran.fin = i - minimo;
                    END IF;
                    
                    EXIT;
                END IF;
                EXIT WHEN i = CD;
                i := i + 1;
            END LOOP;
        END IF;

        IF c > 1 THEN
            -- mas de una actividad

            da := 0;
            db := CD + 1;

            i := 1;
            LOOP
                IF arr[i] = n - 1 AND arr[i] <> 0 THEN
                    da = i;
                    EXIT;
                END IF;
                EXIT WHEN i = CD;            
                i := i + 1;
            END LOOP;


            i := 1;
            LOOP
                IF arr[i] = n + 1 AND arr[i] <> 0 THEN
                    db = i;
                    EXIT;
                END IF;
                EXIT WHEN i = CD;            
                i := i + 1;
            END LOOP;            

            dif := db - da;

            ran.ini := da + minimo;

            IF dif <= maximo * 2 THEN
                ran.fin = db - minimo;
            ELSE
                ran.fin = da + maximo;
            END IF;
        END IF;

        RETURN ran;
    
    END;
$$
LANGUAGE 'plpgsql';