create or replace function mueve_o_crea_brigada_de_grupo(idbrg varchar, idgrupo varchar) returns boolean as
$$
	declare r record;
		rr record;
		rgrupo record;
	begin
		-- verificando que el grupo destino no tenga actividad

		select * into r from actividad where grupo = idgrupo;

		if found then
			return false;
		end if;

		-- verificando que ya este asignado a ese grupo
		
		select * into r from brigada_de_grupo where brigada = idbrg and grupo = idgrupo;

		if found then
			return true;
		end if;

		-- verifico que el grupo no tenga actividad asignada
		
		-- obtengo el grupo
		select * into rgrupo from grupo where id = idgrupo;

		-- si no existe me voy
		if not found then
			return false;
		end if;
			
		-- verifico si esta asignada a otro grupo de la misma (esp,nivel,clasif)

		select * into r from brigada_de_grupo 
			where brigada = idbrg 
			and dame_especialidad_de_grupo(grupo) = rgrupo.especialidad 
			and dame_nivel_de_grupo(grupo) = rgrupo.nivel
			and dame_clasificacion_de_grupo(grupo) = rgrupo.clasificacion
			and dame_planificacion_de_grupo(grupo) = rgrupo.planificacion;

		-- elimino el grupo origen
		if found then
			-- verificando que el grupo origen no tenga actividad
			select * into rr from actividad where grupo = r.grupo;

			if found then
				return false;
			end if;
			
			delete from brigada_de_grupo where brigada = idbrg and grupo = r.grupo;
		end if;

		-- creo el grupo destino
		insert into brigada_de_grupo (brigada,grupo) values (idbrg, idgrupo);
		
		return true;
	end;
$$
language 'plpgsql';