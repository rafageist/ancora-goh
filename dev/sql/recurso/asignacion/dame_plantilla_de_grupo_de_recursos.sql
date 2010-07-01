create or replace function dame_plantilla_de_grupo_de_recursos(idgrupo varchar) returns varchar as
$$
	select plantilla from otro_grupo_de_recursos_para_actividad where id = $1;
$$
language 'sql';