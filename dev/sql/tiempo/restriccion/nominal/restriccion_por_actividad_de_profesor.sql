/** restricciones por actividad de profesores */

CREATE TABLE restriccion_por_actividad_de_profesor(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_profesor(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (actividad) REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal)
) INHERITS (restriccion_por_actividad);