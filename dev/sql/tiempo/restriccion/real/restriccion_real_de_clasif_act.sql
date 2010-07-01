DROP TABLE IF EXISTS restriccion_real_de_clasif_act CASCADE;
CREATE TABLE restriccion_real_de_clasif_act(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_clasif_act(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);