CREATE OR REPLACE FUNCTION sube_prioridad_clasif(idclasif character varying)
  RETURNS boolean AS
$BODY$
	declare 
		r1 record;
		r2 record;
	begin
		SELECT * INTO r1 FROM clasificacion_de_actividad WHERE id = idclasif;

		IF r1.prioridad > 1 THEN
			SELECT * INTO r2 FROM clasificacion_de_actividad WHERE creador = r1.creador AND prioridad = r1.prioridad - 1;
			UPDATE clasificacion_de_actividad SET prioridad = r1.prioridad WHERE id = r2.id;
			UPDATE clasificacion_de_actividad SET prioridad = r2.prioridad WHERE id = r1.id;
		END IF;
		
		return true;
	end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE
  COST 100;
