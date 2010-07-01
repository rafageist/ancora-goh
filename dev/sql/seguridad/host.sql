drop table if exists seguridad.host cascade;
create table seguridad.host(
	ip varchar default '*',
	usuario varchar not null references seguridad.usuario(id) on delete cascade on update cascade,
	primary key (ip, usuario)
);