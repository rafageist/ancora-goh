create or replace function dame_max_orden_act(iddesglose varchar) returns integer as
$$ 
	select max(orden) from plantilla_de_actividad where desglose = $1;
$$
language 'sql';
