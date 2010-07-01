create or replace function seguridad.check_host(pusuario varchar, pip varchar) returns boolean as
$$
	declare r record;
	begin
		select * into r from seguridad.host where usuario = pusuario and ip = ip;

		if found then
			return true;
		end if;
		
		return false;
	end;
$$
language 'plpgsql';