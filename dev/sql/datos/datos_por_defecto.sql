-- Datos por defecto

-- Roles
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('superusuario','Usuario con todos los privilegios');
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('administrador','Administrador');
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('planificador','Planificador');
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('profesor','Profesor');
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('invitado','Invitado');

-- Usuarios
INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena) VALUES ('admin-superuser','admin','administrador superusuario','admin@example.com','admin',md5('changeit'));

-- Roles de usuarios

INSERT INTO seguridad.rol_de_usuario (usuario, rol) VALUES ('admin-superuser','superusuario');

-- Permisos

INSERT INTO seguridad.permiso (permiso, rol) VALUES ('todos','superusuario');
