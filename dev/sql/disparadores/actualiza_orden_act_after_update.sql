create or replace function actualiza_orden_act_after_update() returns trigger as
$$
	declare r integer;
	begin

		IF NEW.actualizacion_interna = FALSE THEN
			IF OLD.orden <> NEW.orden THEN
				r := actualiza_orden_plantillas(NEW.desglose);
			END IF;
		END IF;
		
		return NEW;
	end;
$$
language 'plpgsql';

create trigger trigger_actualiza_orden_act_after_update after update on plantilla_de_actividad
for each row
execute procedure actualiza_orden_act_after_update();
