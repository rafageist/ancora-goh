drop table if exists seguridad.traza cascade;
create table seguridad.traza(
	fecha timestamp default now(),
	usuario varchar not null references seguridad.usuario(id) on delete cascade on update cascade,
	accion varchar,
	ip varchar
);
