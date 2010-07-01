CREATE or replace function dame_tipo_hijo_hrt(pid VARCHAR) RETURNS VARCHAR AS
$$
    DECLARE t VARCHAR;
        r record;
    BEGIN
        SELECT * INTO r FROM herencia_de_restricciones WHERE id = pid;
        
        t := iif(r.hijo_brigada is null,'','BRIGADA');
        t := iif(r.hijo_profesor is null,'','PROFESOR');
        t := iif(r.hijo_lugar is null,'','LUGAR');
        t := iif(r.hijo_otro_recurso is null,'','OTRORECURSO');
        
        RETURN t;

    END;
$$
LANGUAGE 'plpgsql';