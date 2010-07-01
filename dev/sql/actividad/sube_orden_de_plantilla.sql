create or replace function sube_orden_de_plantilla(idplantilla varchar) returns boolean as
$$
	declare r record;
		rplantilla1 record;
		rplantilla2 record;
		m integer;
		o1 integer;
		o2 integer;
	begin
		select * into rplantilla1 from plantilla_de_actividad where id = idplantilla;

		-- asegurando que este en orden las plantillas, de paso devuelve la cantidad y max
		
		m := actualiza_orden_plantillas(rplantilla1.desglose);
		
		if found then
			if rplantilla1.orden > 1 then

				-- obtengo la plantilla de arriba
				select * into rplantilla2 from plantilla_de_actividad where desglose = rplantilla1.desglose and orden = rplantilla1.orden - 1;

				-- hago el intercambio

				o1 := rplantilla1.orden;
				o2 := rplantilla2.orden;
				
				update plantilla_de_actividad set orden = m + 1, actualizacion_interna = true where id = rplantilla1.id;
				update plantilla_de_actividad set orden = o1, actualizacion_interna = true where id = rplantilla2.id;
				update plantilla_de_actividad set orden = o2, actualizacion_interna = true where id = rplantilla1.id;

				-- quitando actualizacion interna
				update plantilla_de_actividad set actualizacion_interna = false where id = rplantilla1.id or id = rplantilla2.id;
			end if;
			return true;
		end if;
		return false;
	end;
$$
language 'plpgsql';