CREATE OR REPLACE FUNCTION nivela_prioridad_lxact_after_update()  RETURNS trigger AS
$$
	declare r record;
		p integer;
	begin
		if new.actualizacion_interna = FALSE AND new.prioridad <> old.prioridad then
			p := OLD.prioridad;
			
			select * into r from lugar_para_actividad where prioridad = p;
			if not found then
				update lugar_para_actividad set prioridad = prioridad -1, actualizacion_interna = TRUE where prioridad > p AND plantilla = OLD.plantilla;
				update lugar_para_actividad set actualizacion_interna = FALSE WHERE plantilla = OLD.plantilla AND lugar = NEW.lugar;				
				NEW.prioridad = NEW.prioridad - 1;
			end if;
		end if;
		return NEW;
	end;
$$
  LANGUAGE 'plpgsql';

CREATE TRIGGER trigger_nivela_prioridad_lxact_after_update
  AFTER UPDATE
  ON lugar_para_actividad
  FOR EACH ROW
  EXECUTE PROCEDURE nivela_prioridad_lxact_after_update();
