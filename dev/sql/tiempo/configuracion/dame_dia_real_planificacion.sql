-- devuelve el dia real (fecha) segun un dia relativo de la planificacion
CREATE OR REPLACE FUNCTION dame_dia_real_planificacion(pid VARCHAR, dia INTEGER) RETURNS BIGINT as
$$
    SELECT fecha_inicial + $2 - 1  FROM planificacion WHERE id = $1;
$$
LANGUAGE 'sql';