-- Records the occupancy of an other resource used by an activity (#24, decision
-- D-05 option B). Called by the RULE actualiza_restricciones_por_uso_de_otro_recurso
-- (ancoragoh.sql) on every INSERT INTO otros_recursos_usados, exactly as
-- restringe_segun_actividad records brigades, teacher and place: one per-activity
-- nominal restriction (valor 1, por_actividad TRUE) for every slot of the
-- activity's duration, linked through restriccion_por_actividad_de_otro_recurso.
-- Deleting the activity removes them (ON DELETE CASCADE). A slot where the
-- resource is already restricted or booked violates the unique key
-- (otro_recurso, periodo, dia, turno) of restriccion_de_otro_recurso, so a
-- conflicting booking fails and nothing of it is kept.
-- The 2010 body never compiled (EXIT outside a loop, undefined idplantilla,
-- non-existent crea_id_de_replica, columns that do not exist); it is kept in
-- Git history and described in docs/modernization/sql-reconciliation.md (D-05).
CREATE OR REPLACE FUNCTION restringe_por_uso_de_otro_recurso(
    idactividad VARCHAR,
    idrecurso VARCHAR
)
RETURNS void as
$$
DECLARE
    j INTEGER;
    ractividad RECORD;
    rclasificacion RECORD;
    idperiodo VARCHAR;
    pid VARCHAR;
BEGIN
    SELECT * INTO ractividad FROM actividad WHERE actividad.id = idactividad;
    SELECT clasificacion_de_actividad.* INTO rclasificacion
      FROM plantilla_de_actividad
     INNER JOIN clasificacion_de_actividad ON clasificacion_de_actividad.id = plantilla_de_actividad.clasificacion
     WHERE plantilla_de_actividad.id = ractividad.plantilla;

    idperiodo := dame_periodo_plantilla_de_actividad(ractividad.plantilla);

    j := ractividad.turno;
    LOOP
        pid := crear_id_de_replica('ancoragoh','restriccion_de_otro_recurso','id','restriccion_de_otro_recurso');
        INSERT INTO restriccion_de_otro_recurso (id, otro_recurso, periodo, dia, turno, valor, por_actividad, actividad)
             VALUES (pid, idrecurso, idperiodo, ractividad.dia, j, 1, TRUE, idactividad);
        INSERT INTO restriccion_por_actividad_de_otro_recurso (restriccion_nominal, actividad) VALUES (pid, idactividad);

        EXIT WHEN j >= ractividad.turno + rclasificacion.duracion - 1;
        j := j + 1;
    END LOOP;
END;
$$
LANGUAGE 'plpgsql';
