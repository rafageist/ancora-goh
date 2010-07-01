CREATE OR REPLACE FUNCTION dame_cant_profes_asignados_a_plantilla(idplantilla VARCHAR) RETURNS BIGINT AS
$$
    SELECT count(id) FROM profesor_para_actividad WHERE plantilla = $1;
$$
LANGUAGE 'sql';