
/* ASIGNATURAS */
 DROP table IF EXISTS asignatura CASCADE;
 CREATE TABLE asignatura(
     id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'asignatura', 'id', 'asignatura'),
     descripcion VARCHAR NOT NULL,
     especialidad VARCHAR REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
     nivel VARCHAR NOT NULL REFERENCES nivel(id) ON DELETE CASCADE ON UPDATE CASCADE,
     creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
 );