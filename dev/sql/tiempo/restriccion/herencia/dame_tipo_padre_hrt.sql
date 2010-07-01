CREATE or replace function dame_tipo_padre_hrt(pid VARCHAR) RETURNS VARCHAR AS
$$
    DECLARE t VARCHAR;
    r record;
    BEGIN
        SELECT * INTO r FROM herencia_de_restricciones WHERE id = pid;

        t := iif(r.padre_brigada is null,'','BRIGADA');
        t := iif(r.padre_profesor is null,'','PROFESOR');
        t := iif(r.padre_lugar is null,'','LUGAR');
        t := iif(r.padre_otro_recurso is null,'','OTRORECURSO');
        
        RETURN t;

    END;
$$
LANGUAGE 'plpgsql';