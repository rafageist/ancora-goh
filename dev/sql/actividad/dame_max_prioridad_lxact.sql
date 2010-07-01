create or replace function dame_max_prioridad_lxact(idplantilla varchar) returns integer as
$$
	select max(prioridad) from lugar_para_actividad where plantilla = $1;
$$
language 'sql';
