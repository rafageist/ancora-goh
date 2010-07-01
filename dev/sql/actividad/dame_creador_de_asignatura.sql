CREATE OR REPLACE FUNCTION dame_creador_de_asignatura(idasig varchar) returns varchar as
$$
	select creador from asignatura where id = $1;
$$
language 'sql';



