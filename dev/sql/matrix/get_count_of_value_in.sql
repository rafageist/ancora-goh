
-- -----------------------------------------------------------------------------------
--      NOMBRE: matrix.get_count_of_value_in
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad valores encontrados en un arreglo
--              de colocaciones posibles
--     EJEMPLO: SELECT matrix.get_count_of_value_in(array[FALSE,TRUE,TRUE,TRUE,FALSE], TRUE) = 3;
--              SELECT matrix.get_count_of_value_in(array[[1,2],[2,4]], 2) = 2;
--              SELECT matrix.get_count_of_value_in(array[[[1,2],[2,3]],[[2,4],[4,5]]], 2) = 3;
--              SELECT matrix.get_count_of_value_in(array[[[[1],[2]],[[2],[3]]],[[[2],[4]],[[4],[5]]]], 4) = 2;
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION matrix.get_count_of_value_in(arr anyarray, valor ANYELEMENT) RETURNS INTEGER AS
$$
    DECLARE c BIGINT;
        i INTEGER;
        j INTEGER;
        k INTEGER;
        dims INTEGER;    
        x INTEGER; 
        y INTEGER; 
        z INTEGER;
        narr text;
        nvalor text;
    BEGIN
        c := 0;
        
        dims := matrix.get_count_of_dims(arr);

        -- m?de 3 es un caso excepcional:
        --   se convierte el arreglo a texto y luego arreglo unidimensional
        --   y se duevuelve se trata como tal, puede que el tamano de text no cubra
        --   todos los elementos del arreglo
        
        IF dims > 3 THEN
        
            x := matrix.get_length_of(string_to_array(array_to_string(arr,'~*~'), '~*~'), 1);
            IF x > 0 THEN
                narr := array_to_string(arr,'~*~') || '';

                nvalor := valor || '';

                i := 1;
                
                LOOP
                    IF nvalor = split_part(narr,'~*~',i) THEN
                        c := c + 1;
                    END IF;
                    
                    EXIT WHEN i >= x;
                    i := i + 1;
                END LOOP;
            END IF;
            
        END IF;
        
        
        -- Es una lista
        IF dims = 1 THEN
            
            x := matrix.get_length_of(arr, 1);
            IF x > 0 THEN
                i := 0;

                LOOP
                    IF arr[i] = valor THEN
                        c := c + 1;
                    END IF;
                    EXIT WHEN i >= x;
                    i := i + 1;
                END LOOP;
            END IF;
        END IF;

        -- Es una tabla
        IF dims = 2 THEN
        
            x := matrix.get_length_of(arr,1);
            
            y := matrix.get_length_of(arr,2);

            IF x > 0 AND y > 0 THEN
                i := 0;
                
                LOOP
                    j := 0;
                    LOOP
                        IF arr[i][j] = valor THEN
                            c := c + 1;
                        END IF;
                        EXIT WHEN j >= y;
                        
                        j := j + 1;
                    END LOOP;
                    EXIT WHEN i >= x;
                    
                    i := i + 1;
                END LOOP;
            END IF;
        END IF;
        
        -- Es un cubo
        IF dims = 3 THEN
        
            x := matrix.get_length_of(arr,1);
            
            y := matrix.get_length_of(arr,2);
            
            z := matrix.get_length_of(arr,3);

            IF x > 0 AND y > 0 AND z > 0 THEN
                i := 0;
                
                LOOP
                    j := 0;
                    LOOP
                        k := 0;
                        LOOP
                            IF arr[i][j][k] = valor THEN                        
                                c := c + 1;
                            END IF;
                            
                            EXIT WHEN k >= z;
                            
                            k := k + 1;
                            
                        END LOOP;
                        EXIT WHEN j >= y;
                        j := j + 1;
                    END LOOP;
                    EXIT WHEN i >= x;
                    i := i + 1;
                END LOOP;
            END IF;
        END IF;

        RETURN c;

    END;
$$
LANGUAGE 'plpgsql';