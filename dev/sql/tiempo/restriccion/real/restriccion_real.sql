DROP TABLE IF EXISTS restriccion_real CASCADE;
CREATE TABLE restriccion_real(
    restriccion_nominal VARCHAR NOT NULL,
    periodicidad VARCHAR NOT NULL,
    fecha BIGINT,
    hora_inicial DOUBLE PRECISION,
    hora_final DOUBLE PRECISION,
    por_actividad BOOLEAN NOT NULL,
    actividad VARCHAR, -- puede ser nula, pues todas las restricciones no son por actividad
    objeto VARCHAR NOT NULL,
    CHECK (hora_final > hora_inicial),
    PRIMARY KEY (restriccion_nominal, periodicidad)
);