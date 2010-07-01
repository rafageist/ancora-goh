       CREATE OR REPLACE FUNCTION dame_idobjeto_de_restriccion_de_periodo(pid varchar) returns varchar as
       $$
            SELECT periodo FROM restriccion_de_periodo WHERE id = $1;
       $$
       LANGUAGE 'sql';