-- -----------------------------------------------------------------------------------
--      NOMBRE: matrix.get_length_of
--        TIPO: funcion
-- DESCRIPCION: Devuelve la longitud de la dimension de un arrgelo.
--         Si es una lista, tiene una dimension y significa devolver la longitud del arreglo
--        Si es una tabla, tiene dos dimensiones y signiica devolver la altura y ancho de la tabla
--        Si es un cubo, tiene 3 dimensiones y significa devolver la altura, ancho y profundidad
--     EJEMPLO: SELECT matrix.get_length_of(array[1,2,6,7,8],1) = 5;
--              SELECT matrix.get_length_of(array[[1,2],[6,7]],1) = 2;
--              SELECT matrix.get_length_of(array[[[1],[2]],[[6],[7]]],1) = 2;
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION matrix.get_length_of(arr ANYELEMENT, dimension INTEGER) RETURNS BIGINT as
$$
    BEGIN
	IF arr IS NULL THEN
		return 0;
	END IF;
	
        RETURN to_number(split_part(split_part(array_dims(arr),'][',dimension),':',2),'9999999999');
    END;
$$
LANGUAGE 'plpgsql';
