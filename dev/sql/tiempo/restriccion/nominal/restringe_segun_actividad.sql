CREATE OR REPLACE FUNCTION restringe_segun_actividad(
    idactividad VARCHAR, 
    idplantilla VARCHAR, 
    idprofesor VARCHAR, 
    idlugar VARCHAR, 
    pdia INTEGER, 
    pturno INTEGER, 
    idgrupo VARCHAR
) 
RETURNS VOID AS
$$
DECLARE 
    pid VARCHAR;
    listaidbrg VARCHAR[];
    cant BIGINT;
    i BIGINT; j INTEGER;
    rplantilla record;
    idperiodo varchar;
    rclasificacion record;
    r record;
BEGIN

    SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = idplantilla;
    SELECT * INTO rclasificacion FROM clasificacion_de_actividad WHERE id = rplantilla.clasificacion;
    
    idperiodo := dame_periodo_plantilla_de_actividad(idplantilla);
    -- restringir a los implicados

    -- restringiendo a las brigadas
    listaidbrg := dame_lista_id_brigadas_de_grupo(idgrupo);
    cant := matrix.get_length_of(listaidbrg, 1);

    i := 1;
    LOOP
        j := pturno;

        LOOP
            pid := crear_id_de_replica('ancoragoh','restriccion_de_brigada','id','restriccion_de_brigada');
            INSERT INTO restriccion_de_brigada (id, brigada, periodo, dia, turno, valor, por_actividad, actividad) VALUES (pid, listaidbrg[i], idperiodo, pdia, j, 1, TRUE, idactividad);
            INSERT INTO restriccion_por_actividad_de_brigada (restriccion_nominal, actividad) VALUES (pid, idactividad);
            
            EXIT WHEN j = pturno + rclasificacion.duracion - 1;
            j := j + 1;
        END LOOP;
        
        EXIT WHEN i = cant;

        i := i + 1;
    END LOOP;
    
    -- restringiendo al profesor y lugar
    j := pturno;

    LOOP
        pid := crear_id_de_replica('ancoragoh','restriccion_de_profesor','id','restriccion_de_profesor');
        INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad, actividad) VALUES (pid, idprofesor, idperiodo, pdia, j, 1, TRUE, idactividad);
        INSERT INTO restriccion_por_actividad_de_profesor (restriccion_nominal, actividad) VALUES (pid, idactividad);
        
        pid := crear_id_de_replica('ancoragoh','restriccion_de_lugar','id','restriccion_de_lugar');
        INSERT INTO restriccion_de_lugar (id, lugar, periodo, dia, turno, valor, por_actividad, actividad) VALUES (pid, idlugar, idperiodo, pdia, j, 1, TRUE, idactividad);
        INSERT INTO restriccion_por_actividad_de_lugar (restriccion_nominal, actividad) VALUES (pid, idactividad);
        
        EXIT WHEN j = pturno + rclasificacion.duracion - 1;
        j := j + 1;
    END LOOP;
   
END;
$$
LANGUAGE 'plpgsql';