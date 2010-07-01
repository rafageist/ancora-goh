create or replace function dame_grupos_de_plantilla(idplantilla varchar) returns setof grupo as
$$
	declare r record;
		asig varchar;
	begin
		asig := dame_asignatura_plantilla_de_actividad(idplantilla);
		
		for r in select * from grupo 
			where clasificacion = dame_clasif_plantilla_de_actividad(idplantilla)
			and planificacion = dame_planificacion_de_periodo(dame_periodo_plantilla_de_actividad(idplantilla))
			and especialidad = dame_especialidad_de_asignatura(asig)
			and nivel = dame_nivel_de_asignatura(asig) loop

			return next r;
		end loop;
		
		return;
	end;
$$
language 'plpgsql';