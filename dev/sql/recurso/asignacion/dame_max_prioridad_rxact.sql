CREATE OR REPLACE FUNCTION dame_max_prioridad_rxact(idplantilla character varying) RETURNS integer AS
$$
	select max(prioridad) from otro_grupo_de_recursos_para_actividad where plantilla = $1;
$$
 LANGUAGE 'sql';