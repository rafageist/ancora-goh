create or replace function dame_creador_de_clasifact(idclasif varchar) returns varchar as 
$$
	select creador from clasificacion_de_actividad where id = $1;
$$
language 'sql';