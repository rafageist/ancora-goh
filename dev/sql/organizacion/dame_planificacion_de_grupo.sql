create or replace function dame_planificacion_de_grupo(idgrupo varchar) returns varchar as
$$
	select planificacion from grupo where id = $1;
$$
language 'sql';