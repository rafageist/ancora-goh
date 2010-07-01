CREATE OR REPLACE FUNCTION dame_total_otros_recursos_asignados(idplantilla VARCHAR) RETURNS NUMERIC AS
$$
	declare r record;
	begin
		 SELECT sum(dame_cant_otros_recursos_asignados(id)) as s INTO r FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = $1;
		IF NOT FOUND OR r.s IS NULL THEN
			return 0;
		END IF;
		return r.s;
	end;
$$
LANGUAGE 'plpgsql';