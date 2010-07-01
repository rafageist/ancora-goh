CREATE OR REPLACE FUNCTION dame_creador_de_plantilla(idplantilla varchar) returns varchar as
$$
	select dame_creador_de_asignatura(dame_asignatura_desglose(dame_desglose_de_plantilla($1)));
$$
language 'sql';