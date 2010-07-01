create or replace function actualiza_orden_actividades() returns trigger as
$$
	declare r integer;
	begin

		r := actualiza_orden_plantillas(NEW.desglose);
		return NEW;
	end;
$$
language 'plpgsql';

create trigger trigger_actualiza_orden_actividades after insert or update or delete on plantilla_de_actividad
for each row
execute procedure actualiza_orden_actividades();


ALTER TABLE plantilla_de_actividad ADD COLUMN actualizacion_interna boolean;
ALTER TABLE plantilla_de_actividad ALTER COLUMN actualizacion_interna SET STORAGE PLAIN;
ALTER TABLE plantilla_de_actividad ALTER COLUMN actualizacion_interna SET NOT NULL;
ALTER TABLE plantilla_de_actividad ALTER COLUMN actualizacion_interna SET DEFAULT false;



select dame_max_orden_act('ancoragoh-2010-04-14 16:33:51.871089-04-ljx1pd6mpgw13nwjxr86-desglose');

update plantilla_de_actividad set orden = 5 where id= 'plantilla_de_actividad160410_0623371271456617ccvd2ezcmxnklcpzvyjnotq8kvmn5zp83aate1wrn02zbgpx5yaj'

select actualiza_orden_plantillas('ancoragoh-2010-04-14 16:33:51.871089-04-ljx1pd6mpgw13nwjxr86-desglose');

delete from plantilla_de_actividad where id= 'plantilla_de_actividad160410_0623371271456617ccvd2ezcmxnklcpzvyjnotq8kvmn5zp83aate1wrn02zbgpx5yaj'

insert into plantilla_de_actividad (desglose, orden, clasificacion) values ('ancoragoh-2010-04-14 16:33:51.871089-04-ljx1pd6mpgw13nwjxr86-desglose',1,'clasificacion_de_actividad120410_1139471271086787uty0zv88km1957twy0ju8pclzms2znmj8ajwwgu8sk7mgq06pjqmysxo5ffttr4rscdfjyc290dfpda');

update plantilla_de_actividad set actualizacion_interna = false;

select * from plantilla_de_actividad order by desglose,orden
select sube_orden_de_plantilla('ancoragoh-2010-04-17 13:01:38.876-04-0r4grp2v63nhs0ue4u5x-plantilla_de_actividad');
select baja_orden_de_plantilla('ancoragoh-2010-04-17 13:01:38.876-04-0r4grp2v63nhs0ue4u5x-plantilla_de_actividad');


update plantilla_de_actividad set orden = 16 where id= 'ancoragoh-2010-04-17 13:02:08.178-04-bxnezx3gkr17wcq2eqw8-plantilla_de_actividad'