create or replace function actualiza_orden_plantillas(iddesglose varchar) returns integer
as 
$$
	declare r record;
		c bigint;
		ids varchar[];
		i bigint;
	begin
		c := dame_cant_act_desglose(iddesglose);
		ids := matrix.create_string_list(c);
		
		i := 0;

		for r in select * from plantilla_de_actividad where desglose = iddesglose order by orden loop
		  i:=i+1;
		  ids[i] := r.id;
		end loop;
		
	        i := 0;
	        loop
		  i := i + 1;
		  update plantilla_de_actividad set orden = i, actualizacion_interna = true where id = ids[i] AND orden <> i;
		  update plantilla_de_actividad set actualizacion_interna = false where id = ids[i];
		  exit when i >= c;
		end loop;
		
		return c;
	end;
$$
language 'plpgsql';