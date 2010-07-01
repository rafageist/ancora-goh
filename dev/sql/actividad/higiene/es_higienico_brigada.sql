CREATE or replace function es_higienico_brigada(idbrigada VARCHAR, idplantilla VARCHAR, pdia INTEGER) RETURNS BOOLEAN as
$$
    DECLARE r record;
        r1 record;
        h record;
        idperiodo VARCHAR;
        rclasif VARCHAR;
        gasto INTEGER;
    BEGIN
        idperiodo := dame_periodo_plantilla_de_actividad(idplantilla);
        gasto := dame_gasto_de_plantilla(idplantilla);

        FOR r1 IN SELECT * FROM actividad WHERE dame_periodo_plantilla_de_actividad(plantilla) = idperiodo AND dia = pdia AND matrix.get_pos_of(dame_lista_id_brigadas_de_grupo(grupo), idbrigada) > -1 LOOP
            gasto := gasto + dame_gasto_de_plantilla(idplantilla);            
        END LOOP;

        SELECT * INTO h FROM higiene_para_brigada WHERE periodo = idperiodo and brigada = idbrigada;

        IF FOUND THEN
            IF h.gasto_maximo_diario < gasto THEN
                RETURN FALSE;
            END IF;
        END IF;
        
        RETURN TRUE;
    END;
$$
LANGUAGE 'plpgsql';