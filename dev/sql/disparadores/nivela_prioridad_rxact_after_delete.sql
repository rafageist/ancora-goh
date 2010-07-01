CREATE OR REPLACE FUNCTION nivela_prioridad_rxact_after_delete() RETURNS trigger AS
$$
	declare r record;
		p integer;
	begin
		p := OLD.prioridad;
		
		select * into r from otro_grupo_de_recursos_para_actividad where prioridad = p;
		if not found then
			update otro_grupo_de_recursos_para_actividad set prioridad = prioridad - 1 where prioridad > p AND plantilla = OLD.plantilla;
		end if;

		return OLD;
	end;
$$
  LANGUAGE 'plpgsql';

CREATE TRIGGER trigger_nivela_prioridad_rxact_after_delete
  AFTER DELETE
  ON otro_grupo_de_recursos_para_actividad
  FOR EACH ROW
  EXECUTE PROCEDURE nivela_prioridad_rxact_after_delete();
