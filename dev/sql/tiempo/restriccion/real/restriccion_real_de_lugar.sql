DROP TABLE IF EXISTS restriccion_real_de_lugar CASCADE;
CREATE TABLE restriccion_real_de_lugar(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_lugar(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);