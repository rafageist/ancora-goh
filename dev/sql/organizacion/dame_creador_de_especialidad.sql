create or replace function dame_creador_de_especialidad(esp varchar) returns varchar as 
$$
	select creador from especialidad where id = $1;
$$
language 'sql';