CREATE OR REPLACE FUNCTION dame_max_priori_otro_recurso(idgr character varying)
  RETURNS integer AS
$BODY$
	select max(prioridad) from otro_recurso_para_actividad where grupo_de_recursos = $1;
$BODY$
  LANGUAGE 'sql' VOLATILE
  COST 100;