CREATE or replace function plantilla_exige_lugar(id VARCHAR) RETURNS BOOLEAN as
$$
    SELECT exige_lugar FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';