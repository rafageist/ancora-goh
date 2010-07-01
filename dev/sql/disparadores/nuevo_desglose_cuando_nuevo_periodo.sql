CREATE OR REPLACE FUNCTION nuevo_desglose_cuando_nuevo_periodo() returns trigger as
$$
	DECLARE r record;
	    pcreador varchar;
	begin
		pcreador := dame_creador_de_planificacion(dame_planificacion_de_periodo(NEW.id));

		FOR r IN SELECT * FROM asignatura WHERE creador = pcreador LOOP
			INSERT INTO desglose (asignatura, periodo) VALUES (r.id, NEW.id);
		END LOOP;
		
		return NEW;
	end;
$$
language 'plpgsql';

CREATE TRIGGER nuevo_desglose_si_nuevo_periodo
  AFTER INSERT
  ON periodo
  FOR EACH ROW
  EXECUTE PROCEDURE nuevo_desglose_cuando_nuevo_periodo();