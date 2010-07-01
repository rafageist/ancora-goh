-- Restricciones de periodos
DROP TABLE IF EXISTS restriccion_real_de_periodo CASCADE;
CREATE TABLE restriccion_real_de_periodo(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_periodo(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);