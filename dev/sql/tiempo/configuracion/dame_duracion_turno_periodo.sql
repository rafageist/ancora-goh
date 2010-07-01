    CREATE OR REPLACE FUNCTION dame_duracion_turno_periodo(pid VARCHAR) RETURNS DOUBLE PRECISION as
    $$
        SELECT duracion_del_turno FROM periodo WHERE id = $1;
    $$
    LANGUAGE 'sql';