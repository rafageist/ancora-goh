create or replace function esta_otro_recurso_asignado(idrecurso varchar, idplantilla varchar) returns boolean
as
$$
	declare r record;
	begin
		select * into r from otro_recurso_para_actividad 
		where dame_plantilla_de_grupo_de_recursos(grupo_de_recursos) = idplantilla
		and otro_recurso = idrecurso;

		if found then
			return true;
		end if;

		return false;
	end;
$$
language 'plpgsql';
