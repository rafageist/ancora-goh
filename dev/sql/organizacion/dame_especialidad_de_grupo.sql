create or replace function dame_especialidad_de_grupo(idgrupo varchar) returns varchar as
$$
	select especialidad from grupo where id = $1;
$$
language 'sql';