/**    
  * @name: matrix.create_numeric_list
  * @type: function
  * @language: PL/PgSQL
  * @description: Construye un arreglo de numeros
  * @param filas: cantidad de filas del arreglo (o longitud del arreglo)
  * @example: SELECT matrix.create_numeric_list(50);
  */

CREATE OR REPLACE FUNCTION matrix.create_numeric_list(
    filas BIGINT
) 
RETURNS NUMERIC[] AS
$$
DECLARE
    fila VARCHAR;
    s VARCHAR;
    matrix NUMERIC[];
    i BIGINT;
BEGIN
    IF filas IS NULL OR filas = 0 THEN
        RETURN '{}'::NUMERIC[];
    END IF;
    
    s := '{';
    i := 1;
    
    LOOP
        s := s || '0';

        IF i <> filas THEN
            s := s || ',';
        END IF;
        
        EXIT WHEN i >= filas;
        i := i + 1;
    END LOOP;
    
    s := s || '}';

    matrix := s::NUMERIC[];
    
    RETURN matrix;
END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION matrix.create_numeric_list (BIGINT) IS
    'Construye un arreglo de numeros';