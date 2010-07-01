/**                                                                                                
  * @name: matrix.create_boolean_list                                                              
  * @type: function                                                                                
  * @language: PL/PgSQL                                                                            
  * @description: Construye un arreglo de booleanos                                                
  * @param filas: cantidad de filas del arreglo (o longitud del arreglo)                           
  * @example: SELECT matrix.create_boolean_list(50);                                               
  */

CREATE OR REPLACE FUNCTION matrix.create_boolean_list(
    filas BIGINT
)
RETURNS BOOLEAN[] AS
$$
DECLARE
    fila VARCHAR;
    s VARCHAR;
    matrix BOOLEAN[];
    i BIGINT;
BEGIN
    
    s := '{';
    i := 1;

    IF filas IS NULL or filas = 0 THEN
        RETURN '{}'::BOOLEAN[];
    END IF;
    
    LOOP
        s := s || 'FALSE';

        IF i <> filas THEN
            s := s || ',';
        END IF;
        
        EXIT WHEN i >= filas;
        i := i + 1;
    END LOOP;
    
    s := s || '}';

    matrix := s::BOOLEAN[];
    RETURN matrix;
END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION matrix.create_numeric_list (BIGINT) IS
    'Construye un arreglo de booleanos';
