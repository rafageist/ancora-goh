create or replace function actualiza_orden_act_before_update() returns trigger as
$$
	declare r integer;
		rr record;
	begin
		IF NEW.actualizacion_interna = FALSE THEN
			IF (OLD.orden <> NEW.orden AND OLD.desglose = NEW.desglose) OR OLD.desglose <> NEW.desglose THEN
			
				SELECT * INTO rr FROM plantilla_de_actividad WHERE desglose = NEW.desglose AND orden = NEW.orden;

				IF FOUND THEN
					IF OLD.desglose = NEW.desglose THEN
						NEW.orden := OLD.orden;
					ELSE
						NEW.orden := dame_max_orden_act(NEW.desglose) + 1;
					END IF;
				END IF;
				
			END IF;
		END IF;
		
		return NEW;
	end;
$$
language 'plpgsql';

create trigger trigger_actualiza_orden_act_before_update before update on plantilla_de_actividad
for each row
execute procedure actualiza_orden_act_before_update();
