CREATE OR REPLACE FUNCTION dame_cant_otros_recursos_imprescindibles(idplantilla VARCHAR) RETURNS BIGINT AS
$$
	declare r record;
	begin
		SELECT sum(otro_grupo_de_recursos_para_actividad.necesarios) as s INTO r FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = $1 AND imprescindibles = TRUE;
		IF NOT FOUND OR r.s IS NULL THEN
			return 0;
		END IF;
		return r.s;
	end;
$$
LANGUAGE 'plpgsql';