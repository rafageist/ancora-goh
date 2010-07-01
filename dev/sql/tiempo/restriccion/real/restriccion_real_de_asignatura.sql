DROP TABLE IF EXISTS restriccion_real_de_asignatura CASCADE;
CREATE TABLE restriccion_real_de_asignatura(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);
