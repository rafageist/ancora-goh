
CREATE OR REPLACE FUNCTION dame_lista_id_otros_recursos_usados(idactividad character varying)
  RETURNS character varying[] AS
$BODY$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM otros_recursos_usados WHERE actividad = idactividad LOOP
            arr := array_append(arr,r.otro_recurso);
        END LOOP;

        RETURN arr;
    END;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE
  COST 100;

