-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_asig_de_esp_anno
--        TIPO: funcion
-- DESCRIPCION: Devuelve cantidad de asignaturas de la esp y anno dados
--     EJEMPLO: SELECT  dame_cant_asig_de_esp_anno('info','1');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_asig_de_esp_anno(idesp VARCHAR, idnivel VARCHAR) RETURNS BIGINT as
$$
    SELECT count(id) FROM asignatura WHERE especialidad = $1 AND nivel = $2;
$$
LANGUAGE 'sql';