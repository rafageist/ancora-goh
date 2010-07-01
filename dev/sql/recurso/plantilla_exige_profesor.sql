CREATE or replace function plantilla_exige_profesor (id VARCHAR) RETURNS BOOLEAN as
$$
    SELECT exige_profesor FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';