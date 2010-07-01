
CREATE OR REPLACE FUNCTION dame_lugares_utilizados_en_el_dia(pgrupo VARCHAR, periodo VARCHAR, pdia INTEGER) RETURNS VARCHAR[] AS
$$
    DECLARE
        arr VARCHAR[];
        r record;
        
    BEGIN
        FOR r IN SELECT * FROM actividades WHERE grupo = pgrupo AND dia = pdia AND dame_periodo_de_plantilla(plantilla) = periodo LOOP
            arr := array_append (arr,r.lugar);
        END LOOP;
        
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';