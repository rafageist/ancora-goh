-- -----------------------------------------------------------------------------------
--      NOMBRE: uso_de_otros_recursos
--        TIPO: vista
-- DESCRIPCION: Listado del uso de los recursos
--     EJEMPLO: SELECT  * from uso_de_otros_recursos;
-- -----------------------------------------------------------------------------------
DROP VIEW IF EXISTS uso_de_otros_recursos CASCADE;
CREATE VIEW uso_de_otros_recursos AS
    SELECT otro_recurso, count(actividad) FROM otros_recursos_usados
    GROUP BY otro_recurso;