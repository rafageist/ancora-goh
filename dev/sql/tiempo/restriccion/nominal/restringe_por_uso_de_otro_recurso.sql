CREATE OR REPLACE FUNCTION restringe_por_uso_de_otro_recurso(
    idactividad VARCHAR, 
    idrecurso VARCHAR
) 
RETURNS void as
$$
DECLARE 
    cant BIGINT;
    i BIGINT; j INTEGER;
    rplantilla record;
    ractividad RECORD;
    r record;
    pid varchar;
BEGIN
    SELECT * INTO ractividad FROM actividad WHERE id = idactividad;
    SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = idplantilla;

    j := ractividad.turno;

    LOOP
       pid := crea_id_de_replica("ancoragoh","restriccion_de_otro_recurso","id","restriccion_de_otro_recurso");
       INSERT INTO restriccion_de_otro_recurso (otro_recurso, periodo, dia, turno, valor, por_actividad, actividad) VALUES (idrecurso, ractividad.periodo, ractividad.dia, j, 1, TRUE, idactividad);
       INSERT INTO restriccion_por_actividad_de_otro_recurso (restriccion_nominal, actividad) VALUES (pid, idactividad);

       EXIT WHEN j = ractividad.turno + rplantilla.duracion - 1;
       j := j + 1;
    END LOOP;
    
    EXIT WHEN i = cant;
    
END;
$$
LANGUAGE 'plpgsql';