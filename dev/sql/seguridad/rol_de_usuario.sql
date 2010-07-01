CREATE TABLE seguridad.rol_de_usuario(
	usuario varchar not null references seguridad.usuario(id) on delete cascade on update cascade,
	rol varchar not null references seguridad.rol(nombre) on delete cascade on update cascade,
	primary key(usuario, rol)
);
