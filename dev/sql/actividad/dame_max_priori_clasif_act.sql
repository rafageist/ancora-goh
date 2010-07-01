CREATE or replace function dame_max_priori_clasif_act(pcreador varchar) RETURNS INTEGER as
$$
    DECLARE r record;
    BEGIN
        
        SELECT max(prioridad) as m into r FROM clasificacion_de_actividad WHERE creador='admin-superuser' GROUP BY creador;

        IF r.m is null THEN
            RETURN 1;
        END IF;
        
        RETURN r.m;
    END;
$$
LANGUAGE 'plpgsql';