       CREATE OR REPLACE FUNCTION dame_idobjeto_de_restriccion_de_brigada(pid varchar) returns varchar as
       $$
            SELECT brigada FROM restriccion_de_brigada WHERE id = $1;
       $$
       LANGUAGE 'sql';