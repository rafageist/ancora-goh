CREATE OR REPLACE FUNCTION dame_lugares_no_asignados_a(idplantilla character varying)
  RETURNS SETOF lugar AS
$$
	declare pcreador varchar;
		r record;
		rr record;
	begin
		pcreador := dame_creador_de_plantilla(idplantilla);

		for r in select * from lugar where creador = pcreador loop
			select * into rr from lugar_para_actividad where plantilla = idplantilla and lugar = r.id;
			if not found then
				return next r;
			end if;
		end loop;

		return;
		
	end;
$$
  LANGUAGE 'plpgsql';
