CREATE OR REPLACE FUNCTION seguridad.tiene_permiso(pusuario varchar, ppermiso varchar) RETURNS boolean AS
$$
	DECLARE r RECORD;
	BEGIN
		SELECT * INTO r FROM seguridad.permiso 
            INNER JOIN (seguridad.rol_de_usuario INNER JOIN seguridad.usuario ON seguridad.rol_de_usuario.usuario = seguridad.usuario.id)
            ON seguridad.rol_de_usuario.rol = seguridad.permiso.rol 
            WHERE seguridad.permiso.permiso = ppermiso AND seguridad.usuario.id = pusuario;

		IF FOUND THEN
			return true;
		END IF;
		
		return false;
	END;
$$
language 'plpgsql';