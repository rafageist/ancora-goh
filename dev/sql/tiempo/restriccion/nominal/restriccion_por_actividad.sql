-- ------------------------------
-- RESTRICCIONES POR ACTIVIDAD
-- ------------------------------

DROP TABLE IF EXISTS restriccion_por_actividad CASCADE;
CREATE TABLE restriccion_por_actividad(
	restriccion_nominal varchar not null,
	actividad varchar not null,
	primary key (restriccion_nominal, actividad)
);
