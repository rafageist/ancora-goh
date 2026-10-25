-- Datos por defecto

-- Roles
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('superusuario','Usuario con todos los privilegios');
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('administrador','Administrador');
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('planificador','Planificador');
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('profesor','Profesor');
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('invitado','Invitado');

-- Usuarios
-- The administrator has no usable password (maintainer decision, 2026-10-25):
-- the stored value is neither a password_hash() nor a legacy MD5 digest, so
-- PasswordUtil rejects every login until 'php bin/setpassword.php -nick admin'
-- sets one. The 2010 seed used a well-known default password here.
INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena) VALUES ('admin-superuser','admin','administrador superusuario','admin@example.com','admin','!unusable: set with bin/setpassword.php -nick admin');

-- Roles de usuarios

INSERT INTO seguridad.rol_de_usuario (usuario, rol) VALUES ('admin-superuser','superusuario');

-- Permisos

INSERT INTO seguridad.permiso (permiso, rol) VALUES ('todos','superusuario');
