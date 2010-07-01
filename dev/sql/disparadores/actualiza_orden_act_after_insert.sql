create or replace function actualiza_orden_act_after_insert() returns trigger as
$$
	declare r integer;
	
	begin
		r := actualiza_orden_plantillas(NEW.desglose);
		
		return NEW;
	end;
$$
language 'plpgsql';

create trigger trigger_actualiza_orden_act_after_insert after insert on plantilla_de_actividad
for each row
execute procedure actualiza_orden_act_after_insert();