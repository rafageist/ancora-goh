CREATE OR REPLACE FUNCTION nuevo_desglose_cuando_nueva_asignatura() returns trigger as
$$
	DECLARE r record;
	    pcreador varchar;
	begin
		
		FOR r IN SELECT * FROM periodo WHERE dame_creador_de_planificacion(dame_planificacion_de_periodo(id)) = NEW.creador LOOP
			INSERT INTO desglose (asignatura, periodo) VALUES (NEW.id, r.id);
		END LOOP;
		
		return NEW;
	end;
$$
language 'plpgsql';

CREATE TRIGGER nuevo_desglose_si_nueva_asignatura
  AFTER INSERT
  ON asignatura
  FOR EACH ROW
  EXECUTE PROCEDURE nuevo_desglose_cuando_nueva_asignatura();