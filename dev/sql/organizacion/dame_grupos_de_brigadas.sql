CREATE OR REPLACE FUNCTION dame_grupos_de_brigadas(brigadas VARCHAR[], idplantilla VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        cantBrg BIGINT;
        i BIGINT;
        g VARCHAR;
        clasif VARCHAR;
    BEGIN 
        cantBrg := matrix.get_length_of(brigadas,1);
        clasif := dame_clasif_plantilla_de_actividad(idplantilla);

        i := 1;
        LOOP
            g := dame_grupo(brigadas[i], clasif);

            IF g IS NOT NULL THEN
                IF matrix.get_pos_of(arr, g) <> -1 THEN
                    arr := array_append(arr, g);
                END IF;
            END IF;
            EXIT WHEN i = cantBrg;
            i := i + 1;
        END LOOP;
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';