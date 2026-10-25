-- Restored from the 2010 database (pg_dump in dev/backup/), see
-- docs/modernization/sql-reconciliation.md D-03. This file previously held a
-- stale duplicate of disparadores/nivela_prioridad_rxact_after_delete.sql.
CREATE OR REPLACE FUNCTION nivela_prioridad_lxact_after_delete()
  RETURNS trigger AS
$BODY$
	declare r record;
		p integer;
	begin
		p := OLD.prioridad;
		
		select * into r from lugar_para_actividad where prioridad = p AND plantilla = OLD.plantilla;
		if not found then
			update lugar_para_actividad set prioridad = prioridad - 1 where prioridad > p AND plantilla = OLD.plantilla;
		end if;

		return OLD;
	end;

$BODY$
  LANGUAGE 'plpgsql';


CREATE TRIGGER trigger_nivela_prioridad_lxact_after_delete
  AFTER DELETE
  ON lugar_para_actividad
  FOR EACH ROW
  EXECUTE PROCEDURE nivela_prioridad_lxact_after_delete();
