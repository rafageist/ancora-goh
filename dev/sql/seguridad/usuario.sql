/*  USUARIOS  */
DROP TABLE IF EXISTS seguridad.usuario CASCADE;
CREATE TABLE seguridad.usuario(
                 id VARCHAR PRIMARY KEY 
                        DEFAULT crear_id_de_replica('ancoragoh', 'seguridad.usuario', 'id', 'seguridad.usuario'),
             nombre VARCHAR NOT NULL,
               info VARCHAR NOT NULL,
             correo VARCHAR NOT NULL,
    momento_ingreso DOUBLE PRECISION NOT NULL default tiempo.moment_now(),
              nick VARCHAR NOT NULL unique,
        contrasena VARCHAR NOT NULL
 );
