
-- las planificaciones no se archivan,
-- pues las planificaciones que est?n fuera de fecha ya no se ejecutan
-- lo que se puede hacer es desactivarlas o activarlas que seria como publicar o despublicar
-- por cuestiones administrativas

/* PLANIFICACIONES */
 DROP table IF EXISTS planificacion CASCADE;
 CREATE TABLE planificacion(
     id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'planificacion', 'id', 'planificacion'),
     descripcion VARCHAR NOT NULL,
     fecha_inicial BIGINT,
     cant_dias BIGINT,
     activa BOOLEAN default TRUE,
     creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
 );