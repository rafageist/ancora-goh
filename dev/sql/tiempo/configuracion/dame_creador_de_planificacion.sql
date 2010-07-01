CREATE or replace function dame_creador_de_planificacion(id VARCHAR) RETURNS VARCHAR as
$$
    SELECT creador FROM planificacion WHERE id = $1;
$$
LANGUAGE 'sql';