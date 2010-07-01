CREATE OR REPLACE FUNCTION dame_profesores_no_asignados_a(idplantilla varchar) returns setof varchar as
$$
	declare pcreador varchar;
		r record;
		rr record;
	begin
		pcreador := dame_creador_de_plantilla(idplantilla);

		for r in select * from profesor where creador = pcreador order by nombre loop
			select * into rr from profesor_para_actividad where plantilla = idplantilla and profesor = r.id;
			if not found then
				return next r.id;
			end if;
		end loop;

		return;
		
	end;
$$
language 'plpgsql';