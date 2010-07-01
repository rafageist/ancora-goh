DROP TABLE IF EXISTS restriccion_real_de_profesor CASCADE;
CREATE TABLE restriccion_real_de_profesor(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_profesor(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);