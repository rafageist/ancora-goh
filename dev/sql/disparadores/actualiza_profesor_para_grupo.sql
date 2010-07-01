
CREATE OR REPLACE FUNCTION actualiza_profesor_para_grupo() RETURNS trigger AS
$$
	declare r record;
	begin

		select * into r from profesor_para_grupo where profesor_para_actividad = NEW.profesor_para_actividad and grupo = NEW.grupo;
		if not found then
			insert into profesor_para_grupo (profesor_para_actividad, grupo) VALUES (NEW.profesor_para_actividad, NEW.grupo);
		end if;
		return NEW;
	end;
$$
LANGUAGE 'plpgsql';

CREATE TRIGGER trigger_actualiza_profesor_para_grupo
  BEFORE UPDATE
  ON profesor_para_grupo
  FOR EACH ROW
  EXECUTE PROCEDURE actualiza_profesor_para_grupo();