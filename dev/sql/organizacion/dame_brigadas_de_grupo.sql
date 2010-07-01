create or replace function dame_brigadas_de_grupo(idgrupo varchar) returns setof brigada as
$$ 
	select brigada.* from brigada_de_grupo inner join brigada on brigada_de_grupo.brigada = brigada.id
	where brigada_de_grupo.grupo = $1;
$$
language 'sql';
