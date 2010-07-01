-- -----------------------------------------------------------------------------------
--      NOMBRE: asignaturas_cant_act
--        TIPO: vista
-- DESCRIPCION: Listado de las asignaturas con sus cantidades de actividades por periodo
--     EJEMPLO: SELECT  * from asignaturas_cant_act;
-- -----------------------------------------------------------------------------------
DROP VIEW IF EXISTS asignaturas_cant_act CASCADE;
CREATE OR REPLACE VIEW asignaturas_cant_act AS
    SELECT asignatura.id as asignatura, especialidad, nivel, periodo, dame_cant_act(asignatura.id, periodo) as cantidad FROM asignatura INNER JOIN desglose ON desglose.asignatura = asignatura.id;
