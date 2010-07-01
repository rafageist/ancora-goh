create or replace function dame_max_prioridad_de_pxact(idplantilla varchar) returns integer as
$$
	select max(prioridad) from profesor_para_actividad where plantilla = $1;
$$
language 'sql';