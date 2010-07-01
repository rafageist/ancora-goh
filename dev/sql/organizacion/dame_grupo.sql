CREATE OR REPLACE FUNCTION dame_grupo(brg VARCHAR, clasif VARCHAR ) RETURNS VARCHAR AS
$$
    SELECT grupo FROM brigada_de_grupo WHERE brigada = $1 AND dame_clasificacion_de_grupo(grupo) = $2;
$$
LANGUAGE 'sql';
