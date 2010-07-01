create or replace function actualiza_orden_act_after_delete() returns trigger as
$$
	declare r integer;
	begin

		r := actualiza_orden_plantillas(OLD.desglose);
		return OLD;
	end;
$$
language 'plpgsql';

create trigger trigger_actualiza_orden_act_after_delete after delete on plantilla_de_actividad
for each row
execute procedure actualiza_orden_act_after_delete();