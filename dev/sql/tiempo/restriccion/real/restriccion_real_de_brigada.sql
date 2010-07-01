DROP TABLE IF EXISTS restriccion_real_de_brigada CASCADE;
CREATE TABLE restriccion_real_de_brigada(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_brigada(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (actividad) REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);