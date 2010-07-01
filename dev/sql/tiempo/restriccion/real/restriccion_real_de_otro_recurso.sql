DROP TABLE IF EXISTS restriccion_real_de_otro_recurso CASCADE;
CREATE TABLE restriccion_real_de_otro_recurso( 
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);