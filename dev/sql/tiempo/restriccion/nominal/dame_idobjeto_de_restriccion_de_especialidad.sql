       CREATE OR REPLACE FUNCTION dame_idobjeto_de_restriccion_de_especialidad(pid varchar) returns varchar as
       $$
            SELECT especialidad FROM restriccion_de_especialidad WHERE id = $1;
       $$
       LANGUAGE 'sql';