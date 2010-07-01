CREATE TABLE seguridad.permiso(
	permiso varchar not null,
	rol varchar not null references seguridad.rol(nombre) on delete cascade on update cascade,
	primary key (permiso, rol)
);