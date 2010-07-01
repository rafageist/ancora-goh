create or replace function actualiza_orden_act_before_insert() returns trigger as
$$
	declare r integer;
		rr record;
	begin
		SELECT * into rr from plantilla_de_actividad WHERE desglose = NEW.desglose AND orden = NEW.orden;

		IF FOUND THEN
			NEW.orden := dame_max_orden_act(NEW.desglose) + 1;
		END IF;
		
		return NEW;
	end;
$$
language 'plpgsql';

create trigger trigger_actualiza_orden_act_before_insert before insert on plantilla_de_actividad
for each row
execute procedure actualiza_orden_act_before_insert();