    CREATE OR REPLACE FUNCTION dame_hora_inicial_periodo(pid VARCHAR) RETURNS DOUBLE PRECISION as
    $$
        SELECT hora_inicial FROM periodo WHERE id = $1;
    $$
    LANGUAGE 'sql'; 