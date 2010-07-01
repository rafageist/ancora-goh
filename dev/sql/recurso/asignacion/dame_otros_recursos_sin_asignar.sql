CREATE OR REPLACE FUNCTION dame_otros_recursos_sin_asignar(idplantilla character varying)
  RETURNS SETOF otro_recurso AS
$BODY$ 
	declare r record;
	begin

	for r in select * from otro_recurso where esta_otro_recurso_asignado(id, $1) = false loop
		return next r;
	end loop;
	end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE
  COST 100
  ROWS 1000;