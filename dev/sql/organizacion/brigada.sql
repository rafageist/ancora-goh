/*  BRIGADAS   */
 DROP table IF EXISTS brigada CASCADE;
 CREATE TABLE brigada(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'brigada', 'id', 'brigada'),
    descripcion VARCHAR NOT NULL,
    especialidad VARCHAR NOT NULL REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    nivel VARCHAR NOT NULL REFERENCES nivel(id) ON DELETE CASCADE ON UPDATE CASCADE,
    matricula INTEGER default 0,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
 );
