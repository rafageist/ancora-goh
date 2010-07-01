create or replace function dame_nivel_de_grupo(idgrupo varchar) returns varchar as
$$
	select nivel from grupo where id = $1;
$$
language 'sql';
