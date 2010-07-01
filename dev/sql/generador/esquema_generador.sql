CREATE SCHEMA generador;
CREATE TABLE generador.cancelar(
	especialidad varchar not null,
	nivel varchar not null,
	periodo varchar not null,
	primary key(especialidad, nivel, periodo)
);

CREATE OR REPLACE FUNCTION generador.fue_cancelado(idesp varchar, idnivel varchar, idperiodo varchar) returns boolean as
$$
	declare
		r record;
	begin
		select * into r from generador.cancelar where especialidad = idesp and nivel = idnivel and periodo = idperiodo;

		if found then
			delete from generador.cancelar where especialidad = idesp and nivel = idnivel and periodo = idperiodo;
			return true;
		end if;

		return false;
	end;
$$
language 'plpgsql';
/*
insert into generador.cancelar VALUES ('especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e',
'nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s',
'periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q')
*/