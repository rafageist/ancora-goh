/**                                                                                                
  * @name: matrix.create_string_list                                                               
  * @type: function                                                                                
  * @language: PL/PgSQL                                                                            
  * @description: Construye un arreglo de cadenas                                                  
  * @param filas: cantidad de filas del arreglo (o longitud del arreglo)                           
  * @example: SELECT matrix.create_string_list(50);                                                
  */

CREATE OR REPLACE FUNCTION matrix.create_string_list(
    filas BIGINT
)
RETURNS VARCHAR[] AS
$$
DECLARE
    fila VARCHAR;
    s VARCHAR;
    matrix VARCHAR[];
    i BIGINT;
BEGIN
    
    s := '{';
    i := 1;

    IF filas IS NULL or filas = 0THEN
        RETURN '{}'::VARCHAR[];
    END IF;
    
    LOOP
        s := s || quote_literal('');

        IF i <> filas THEN
            s := s || ',';
        END IF;
        
        EXIT WHEN i >= filas;
        i := i + 1;
    END LOOP;
    
    s := s || '}';

    matrix := s::VARCHAR[];
    RETURN matrix;
END;
$$
LANGUAGE 'plpgsql';