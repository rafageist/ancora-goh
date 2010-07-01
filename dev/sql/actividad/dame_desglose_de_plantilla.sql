
CREATE OR REPLACE FUNCTION dame_desglose_de_plantilla (idplantilla VARCHAR ) RETURNS VARCHAR as
$$
    SELECT desglose FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';
