-- drop function dame_brigadas_sin_grupo(varchar,varchar,varchar, varchar);
create or replace function dame_brigadas_sin_grupo(idesp varchar, idnivel varchar, idclasif varchar, idplanificacion varchar) returns setof varchar as
$$
	declare r brigada%rowtype;
		r1 record;
		-- ids varchar[];
	begin
		for r in select * from brigada where especialidad = $1 and nivel = $2 loop
			select * into r1 from brigada_de_grupo where brigada = r.id and dame_clasificacion_de_grupo(grupo) = idclasif and dame_planificacion_de_grupo(grupo) = idplanificacion;
			if not found then
				return next r.id; -- ids := array_append(ids, r.id);
			end if;
		end loop;
		return; -- ids;
	end;
$$
language 'plpgsql';