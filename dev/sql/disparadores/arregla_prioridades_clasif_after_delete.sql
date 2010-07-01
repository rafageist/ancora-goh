CREATE OR REPLACE FUNCTION arregla_prioridades_clasif_after_delete() returns trigger as
$$
	begin
		execute arregla_prioridades_clasif(old.creador);
		return old;
	end;
$$
language 'plpgsql';

CREATE TRIGGER trigger_arregla_prioridades_clasif_after_delete
  AFTER DELETE
  ON clasificacion_de_actividad
  FOR EACH ROW
  EXECUTE PROCEDURE arregla_prioridades_clasif_after_delete();
