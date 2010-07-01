CREATE OR REPLACE FUNCTION dia_minimo_impacto(idclasif VARCHAR, idesp VARCHAR, idnivel VARCHAR, idperiodo VARCHAR, dia_ini INTEGER, dia_fin INTEGER) RETURNS INTEGER AS
$$
    DECLARE
        i BIGINT; j BIGINT; k BIGINT; l BIGINT; ll BIGINT; dia INTEGER;
        max BIGINT;
        cant BIGINT;
        r record;
        CD INTEGER;
        CT INTEGER;
        rperiodo RECORD;
        cantBrg BIGINT;
        maxzpriori INTEGER;
        zp INTEGER[][];
        impactodiario INTEGER[];
        min bigint;
        impacto bigint;
    BEGIN
 
        zp := dame_zpriori(idclasif, idperiodo);

        SELECT * INTO r FROM zpriori WHERE clasificacion = idclasif AND periodo = idperiodo ORDER BY valor DESC;

        maxzpriori := r.valor;
            
        SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;

        CD := rperiodo.cant_dias;
        CT := rperiodo.cant_turnos;
        
        SELECT count(id) as c INTO r FROM brigada WHERE especialidad = idesp AND nivel = idnivel;

        cantBrg := r.c;

        i := dia_ini;
        LOOP
            j := 1;
            LOOP
                zp[i][j] := maxzpriori - zp[i][j];
                EXIT WHEN j = CT;
                j := j + 1;
            END LOOP;
            
            EXIT WHEN i = dia_fin;
            i := i + 1;
        END LOOP;


        -- En los turnos restringidos el impacto es cero tanto para el grupoXClasif en curso
        -- como para los dem?
        -- Poniendo cero seg??estricciones de la clasificacion de actividad
        FOR r IN SELECT * FROM restriccion_de_clasif_act WHERE clasificacion = idclasif AND periodo = idperiodo LOOP
            IF r.valor > 0 THEN 
                zp[r.dia][r.turno] := 0;
            END IF;
        END LOOP;
    
        -- Poniendo cero seg??estricciones de las brigadas
        FOR r IN SELECT * FROM restriccion_de_brigada WHERE dame_especialidad_de_brigada(brigada) = idesp AND dame_nivel_de_brigada(brigada) = idnivel LOOP
            IF r.valor > 0 THEN 
                zp[r.dia][r.turno] := 0;
            END IF;
        END LOOP;

        -- Poniendo cero seg??estricciones del periodo
        FOR r IN SELECT * FROM restriccion_de_periodo WHERE periodo = idperiodo LOOP
            IF r.valor > 0 THEN 
                zp[r.dia][r.turno] := 0;
            END IF;
        END LOOP;

        -- calculando impacto diario y minimizando
        
        i := dia_ini;
        min := -1;
        dia := 0;
        LOOP
            impacto := 0;
            j := 1;
            LOOP
                impacto := impacto + zp[i][j];
                EXIT WHEN j = CT;
                j := j + 1;
            END LOOP;

            -- puede que haya otros minimos, pero me quedo con el primero porque est?as cerca
            -- del origen del periodo (recordar que se colocan actividades por nivel)
            
            IF min = -1 OR min > impacto THEN
                min := impacto;
                dia := i;
            END IF;
            
            EXIT WHEN i = dia_fin;
            i := i + 1;
        END LOOP;

        RETURN dia;
    END;
$$
LANGUAGE 'plpgsql';
