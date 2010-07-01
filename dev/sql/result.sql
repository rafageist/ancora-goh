/* ------------------------------ language.plpgsql ------------------------- */ 
--  Lenguage procedural que se va a utilizar

DROP LANGUAGE IF EXISTS 'plpgsql' CASCADE;           
CREATE LANGUAGE 'plpgsql';
/* --------------------------------------------------------- */ 
/* ------------------------------ miscelanea.iif ------------------------- */ 
/**                                                                                                
    @name: iif                                                                                     
    @type: function                                                                                
    @language: SQL                                                                                 
    @description: Devuelve el primer elemento si la condicion es true y el segundo si es falsa     
    @note: Es similar a la instruccion IIF de Visual Basic y aloperador de ? de C++                
    @param $1: expresion booleana a evaluar                                                        
    @param $2: dato (de cualquier tipo) a retornar si la expresion $1 es verdadera                 
    @param $3: dato (de cualquier tipo) a retornar si la expresion $2 es falsa                     
    @example: SELECT iif(a = b, "a y b son iguales", "a y b son diferentes");                      
                                                                                                  */

CREATE OR REPLACE FUNCTION iif(
    expresion BOOLEAN, 
    dato1 ANYELEMENT, 
    dato2 ANYELEMENT
) 
RETURNS ANYELEMENT AS
$$ 
    SELECT CASE $1 WHEN TRUE THEN $2 ELSE $3 END;
$$
LANGUAGE 'sql';

COMMENT ON FUNCTION iif(BOOLEAN, ANYELEMENT, ANYELEMENT) IS 
    'Devuelve el primer elemento si la condicion es true y el segundo si es falsa';
/* --------------------------------------------------------- */ 
/* ------------------------------ miscelanea.crear_cadena_aleatoria ------------------------- */ 
/**                                                                                                
    @name: crear_cadena_aleatoria                                                                  
    @type: function                                                                                
    @language: PL/PgSQL                                                                            
    @description: Devuelve una cadena de caracteres alfanum?ricos aleatorios                       
    @param caracteres: caracteres posibles para construir la cadena                                
    @param cantidad: longitud de la cadenada                                                       
    @var i: contador                                                                               
    @var cadena: contiene la cadena aleatoria                                                      
    @var posicion: guarda la posicion aleatoria en la cadena de caracteres posibles                
    @example: SELECT crear_cadena_aleatoria();                                                     
                                                                                                  */
 
CREATE OR REPLACE FUNCTION crear_cadena_aleatoria(
    caracteres VARCHAR DEFAULT 'abcdefghijklmnopqrstuvwxyz1234567890', 
    longitud INTEGER DEFAULT 20
) 
RETURNS VARCHAR AS
$$
DECLARE 
    i INTEGER; 
    cadena VARCHAR; 
    posicion INTEGER;
BEGIN
    cadena := '';
    i := 1;
    LOOP
        posicion := round(1 + random() * char_length(caracteres));
        cadena := cadena || substring(caracteres from posicion FOR 1);
        EXIT WHEN i = longitud;
        i := i + 1;
    END LOOP;
    RETURN r;
END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION crear_cadena_aleatoria (VARCHAR, INTEGER) IS 
    'Devuelve una cadena de caracteres alfanum?ricos aleatorios';
/* --------------------------------------------------------- */ 
/* ------------------------------ miscelanea.crear_id_de_replica ------------------------- */ 
/**                                                                                                
    @name: crear_id_de_replica                                                                     
    @type: function                                                                                
    @language: PL/PgSQL                                                                            
    @description: Crea una cadena de caracteres ?nica para una columna de una tabla                
    @note: Para construir la replica se tiene en cuenta la fecha y la hora actual.                 
    @note: Se le anexa un prefijo, un sufijo y una cadena de caracteres aleatorios                 
    @param prefijo: prefijo del id de replica                                                      
    @param tabla: tabla para la cual se crea el id                                                 
    @param campoid: nombre del campo o columna                                                     
    @param sufijo: sufijo del id de replica                                                        
    @var id: contiene el id de replica construido                                                  
    @var cant: contiene la cantidad de registros que poseen el id de replica                       
    @example: SELECT crear_cadena_aleatoria();                                                     
                                                                                                  */
 
CREATE OR REPLACE FUNCTION crear_id_de_replica(
    prefijo VARCHAR, 
    tabla VARCHAR, 
    campoid VARCHAR, 
    sufijo VARCHAR
) 
RETURNS VARCHAR AS
$$
DECLARE 
    id VARCHAR;
    cant INTEGER;
BEGIN

    -- mientras exista el id de replica generado genero nuevos id de replicas
    LOOP
        -- se genera el id de replica
        id := prefijo || '-' || CURRENT_TIMESTAMP || '-' || crear_cadena_aleatoria('abcdefghijklmnopqrstuvwxyz1234567890', 20) || '-' || sufijo;

        -- se consulta la tabla correspondiente para asegurarse que no exista
        EXECUTE 'SELECT * FROM ' || tabla || ' WHERE ' || campoid ||' = ' || quote_literal(id);        

        -- se obtiene la cantidad de filas encontradas seg?n la instrucci?n SELECT anterior
        GET DIAGNOSTICS cant = ROW_COUNT;

        -- si no se encontraron filas entonces el id no existe y se puede salir del ciclo
        EXIT WHEN cant = 0;            
    END LOOP;
  
    RETURN id;
END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION crear_id_de_replica (VARCHAR, VARCHAR, VARCHAR, VARCHAR) IS 
    'Crea una cadena de caracteres ?nica para una columna de una tabla';/* --------------------------------------------------------- */ 
/* ------------------------------ miscelanea.bool_to_str ------------------------- */ 
/**                                                                                                
    @name: bool_to_int                                                                             
    @type: function                                                                                
    @language: PL/PgSQL                                                                            
    @description: Convierte un valor BOOLEAN a un VARCHAR donde 'TRUE' = TRUE y 'FALSE' = FALSE    
    @param valor: valor booleano a analizar                                                        
    @example: SELECT bool_to_str(true);                                                            
                                                                                                  */

CREATE OR REPLACE FUNCTION bool_to_str(
    valor BOOLEAN
) 
RETURNS VARCHAR AS
$$
BEGIN 
    IF valor = TRUE THEN
        RETURN 'TRUE';
    END IF;
    RETURN 'FALSE';
END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION bool_to_str(BOOLEAN) IS
    'Convierte un valor BOOLEAN a un VARCHAR donde "TRUE" = TRUE y "FALSE" = FALSE';
/* --------------------------------------------------------- */ 
/* ------------------------------ miscelanea.bool_to_int ------------------------- */ 
/**                                                                                                
    @name: bool_to_int                                                                             
    @type: function                                                                                
    @language: PL/PgSQL                                                                            
    @description: Convierte un valor BOOLEAN a un INTEGER donde 1 = TRUE y 0 = FALSE               
    @param valor: valor booleano a analizar                                                        
    @example: SELECT bool_to_int(true);                                                            
                                                                                                  */
 
CREATE OR REPLACE FUNCTION bool_to_int(
    valor BOOLEAN
) 
RETURNS INTEGER AS
$$
BEGIN 
    IF valor = TRUE THEN
        RETURN 1;
    END IF;
    RETURN 0;
END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION bool_to_int(BOOLEAN) IS 
    'Convierte un valor BOOLEAN a un entero donde 1 = TRUE y 0 = FALSE';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.matrix ------------------------- */ 
-- Trabajo con matrices

DROP SCHEMA IF EXISTS matrix CASCADE;
CREATE SCHEMA matrix;/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.create_numeric_list ------------------------- */ 
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
    'Construye un arreglo de numeros';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.create_numeric_matrix ------------------------- */ 
CREATE OR REPLACE FUNCTION matrix.create_numeric_matrix(filas BIGINT, columnas BIGINT) RETURNS NUMERIC[][] as
$$
    DECLARE
        fila VARCHAR;
        s VARCHAR;
        matrix NUMERIC[][];
        i BIGINT;
        j BIGINT;
    BEGIN
        IF filas IS NULL OR columnas is NULL or filas = 0 or columnas = 0 THEN
            RETURN '{}'::NUMERIC[][];
        END IF;
        s := '{';
        i := 1;
        LOOP
            j := 1;
            fila := '{';
            LOOP
                fila := fila || '0';

                IF j <> columnas THEN
                    fila := fila || ',';
                END IF;
                
                EXIT WHEN j >= columnas;
                j := j + 1;
            END LOOP;
            
            fila := fila || '}';

            s := s || fila;
            IF i <> filas THEN
                s := s || ',';
            END IF;

            
            EXIT WHEN i >= filas;
            i := i + 1;
        END LOOP;
        
        s := s || '}';

        matrix := s::NUMERIC[][];
        RETURN matrix;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.create_string_matrix ------------------------- */ 
CREATE OR REPLACE FUNCTION matrix.create_string_matrix(filas BIGINT, columnas BIGINT) RETURNS VARCHAR[][] as
$$
    DECLARE
        fila VARCHAR;
        s VARCHAR;
        matrix VARCHAR[][];
        i BIGINT;
        j BIGINT;
    BEGIN
        IF filas IS NULL OR columnas is NULL or filas = 0 or columnas = 0 THEN
            RETURN '{}'::VARCHAR[][];
        END IF;
        
        s := '{';
        i := 1;
        LOOP
            j := 1;
            fila := '{';
            LOOP
                fila := fila || quote_literal('');

                IF j <> columnas THEN
                    fila := fila || ',';
                END IF;
                
                EXIT WHEN j >= columnas;
                j := j + 1;
            END LOOP;
            
            fila := fila || '}';

            s := s || fila;

            IF i <> filas THEN
                s := s || ',';
            END IF;

            
            EXIT WHEN i >= filas;

            i := i + 1;
        END LOOP;
        
        s := s || '}';

        matrix := s::VARCHAR[][];
        RETURN matrix;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.create_string_list ------------------------- */ 
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
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.create_boolean_list ------------------------- */ 
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
/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.create_boolean_matrix ------------------------- */ 
CREATE OR REPLACE FUNCTION matrix.create_boolean_matrix(filas BIGINT, columnas BIGINT) RETURNS BOOLEAN[][] as
$$
    DECLARE
        fila VARCHAR;
        s VARCHAR;
        matrix BOOLEAN[][];
        i BIGINT;
        j BIGINT;
    BEGIN
        IF filas IS NULL OR columnas is NULL OR filas = 0 OR columnas = 0 THEN
            RETURN '{}'::BOOLEAN[][];
        END IF;
        
        s := '{';
        i := 1;
        LOOP
            j := 1;
            fila := '{';
            LOOP
                fila := fila || 'FALSE';

                IF j <> columnas THEN
                    fila := fila || ',';
                END IF;
                
                EXIT WHEN j >= columnas;
                j := j + 1;
            END LOOP;
            
            fila := fila || '}';

            s := s || fila;
            IF i <> filas THEN
                s := s || ',';
            END IF;

            
            EXIT WHEN i >= filas;
            i := i + 1;
        END LOOP;
        
        s := s || '}';

        matrix := s::BOOLEAN[][];
        RETURN matrix;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.create_boolean_cube ------------------------- */ 
-- EXAMPLE SELECT matrix.create_boolean_cube (3,3,3);
CREATE OR REPLACE FUNCTION matrix.create_boolean_cube(x BIGINT, y BIGINT, z BIGINT) RETURNS BOOLEAN[][][] as
$$
    DECLARE
        xs VARCHAR;
        ys VARCHAR;
        zs VARCHAR;
        matrix BOOLEAN[][][];
        i BIGINT;
        j BIGINT;
        k BIGINT;
    BEGIN
        IF x IS NULL OR y is NULL OR z IS NULL OR x = 0 OR y = 0 OR z = 0 THEN
            RETURN '{}'::BOOLEAN[][][];
        END IF;
        
        xs := '{';
        i := 1;
        LOOP
            j := 1;
            ys := '{';
            
            LOOP
                k := 1;
                
                zs := '{';

                LOOP
                    zs := zs || 'FALSE';

                    IF k <> z THEN
                        zs := zs || ',';
                    END IF;
                    
                    EXIT WHEN k >= z;
                    k := k + 1;
                END LOOP;
                
                zs := zs || '}';

                ys := ys || zs;

                IF j <> y THEN
                    ys := ys || ',';
                END IF;
                
                EXIT WHEN j >= y;
                j := j + 1;
            END LOOP;
            
            ys := ys || '}';

            xs := xs || ys;
            
            IF i <> x THEN
                xs := xs || ',';
            END IF;

            
            EXIT WHEN i >= x;
            i := i + 1;
        END LOOP;
        
        xs := xs || '}';

        matrix := xs::BOOLEAN[][][];
        RETURN matrix;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.get_length_of ------------------------- */ 
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
        RETURN to_number(split_part(split_part(array_dims(arr),'][',dimension),':',2),'9999999999');
    END;
$$
LANGUAGE 'plpgsql';
/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.set_matrix_in_cube ------------------------- */ 
-- --------------------------------
--          NOMBRE: 
--            TIPO:
--     DESCRIPCION:
--         EJEMPLO:
-- ---------------------------------
CREATE OR REPLACE FUNCTION matrix.set_matrix_in_cube(out pcube anyarray, pmatrix anyarray, pos BIGINT) RETURNS anyarray AS
$$
    DECLARE x BIGINT; y BIGINT; z BIGINT; r BIGINT; c BIGINT; i BIGINT; j BIGINT; 
    BEGIN
        x := matrix.get_length_of(pcube,1);
        y := matrix.get_length_of(pcube,2);
        z := matrix.get_length_of(pcube,3);

        r := matrix.get_length_of(pmatrix,1);
        c := matrix.get_length_of(pmatrix,2);

        IF x > 0 AND y > 0 AND z > 0 AND r > 0 AND c > 0 THEN
                    
            i := 0;
            LOOP
                j := 0;
                LOOP
                    
                    pcube[pos][i][j] := pmatrix[i][j];
                    
                    EXIT WHEN j >= c;
                    j := j + 1;
                END LOOP;
                EXIT WHEN i >= r;
                i := i + 1;
            END LOOP;
        END IF;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.get_pos_of ------------------------- */ 
-- SELECT matrix.get_pos_of(array['a','b','c'],'c');
CREATE OR REPLACE FUNCTION matrix.get_pos_of(arr anyarray, v ANYELEMENT) RETURNS BIGINT AS
$$
    DECLARE i BIGINT; j BIGINT; 
        x BIGINT;
    BEGIN
        x := matrix.get_length_of(arr,1);
        IF x > 0 THEN
            i := 1;
            LOOP
                IF arr[i] = v THEN
                    RETURN i;
                END IF;
                
                EXIT WHEN i >= x;
                i := i + 1;
            END LOOP;
        END IF;
        RETURN -1;
        
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.extract_boolean_column ------------------------- */ 
-- --------------------------------
--          NOMBRE: 
--            TIPO:
--     DESCRIPCION:
--         EJEMPLO:
-- ---------------------------------
CREATE OR REPLACE FUNCTION matrix.extract_boolean_column(arr ANYELEMENT, c BIGINT) RETURNS BOOLEAN[] AS
$$
    DECLARE narr BOOLEAN[];
        i BIGINT;
        crows BIGINT;
    BEGIN
        i := 1;
        crows := matrix.get_length_of(arr,2);
        IF crows > 0 THEN
            LOOP
                narr := array_append(narr, arr[i][c]);
                EXIT WHEN i >= crows;
                i := i + 1;
            END LOOP;
        END IF;
        RETURN narr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.extract_numeric_column ------------------------- */ 
CREATE OR REPLACE FUNCTION matrix.extract_numeric_column(arr ANYELEMENT, c BIGINT) RETURNS NUMERIC[] AS
$$
    DECLARE narr NUMERIC[];
        i BIGINT;
        crows BIGINT;
    BEGIN
        i := 1;
        crows := matrix.get_length_of(arr,2);
        IF crows > 0 THEN
            LOOP
                narr := array_append(narr, arr[i][c]);
                EXIT WHEN i >= crows;
                i := i + 1;
            END LOOP;
        END IF;
        RETURN narr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.extract_string_column ------------------------- */ 
-- --------------------------------
--          NOMBRE: 
--            TIPO:
--     DESCRIPCION:
--         EJEMPLO:
-- ---------------------------------
CREATE OR REPLACE FUNCTION matrix.extract_string_column(arr ANYELEMENT, c BIGINT) RETURNS text[] AS
$$
    DECLARE narr text[];
        i BIGINT;
        crows BIGINT;
    BEGIN
        i := 1;
        crows := matrix.get_length_of(arr,2);
        IF crows > 0 THEN
            LOOP
                narr := array_append(narr, arr[i][c]);
                EXIT WHEN i >= crows;
                i := i + 1;
            END LOOP;
        END IF;
        RETURN narr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.get_count_of_dims ------------------------- */ 
-- -----------------------------------------------------------------------------------
--      NOMBRE: matrix.get_count_of_dims
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de dimensiones de una arreglo (1 - lista, 2 - matrix o 3 - cubo...)
--     EJEMPLO: SELECT matrix.get_count_of_dims(array[1,2,3]) = 1;
--        SELECT matrix.get_count_of_dims(array[[1,2],[1,2]]) = 2;
--        SELECT matrix.get_count_of_dims(array[[[1],[2]],[[1],[2]]]) = 3;
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION matrix.get_count_of_dims(arr ANYELEMENT) RETURNS INTEGER AS
$$
    DECLARE c INTEGER;
    BEGIN
        c := 0;
        LOOP
            IF split_part(array_dims(arr),'][',c + 1) <> '' THEN
                c:= c + 1;
            ELSE 
                EXIT;
            END IF;
            EXIT WHEN c = 36000;
        END LOOP;
        
        RETURN c;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.get_count_of_value_in ------------------------- */ 

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
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.echo ------------------------- */ 
-- --------------------------------
--          NOMBRE: 
--            TIPO:
--     DESCRIPCION:
--         EJEMPLO:
-- ---------------------------------
CREATE OR REPLACE FUNCTION matrix.echo(arr anyarray) RETURNS void AS
$$
    BEGIN
        RAISE NOTICE '[INFO] array = %', array_to_string(arr,',');
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.get_min_pos ------------------------- */ 
-- BUSCAR MINIMO EN UN ARREGLO
CREATE OR REPLACE FUNCTION matrix.get_min_pos(arr anyarray) RETURNS bigint as 
$$
    DECLARE 
        pos bigint;
        i bigint;
        cant bigint;
    BEGIN
        cant := matrix.get_length_of(arr, 1);
        
        IF cant < 1 THEN
            RETURN -1;
        END IF;
        
        pos := 1;
        i := 1;
        LOOP
            EXIT WHEN i > cant;
            IF arr[pos] > arr[i] THEN 
                pos := i;
            END IF;

            EXIT WHEN i = cant;
            i := i + 1;
        END LOOP;

        RETURN pos;
    END;
$$
LANGUAGE  'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.get_max_pos ------------------------- */ 
-- BUSCAR MAXIMO EN UN ARREGLO
CREATE OR REPLACE FUNCTION matrix.get_max_pos(arr anyarray) RETURNS bigint as 
$$
    DECLARE 
        pos bigint;
        i bigint;
        cant bigint;
    BEGIN

        cant := matrix.get_length_of(arr, 1);
        
        IF cant < 1 THEN
            RETURN -1;
        END IF;
        
        pos := 1;
        i := 1;
        LOOP
            EXIT WHEN i > cant;
            
            IF arr[pos] < arr[i]  THEN 
                pos := i;
            END IF;

            EXIT WHEN i = cant;
            i := i + 1;
            
        END LOOP;

        RETURN pos;
    END;
$$
LANGUAGE  'plpgsql';
/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.get_min ------------------------- */ 
CREATE OR REPLACE FUNCTION matrix.get_min(arr anyarray) RETURNS anyelement as 
$$
    BEGIN
        RETURN arr[matrix.get_min_pos(arr)];
    END;
$$
LANGUAGE  'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ matrix.get_max ------------------------- */ 
CREATE OR REPLACE FUNCTION matrix.get_max(arr anyarray) RETURNS anyelement as 
$$
    BEGIN
        RETURN arr[matrix.get_max_pos(arr)];
    END;
$$
LANGUAGE  'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ generador.get_max_colocation ------------------------- */ 
-- -----------------------------------------------------------------------------------
--      NOMBRE: get_max_colocation
--        TIPO: funcion
-- DESCRIPCION: Dado un arreglo de posibles inicios de una actividad devuelve la maxima cantidad 
--              de colocaciones posibles
--     EJEMPLO: SELECT matrix.get_max_colocation(array[FALSE,TRUE,TRUE,TRUE,FALSE], 2);

/**   @note:                                                                                       
        Un arreglo con TRUE al final no debe ocurrir para actividades de m?de 1 turno de           
        duracion. Por ejemplo array[FALSE,TRUE,TRUE,TRUE]  con una actividad de 2 turnos no        
        debe ser un arreglo de posibles inicios, pues en el ??mo turno no se pueden dar dos.       
        De todas maneras la funcion verifica eso para un posible arreglo FALSE de posibles         
        inicios, de modo tal que                                                                   
                                                                                                   
            matrix.get_max_colocation(array[FALSE,TRUE,TRUE,TRUE],2) = 1;                          
                                                                                                   
        y por su puesto                                                                            
            matrix.get_max_colocation(array[FALSE,TRUE,TRUE,TRUE],3) = 1;                          
            matrix.get_max_colocation(array[FALSE,TRUE,TRUE,TRUE],1) = 3;                          
                                                                                                  */

-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION get_max_colocation(arr BOOLEAN[], duracion INTEGER) RETURNS INTEGER AS
$$
    DECLARE crows BIGINT;
        i INTEGER;
        inipi INTEGER;
        ini INTEGER;
        c INTEGER;
    BEGIN
        crows := matrix.get_length_of(arr);

        IF crows = 0 THEN
            RETURN 0;
        END IF;
        
        i := 1;
        LOOP 
            IF arr[i] = TRUE THEN
                inipi = i;
                c := 1;
            END IF;
            EXIT WHEN i >= crows OR c = 1;
            i := i + 1;
        END LOOP;
            
        LOOP
            EXIT WHEN ini + duracion > crows;

            ini := inipi + duracion;
            
            IF arr[ini] = FALSE THEN
                inipi = inipi+ 1;
            ELSE
                IF ini < crows OR duracion = 1 THEN
                    c := c + 1;
                END IF;
                inipi = ini;
            END IF;
                
        END LOOP;
        
        RETURN c;

    END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION get_max_colocation(arr BOOLEAN[], duracion INTEGER) IS 'Dado un arreglo de posibles inicios de una actividad devuelve la maxima cantidad de colocaciones posibles';
/* --------------------------------------------------------- */ 
/* ------------------------------ prioriza.prioriza ------------------------- */ 
-- Prioriza

DROP schema IF EXISTS prioriza CASCADE;
CREATE schema prioriza;
/* --------------------------------------------------------- */ 
/* ------------------------------ prioriza.nivelar ------------------------- */ 

-- NIVELAR UN ARREGLO
CREATE OR REPLACE FUNCTION prioriza.nivelar(arr NUMERIC[], criterio BOOLEAN /* TRUE minimizar, FALSE maximizar*/) RETURNS INTEGER[] as
$$
    DECLARE niveles int[];
        nivel int;
        ya BOOLEAN[];
        i INTEGER;
        countya INTEGER;
        optimo INTEGER;
        primero BOOLEAN;
        cant bigint;
    BEGIN
        nivel := 1;
        cant := matrix.get_length_of(tabla,1);
        niveles := matrix.create_numeric_list(cant);
        
        i := 0;
        countya := 0;

        LOOP
            ya[i] = FALSE;
            EXIT WHEN i = cant;
            i := i + 1;
        END LOOP;

        LOOP
            
            IF criterio = TRUE THEN
                -- buscando minimo
                optimo := 0;
                primero := TRUE;
                i := 1;
                LOOP
                    IF ya[i] = FALSE THEN
                        IF optimo > arr[i] or primero = TRUE THEN 
                            optimo := arr[i];
                            primero := FALSE;
                        END IF;
                    END IF;

                    EXIT WHEN i = cant;
                    i := i + 1;
                    
                END LOOP;
            ELSE
                -- buscando maximo
                optimo := 0;
                primero := TRUE;
                i := 1;
                LOOP
                    IF ya[i] = FALSE THEN
                        IF optimo < arr[i] or primero = TRUE THEN 
                            optimo := arr[i];
                            primero := FALSE;
                        END IF;
                    END IF;
                    
                    EXIT WHEN i = cant;
                    i := i + 1;
                    
                END LOOP;
            END IF;

            i := 1;
            LOOP
                IF ya[i] = FALSE THEN
                    IF optimo = arr[i] THEN 
                        niveles[i] := nivel;
                        ya[i] := TRUE;
                        countya := countya + 1;                    
                    END IF;
                END IF;
                
                EXIT WHEN i = cant;
                i := i + 1;
            END LOOP;
            nivel := nivel + 1;
            EXIT WHEN countya = cant;
        END LOOP;
        RETURN niveles;
    END;
$$
LANGUAGE  'plpgsql';

-- SELECT prioriza.nivelar(array[2,4,5,5,6,7,7,3,4,5,6,10,11,11],14,FALSE);
/* --------------------------------------------------------- */ 
/* ------------------------------ prioriza.nivelar_matrix ------------------------- */ 
-- NIVELAR UN ARREGLO
CREATE OR REPLACE FUNCTION prioriza.nivelar_matrix(tabla numeric[][], criterios BOOLEAN[]) RETURNS INTEGER[][] as
$$
    DECLARE
        valores NUMERIC[];
        nivelados INTEGER[];
        niveles INTEGER[][];
        filas bigint;
        columnas bigint;
        i INTEGER;
        j INTEGER;
    BEGIN

        niveles := matrix;
        filas := matrix.get_length_of(tabla,1);
        columnas := matrix.get_length_of(tabla,2);
        
        i := 1;
        LOOP
            raise notice 'analizando columna %',i;
            -- tomo la columna actual
            j :=1;
            LOOP
                valores[j] = matrix[j][i];
                EXIT WHEN j = filas;
                j := j + 1;
            END LOOP;
            
            -- nivelo la columna

            nivelados := prioriza.nivelar(valores, criterios[i]);
            
            -- la introduzco en la matriz de respuesta

            j := 1;
            LOOP
                niveles[j][i] := nivelados[j];            
                EXIT WHEN j = filas;
                j := j + 1;
            END LOOP;            
            
            EXIT WHEN i = columnas;
            i := i + 1;
        END LOOP;

        RETURN niveles;
    END;
$$
LANGUAGE  'plpgsql';

-- SELECT prioriza.nivelar_matrix(array[array[10,10],array[20,20]],2,2,array[TRUE,TRUE]);
-- SELECT prioriza.nivelar(array[5,3],2,FALSE);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.tiempo ------------------------- */ 
-- ------------------------------------------
-- FUNCIONES PARA EL TRABAJO CON EL TIEMPO
-- ------------------------------------------

DROP schema IF EXISTS tiempo CASCADE;
CREATE schema tiempo;

-- SELECT tiempo.date_to_number(31,12,2000);
-- SELECT tiempo.day_number(31,12,2000);
-- SELECT 31+29+31+30+31+30+31+31+30+31+30+31;
-- SELECT tiempo.is_leap_year(2000);
-- SELECT tiempo.is_leap_year(2000) = FALSE
-- SELECT tiempo.moment_number(4000,1382.18);
-- SELECT tiempo.hour_to_number(23,59,59);
-- SELECT tiempo.number_to_date(4000);
-- SELECT now();
-- SELECT tiempo.date_to_number(30,9,2009);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.ancora_date ------------------------- */ 

-- Date
CREATE type tiempo.ancora_date as (
    d INTEGER,
    m INTEGER,
    a INTEGER
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.ancora_hour ------------------------- */ 
-- Hour
CREATE type tiempo.ancora_hour as(
    h INTEGER,
    m INTEGER,
    s INTEGER
);
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.countdays_of_month ------------------------- */ 
-- Count days of month
CREATE OR REPLACE FUNCTION tiempo.countdays_of_month(m INTEGER) RETURNS INTEGER as
$$
    DECLARE d INTEGER;
    BEGIN
        d:=31;
        
        IF (m=4 or m=6 or m=9 or m=11) THEN
            d:=30;
        END IF;
        
        IF (m=2) THEN
            d:=28;
        END IF;
        
        RETURN d;
    END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION tiempo.countdays_of_month(INTEGER) is 'RETURNS count of days of specificic month';
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.hour_to_number ------------------------- */ 
-- Convert hour to number
DROP function IF EXISTS tiempo.hour_to_number(INTEGER,INTEGER,INTEGER) CASCADE; 
CREATE OR REPLACE FUNCTION tiempo.hour_to_number(h INTEGER, m INTEGER, s INTEGER) RETURNS DOUBLE PRECISION as
$$
    BEGIN
        RETURN h * 36 + m * 0.6 + s * 0.01;
    END;
$$
LANGUAGE 'plpgsql';

-- SELECT * from tiempo.hour_to_number(23,59,59);

COMMENT ON FUNCTION tiempo.hour_to_number(INTEGER,INTEGER,INTEGER) is 'Convert hour to number';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.current_hour_number ------------------------- */ 
CREATE or replace function tiempo.current_hour_number() RETURNS DOUBLE PRECISION as
$$
    DECLARE ahora timestamp;
        hora INTEGER;
        minuto INTEGER;
        segundo INTEGER;
        h DOUBLE PRECISION;
        
    BEGIN
        ahora := now();
        hora := EXTRACT(HOUR FROM ahora);
        minuto := EXTRACT(MINUTE FROM ahora);
        segundo := trunc(EXTRACT(SECOND FROM ahora));

        h := tiempo.hour_to_number(hora,minuto,segundo);
        
        RETURN h;

    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.date_number_from_moment_number ------------------------- */ 
CREATE or replace function tiempo.date_number_from_moment_number(m DOUBLE PRECISION) RETURNS BIGINT as
$$
    BEGIN
        RETURN trunc(m / 864) + 1;
    END;
$$
LANGUAGE 'plpgsql';

-- SELECT now();
-- SELECT tiempo.number_to_date(tiempo.date_number_from_moment_number(tiempo.moment_now()));
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.is_leap_year ------------------------- */ 
CREATE OR REPLACE FUNCTION tiempo.is_leap_year(pyear INTEGER) RETURNS BOOLEAN AS
$$
    BEGIN
        IF mod(pyear, 4) = 0 and mod(pyear, 100) <> 0 or mod(pyear, 400) = 0 THEN 
            RETURN TRUE;
        END IF;
        RETURN FALSE;
    END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION tiempo.is_leap_year(INTEGER) is 'RETURN TRUE IF is a leap year';
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.day_number ------------------------- */ 
-- Consecutive number of day
CREATE OR REPLACE FUNCTION tiempo.day_number(d INTEGER, m INTEGER, a INTEGER) RETURNS BIGINT as
$$
    DECLARE cd INTEGER;
	    day_began integer[12];
    
    BEGIN
        -- cantidad de dias antes del mes a partir del 1ero de enero
        cd := 0;

        day_began[1] := 1;
        day_began[2] := 31 + 1;
        day_began[3] := 31 + 28 + 1;
        day_began[4] := 31 + 28 + 31 + 1;
        day_began[5] := 31 + 28 + 31 + 30 + 1;
        day_began[6] := 31 + 28 + 31 + 30 + 31 + 1;
        day_began[7] := 31 + 28 + 31 + 30 + 31 + 30 + 1;
        day_began[8] := 31 + 28 + 31 + 30 + 31 + 30 + 31 + 1;
        day_began[9] := 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 1;
        day_began[10] := 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 1;
        day_began[11] := 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31 + 1;
        day_began[12] := 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31 + 30 + 1;

        cd := day_began[m] + d -1;

        -- si es bisiesto y la fecha es despues de febrero se le aumenta un dia
        IF (tiempo.is_leap_year(a) = TRUE and m > 2) THEN
            cd := cd + 1;
        END IF;
        
        RETURN cd;
    END;
$$
LANGUAGE 'plpgsql';    

COMMENT ON FUNCTION tiempo.day_number(INTEGER,INTEGER,INTEGER) is 'RETURNS the consecutive number of day';
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.date_to_number ------------------------- */ 
-- Consecutive number of date
CREATE OR REPLACE FUNCTION tiempo.date_to_number(d INTEGER, m INTEGER, a INTEGER) RETURNS BIGINT as
$$
    DECLARE cd INTEGER;
    
    BEGIN
        -- cantidad de dias antes del mes a partir del 1ero de enero
        cd := tiempo.day_number(d,m,a);

        -- cantidad de dias a partir del 1ero enero del 2000
        RETURN (a - 2000) * 365 + cd + (a - 2000) / 4 + bool_to_int(tiempo.is_leap_year(a) = FALSE);
    END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION tiempo.date_to_number(INTEGER,INTEGER,INTEGER) is 'RETURNS consecutive number of date';
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.hour_number_from_date_number ------------------------- */ 
CREATE or replace function tiempo.hour_number_from_date_number(m DOUBLE PRECISION) RETURNS DOUBLE PRECISION as
$$
    BEGIN
        RETURN m - (trunc(m / 864)) * 864;
    END;
$$
LANGUAGE 'plpgsql';

-- SELECT tiempo.moment_now();
-- SELECT tiempo.hour_number_from_date_number(tiempo.moment_now());
-- SELECT tiempo.number_to_hour(tiempo.hour_number_from_date_number(tiempo.moment_now()));
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.moment_number ------------------------- */ 
-- (60*24*60/100) = 864
CREATE OR REPLACE FUNCTION tiempo.moment_number(fecha BIGINT, hora DOUBLE PRECISION) RETURNS DOUBLE PRECISION as
    'SELECT ($1 - 1) * 864 + $2;'
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.today_number ------------------------- */ 
CREATE or replace function tiempo.today_number() RETURNS BIGINT as
$$
    DECLARE ahora timestamp;
        dia INTEGER;    
        mes INTEGER;
        anno INTEGER;
        f BIGINT;
        
    BEGIN
        ahora := now();
        dia := EXTRACT(DAY FROM ahora);
        mes := EXTRACT(MONTH FROM ahora);
        anno := EXTRACT(YEAR FROM ahora);
        
        f := tiempo.date_to_number(dia,mes,anno);
    
        RETURN f;

    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.moment_now ------------------------- */ 
CREATE or replace function tiempo.moment_now() RETURNS DOUBLE PRECISION as
$$
    DECLARE ahora timestamp;
        f BIGINT;
        h DOUBLE PRECISION;
        
    BEGIN

        f := tiempo.today_number();
        h := tiempo.current_hour_number();
        
        RETURN tiempo.moment_number(f, h);

    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.month_from_day_number ------------------------- */ 
-- Month from consecutive number of date
CREATE OR REPLACE FUNCTION tiempo.month_from_day_number(n INTEGER, a INTEGER) RETURNS BIGINT as
$$
    DECLARE cd INTEGER;
        x INTEGER;
    BEGIN
        x := bool_to_int(tiempo.is_leap_year(a));
        
        cd := 1 + bool_to_int(n > 31) + bool_to_int(n > 59 + x) + bool_to_int(n > 90 + x) + bool_to_int(n > 120 + x);
        cd := cd + bool_to_int(n > 151 + x) + bool_to_int(n > 181 + x) + bool_to_int(n > 212 + x) + bool_to_int(n > 243 + x);
        cd := cd + bool_to_int(n > 273 + x) + bool_to_int (n > 304 + x) + bool_to_int(n > 334 + x);

        RETURN cd;
    END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION tiempo.month_from_day_number(INTEGER,INTEGER) is 'RETURNS month from consecutive number of date';
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.number_to_date ------------------------- */ 
/**
  * Date of consecutive number
  * Esta funcion se equivocaba para una fecha superior al 31 de diciembre del 3452 
  * por la acumulacion de dias de los annos bisiestos.
  * Este error fue eliminado al cambiar la division entre 365 por 365.25
  * 0.25 se debe a dividir 1/4, es decir devidir el dia adicional de un anno bisiesto
  * en 4 y decir que todos los annos son de 365.25, 
  * para corregir el error que se introduce en el calculo del anno
  * cuando el d'ia, se hace 0, se cambia a 31 de diciembre del anno anterior.
  * Las comparaciones para n<366 se hacen para eliminar el error con la fecha 31/12/2000
  */

CREATE OR REPLACE FUNCTION tiempo.number_to_date(n BIGINT) RETURNS tiempo.ancora_date as
$$
    DECLARE f tiempo.ancora_date;
        cd INTEGER;
        b INTEGER;
    BEGIN
        IF (n<=366) THEN
            f.a = 2000;
        ELSE
            f.a := 2000 + trunc(n / 365.25);
        END IF;
        
        IF (n<=366) THEN
            cd := n;
        ELSE
            cd := n - tiempo.date_to_number(31, 12, f.a - 1);
        END IF;
        
        f.m := tiempo.month_from_day_number(cd, f.a);

        f.d := cd - tiempo.day_number(1, f.m, f.a) + 1;

        b:= tiempo.date_to_number(f.d,f.m,f.a) - n;

        IF f.d = 0 THEN
            f.a := f.a - 1;
            f.m := 12;
            f.d := 31;
        ELSE
            IF (b > 0) THEN
                -- retrasar un dia
                IF (f.d = 1 and f.m = 1) THEN
                    f.a := f.a - 1;
                    f.m := 12;
                    f.d := 31;
                ELSE
                    IF (f.d = 1) THEN
                        f.m := f.m - 1;
                        f.d := tiempo.countdays_of_month(f.m);
                    ELSE
                        f.d := f.d - 1;
                    END IF;
                END IF;
            END IF;
        END IF;    
        RETURN f;
    END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION tiempo.number_to_date(BIGINT) is 'RETURNS a date from consecutive number';
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.number_to_hour ------------------------- */ 
-- Convert number to hour
CREATE OR REPLACE FUNCTION tiempo.number_to_hour(n float) RETURNS tiempo.ancora_hour as
$$
    DECLARE t tiempo.ancora_hour;
    BEGIN
        t.h := trunc(n / 36);
        t.m := trunc((n - t.h * 36) /  0.6);
        t.s := trunc((n - (t.h * 36 + t.m * 0.6)) / 0.01);
        
        RETURN t;
    END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION tiempo.number_to_hour(float) is 'Conert number to hour';
-- SELECT tiempo.number_to_hour(863.99);/* --------------------------------------------------------- */ 
/* ------------------------------ seguridad.seguridad ------------------------- */ 
DROP SCHEMA IF EXISTS seguridad CASCADE;
CREATE SCHEMA seguridad;
/* --------------------------------------------------------- */ 
/* ------------------------------ seguridad.rol ------------------------- */ 
-- Roles de usuarios
CREATE TABLE seguridad.rol (
	nombre varchar not null primary key,
	descripcion varchar
);/* --------------------------------------------------------- */ 
/* ------------------------------ seguridad.usuario ------------------------- */ 
/*  USUARIOS  */
DROP TABLE IF EXISTS seguridad.usuario CASCADE;
CREATE TABLE seguridad.usuario(
                 id VARCHAR PRIMARY KEY 
                        DEFAULT crear_id_de_replica('ancoragoh', 'seguridad.usuario', 'id', 'seguridad.usuario'),
             nombre VARCHAR NOT NULL,
               info VARCHAR NOT NULL,
             correo VARCHAR NOT NULL,
    momento_ingreso DOUBLE PRECISION NOT NULL default tiempo.moment_now(),
              nick VARCHAR NOT NULL unique,
        contrasena VARCHAR NOT NULL
 );
/* --------------------------------------------------------- */ 
/* ------------------------------ seguridad.rol_de_usuario ------------------------- */ 
CREATE TABLE seguridad.rol_de_usuario(
	usuario varchar not null references seguridad.usuario(id) on delete cascade on update cascade,
	rol varchar not null references seguridad.rol(nombre) on delete cascade on update cascade,
	primary key(usuario, rol)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ seguridad.permiso ------------------------- */ 
CREATE TABLE seguridad.permiso(
	permiso varchar not null,
	rol varchar not null references seguridad.rol(nombre) on delete cascade on update cascade,
	primary key (permiso, rol)
);/* --------------------------------------------------------- */ 
/* ------------------------------ seguridad.tiene_permiso ------------------------- */ 
CREATE OR REPLACE FUNCTION seguridad.tiene_permiso(pusuario varchar, ppermiso varchar) RETURNS boolean AS
$$
	DECLARE r RECORD;
	BEGIN
		SELECT * INTO r FROM seguridad.permiso 
            INNER JOIN (seguridad.rol_de_usuario INNER JOIN seguridad.usuario ON seguridad.rol_de_usuario.usuario = seguridad.usuario.id)
            ON seguridad.rol_de_usuario.rol = seguridad.permiso.rol 
            WHERE seguridad.permiso.permiso = ppermiso AND seguridad.usuario.id = pusuario;

		IF FOUND THEN
			return true;
		END IF;
		
		return false;
	END;
$$
language 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ seguridad.traza ------------------------- */ 
drop table if exists seguridad.traza cascade;
create table seguridad.traza(
	fecha timestamp default now(),
	usuario varchar not null references seguridad.usuario(id) on delete cascade on update cascade,
	accion varchar,
	ip varchar
);
/* --------------------------------------------------------- */ 
/* ------------------------------ seguridad.host ------------------------- */ 
drop table if exists seguridad.host cascade;
create table seguridad.host(
	ip varchar default '*',
	usuario varchar not null references seguridad.usuario(id) on delete cascade on update cascade,
	primary key (ip, usuario)
);/* --------------------------------------------------------- */ 
/* ------------------------------ seguridad.check_host ------------------------- */ 
create or replace function seguridad.check_host(pusuario varchar, pip varchar) returns boolean as
$$
	declare r record;
	begin
		select * into r from seguridad.host where usuario = pusuario and ip = ip;

		if found then
			return true;
		end if;
		
		return false;
	end;
$$
language 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.planificacion ------------------------- */ 

-- las planificaciones no se archivan,
-- pues las planificaciones que est?n fuera de fecha ya no se ejecutan
-- lo que se puede hacer es desactivarlas o activarlas que seria como publicar o despublicar
-- por cuestiones administrativas

/* PLANIFICACIONES */
 DROP table IF EXISTS planificacion CASCADE;
 CREATE TABLE planificacion(
     id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'planificacion', 'id', 'planificacion'),
     descripcion VARCHAR NOT NULL,
     fecha_inicial BIGINT,
     cant_dias BIGINT,
     activa BOOLEAN default TRUE,
     creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
 );/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.periodo ------------------------- */ 
DROP table IF EXISTS periodo CASCADE;
CREATE TABLE periodo(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'periodo', 'id', 'periodo'),
    descripcion VARCHAR NOT NULL,
    cant_dias INTEGER default 5,
    cant_turnos INTEGER default 6,
    CHECK (cant_dias >0),
    CHECK (cant_turnos >0),
    hora_inicial DOUBLE PRECISION,
    duracion_del_turno DOUBLE PRECISION,
    planificacion VARCHAR NOT NULL REFERENCES planificacion(id) ON DELETE CASCADE ON UPDATE CASCADE
);
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.receso ------------------------- */ 
DROP table IF EXISTS receso CASCADE;
CREATE TABLE receso(
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE,
    despues_del_turno INTEGER,
    duracion DOUBLE PRECISION,
    PRIMARY KEY (periodo, despues_del_turno)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.dame_planificacion_de_periodo ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_planificacion_de_periodo(pid VARCHAR) RETURNS VARCHAR as
$$
    SELECT planificacion FROM periodo WHERE id = $1;
$$
LANGUAGE 'sql';
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.periodicidad ------------------------- */ 
DROP table IF EXISTS periodicidad CASCADE;
CREATE TABLE periodicidad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'periodicidad', 'id', 'periodicidad'),
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE,
    dia INTEGER,
    UNIQUE (periodo,dia),
    CHECK (dia > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.clasificacion_de_actividad ------------------------- */ 
DROP table IF EXISTS clasificacion_de_actividad CASCADE;
CREATE TABLE clasificacion_de_actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'clasificacion_de_actividad', 'id', 'clasificacion_de_actividad'),
    descripcion VARCHAR NOT NULL,
    duracion INTEGER default 1,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE,
    prioridad INTEGER NOT NULL default 1,
    gasto INTEGER default 1
);/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_creador_de_clasifact ------------------------- */ 
create or replace function dame_creador_de_clasifact(idclasif varchar) returns varchar as 
$$
	select creador from clasificacion_de_actividad where id = $1;
$$
language 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.grupo ------------------------- */ 
DROP table IF EXISTS grupo CASCADE;
CREATE TABLE grupo(
    id VARCHAR NOT NULL PRIMARY KEY,
    descripcion VARCHAR NOT NULL
    clasificacion VARCHAR NOT NULL REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    especialidad VARCHAR NOT NULL REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    planificacion VARCHAR NOT NULL REFERENCES planificacion(id) ON DELETE CASCADE ON UPDATE CASCADE,
    nivel VARCHAR NOT NULL REFERENCES nivel(id) ON DELETE CASCADE ON UPDATE CASCADE    
);/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.especialidad ------------------------- */ 
DROP table IF EXISTS especialidad CASCADE;
CREATE TABLE especialidad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'especialidad', 'id', 'especialidad'),
    descripcion VARCHAR NOT NULL,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.nivel ------------------------- */ 
DROP table IF EXISTS nivel CASCADE;
CREATE TABLE nivel(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'nivel', 'id', 'nivel'),
    descripcion VARCHAR NOT NULL,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.brigada ------------------------- */ 
/*  BRIGADAS   */
 DROP table IF EXISTS brigada CASCADE;
 CREATE TABLE brigada(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'brigada', 'id', 'brigada'),
    descripcion VARCHAR NOT NULL,
    especialidad VARCHAR NOT NULL REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    nivel VARCHAR NOT NULL REFERENCES nivel(id) ON DELETE CASCADE ON UPDATE CASCADE,
    matricula INTEGER default 0,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
 );
/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.brigada_de_grupo ------------------------- */ 
DROP table IF EXISTS brigada_de_grupo CASCADE;
CREATE TABLE brigada_de_grupo(
    brigada VARCHAR NOT NULL REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    grupo VARCHAR NOT NULL REFERENCES grupo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    PRIMARY KEY (brigada,grupo)
);/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.asignatura ------------------------- */ 

/* ASIGNATURAS */
 DROP table IF EXISTS asignatura CASCADE;
 CREATE TABLE asignatura(
     id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'asignatura', 'id', 'asignatura'),
     descripcion VARCHAR NOT NULL,
     especialidad VARCHAR REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
     nivel VARCHAR NOT NULL REFERENCES nivel(id) ON DELETE CASCADE ON UPDATE CASCADE,
     creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
 );/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.desglose ------------------------- */ 
DROP table IF EXISTS desglose CASCADE;
CREATE TABLE desglose(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'desglose', 'id', 'desglose'),
    asignatura VARCHAR NOT NULL REFERENCES asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    min_dias INTEGER default 1,
    max_dias INTEGER default 5,
    respetar_orden BOOLEAN default TRUE,
    mismo_dia BOOLEAN default FALSE,
    unique(asignatura,periodo)
);/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.plantilla_de_actividad ------------------------- */ 
DROP table IF EXISTS plantilla_de_actividad CASCADE;
CREATE TABLE plantilla_de_actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'plantilla_de_actividad', 'id', 'plantilla_de_actividad'),
    desglose VARCHAR NOT NULL REFERENCES desglose(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    orden INTEGER,
    clasificacion VARCHAR NOT NULL REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    unique (desglose,orden),
    exige_profesor BOOLEAN default TRUE,
    exige_lugar BOOLEAN default TRUE,
    actualizacion_interna NOT NULL BOOLEAN default FALSE
);/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_clasif_plantilla_de_actividad ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_clasif_plantilla_de_actividad(VARCHAR) RETURNS VARCHAR as
$$
    SELECT clasificacion FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_duracion_clasif_de_actividad ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_duracion_clasif_de_actividad(VARCHAR) RETURNS INTEGER as
$$
    SELECT duracion FROM clasificacion_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_duracion_plantilla_de_actividad ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_duracion_plantilla_de_actividad(VARCHAR) RETURNS INTEGER as
$$
    SELECT dame_duracion_clasif_de_actividad(clasificacion) FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.plantilla_exige_profesor ------------------------- */ 
CREATE or replace function plantilla_exige_profesor (id VARCHAR) RETURNS BOOLEAN as
$$
    SELECT exige_profesor FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.plantilla_exige_lugar ------------------------- */ 
CREATE or replace function plantilla_exige_lugar(id VARCHAR) RETURNS BOOLEAN as
$$
    SELECT exige_lugar FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.zpriori.zpriori ------------------------- */ 
DROP table IF EXISTS zpriori CASCADE;
CREATE TABLE zpriori(
    clasificacion VARCHAR NOT NULL REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    PRIMARY KEY (clasificacion,periodo,dia,turno),
    CHECK (valor > 0)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.profesor ------------------------- */ 
DROP table IF EXISTS profesor CASCADE;
CREATE TABLE profesor(
    id VARCHAR NOT NULL PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'profesor', 'id', 'profesor'),
    nombre VARCHAR,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.lugar ------------------------- */ 
DROP table IF EXISTS lugar CASCADE;
CREATE TABLE lugar(
    id VARCHAR NOT NULL PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'lugar', 'id', 'lugar'),
    nombre VARCHAR,
    capacidad INTEGER default 0,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.otro_recurso ------------------------- */ 
DROP table IF EXISTS otro_recurso CASCADE;
CREATE TABLE otro_recurso(
    id VARCHAR NOT NULL PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'otro_recurso', 'id', 'otro_recurso'),
    nombre VARCHAR,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.otro_grupo_de_recursos_para_actividad ------------------------- */ 
DROP table IF EXISTS otro_grupo_de_recursos_para_actividad CASCADE;
CREATE TABLE otro_grupo_de_recursos_para_actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'otro_grupo_de_recursos_para_actividad', 'id', 'otro_grupo_de_recursos_para_actividad'),
    plantilla VARCHAR NOT NULL REFERENCES plantilla_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    prioridad INTEGER NOT NULL default 1,
    necesarios INTEGER NOT NULL default 1,
    imprescindibles BOOLEAN NOT NULL default TRUE,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.otro_recurso_para_actividad ------------------------- */ 
DROP table IF EXISTS otro_recurso_para_actividad CASCADE;
CREATE TABLE otro_recurso_para_actividad(
    grupo_de_recursos VARCHAR NOT NULL REFERENCES otro_grupo_de_recursos_para_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    prioridad INTEGER NOT NULL default 1,
    otro_recurso VARCHAR NOT NULL REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (grupo_de_recursos,otro_recurso)
);/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.actividad ------------------------- */ 
DROP table IF EXISTS actividad CASCADE;
CREATE TABLE actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'actividad', 'id', 'actividad'),
    plantilla VARCHAR NOT NULL REFERENCES plantilla_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    grupo VARCHAR NOT NULL REFERENCES grupo(id) ON DELETE CASCADE ON UPDATE CASCADE, 

    -- el profesor y el lugar no son obligatorios a no ser que la plantilla lo exija
    
    profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    lugar VARCHAR  REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 

    CHECK ((plantilla_exige_profesor(plantilla) = TRUE AND profesor IS NOT NULL) OR plantilla_exige_profesor(plantilla) = FALSE),
    CHECK ((plantilla_exige_lugar(plantilla) = TRUE AND lugar IS NOT NULL) OR plantilla_exige_lugar(plantilla) = FALSE),
    
    dia INTEGER,
    turno INTEGER,
    momento_de_colocacion DOUBLE PRECISION,
    unique (plantilla,grupo),
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.otros_recursos_usados ------------------------- */ 
DROP table IF EXISTS otros_recursos_usados CASCADE;
CREATE TABLE otros_recursos_usados(
    otro_recurso VARCHAR NOT NULL REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE,
    actividad VARCHAR NOT NULL REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (otro_recurso, actividad)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.lugar_para_actividad ------------------------- */ 
DROP table IF EXISTS lugar_para_actividad CASCADE;
CREATE TABLE lugar_para_actividad(
    plantilla VARCHAR NOT NULL REFERENCES plantilla_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    lugar VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    prioridad INTEGER NOT NULL default 1,
    actualizacion_interna BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (plantilla,lugar)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.acceso_prohibido ------------------------- */ 
DROP table IF EXISTS acceso_prohibido CASCADE;
CREATE TABLE acceso_prohibido(
    grupo VARCHAR NOT NULL REFERENCES grupo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    lugar VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    PRIMARY KEY (grupo,lugar)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.lugar_fijo ------------------------- */ 
DROP table IF EXISTS lugar_fijo CASCADE;
CREATE TABLE lugar_fijo(
    grupo VARCHAR NOT NULL REFERENCES grupo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    lugar VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    PRIMARY KEY (grupo,lugar)
);/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.profesor_para_actividad ------------------------- */ 
DROP table IF EXISTS profesor_para_actividad CASCADE;
CREATE TABLE profesor_para_actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'profesor_para_actividad', 'id', 'profesor_para_actividad'),
    plantilla VARCHAR NOT NULL REFERENCES plantilla_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    profesor VARCHAR NOT NULL REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    prioridad INTEGER NOT NULL default 1,
    unique (plantilla, profesor)
);/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.profesor_para_grupo ------------------------- */ 
DROP table IF EXISTS profesor_para_grupo CASCADE;
CREATE TABLE profesor_para_grupo(
    profesor_para_actividad VARCHAR NOT NULL REFERENCES profesor_para_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    grupo VARCHAR NOT NULL REFERENCES grupo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    PRIMARY KEY (profesor_para_actividad,grupo)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.costo_de_traslado ------------------------- */ 
DROP table IF EXISTS costo_de_traslado CASCADE;
CREATE TABLE costo_de_traslado(
    origen VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    destino VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    costo BIGINT,
    PRIMARY KEY(origen,destino)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_periodo_desglose ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_periodo_desglose(VARCHAR) RETURNS VARCHAR as
$$
    SELECT periodo FROM desglose WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_periodo_plantilla_de_actividad ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_periodo_plantilla_de_actividad(VARCHAR) RETURNS VARCHAR as
$$
    SELECT dame_periodo_desglose(desglose) FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.higiene.higiene_para_profesor ------------------------- */ 
DROP table IF EXISTS higiene_para_profesor CASCADE;
CREATE TABLE higiene_para_profesor(
    periodo VARCHAR NOT NULL REFERENCES periodo(id)  ON DELETE CASCADE ON UPDATE CASCADE, 
    profesor VARCHAR NOT NULL REFERENCES profesor(id)  ON DELETE CASCADE ON UPDATE CASCADE, 
    gasto_maximo_diario INTEGER,
    PRIMARY KEY (periodo, profesor, gasto_maximo_diario)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.higiene.higiene_para_brigada ------------------------- */ 
DROP table IF EXISTS higiene_para_brigada CASCADE;
CREATE TABLE higiene_para_brigada(
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    brigada VARCHAR NOT NULL REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    gasto_maximo_diario INTEGER,
    PRIMARY KEY(periodo, brigada, gasto_maximo_diario)
);/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.higiene.dame_gasto_de_plantilla ------------------------- */ 
CREATE or replace function dame_gasto_de_plantilla (idplantilla VARCHAR) RETURNS INTEGER as
$$
    SELECT gasto FROM clasificacion_de_actividad WHERE id = dame_clasif_plantilla_de_actividad($1);
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.higiene.es_higienico_brigada ------------------------- */ 
CREATE or replace function es_higienico_brigada(idbrigada VARCHAR, idplantilla VARCHAR, pdia INTEGER) RETURNS BOOLEAN as
$$
    DECLARE r record;
        r1 record;
        h record;
        idperiodo VARCHAR;
        rclasif VARCHAR;
        gasto INTEGER;
    BEGIN
        idperiodo := dame_periodo_plantilla_de_actividad(idplantilla);
        gasto := dame_periodo_plantilla_de_actividad(idplantilla);

        FOR r1 IN SELECT * FROM actividad WHERE dame_periodo_plantilla_de_actividad(plantilla) = idperiodo AND dia = pdia AND matrix.get_pos_of(idbrigada, dame_lista_id_brigadas_de_grupo(grupo)) > -1 LOOP
            gasto := gasto + dame_gasto_de_plantilla(plantilla);            
        END LOOP;

        SELECT * INTO h FROM higiene_para_brigada WHERE periodo = idperiodo and brigada = idbrigada;

        IF FOUND THEN
            IF h.gasto_maximo_diario < gasto THEN
                RETURN FALSE;
            END IF;
        END IF;
        
        RETURN TRUE;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.higiene.es_higienico_profesor ------------------------- */ 
CREATE or replace function es_higienico_profesor(idprofesor VARCHAR, idplantilla VARCHAR, pdia INTEGER) RETURNS BOOLEAN as
$$
    DECLARE r record;
        r1 record;
        h record;
        idperiodo VARCHAR;
        rclasif VARCHAR;
        gasto INTEGER;
    BEGIN
        idperiodo := dame_periodo_plantilla_de_actividad(idplantilla);
        gasto := dame_periodo_plantilla_de_actividad(idplantilla);

        FOR r1 IN SELECT * FROM actividad WHERE dame_periodo_plantilla_de_actividad(plantilla) = idperiodo AND dia = pdia AND profesor = idprofesor LOOP
            gasto := gasto + dame_gasto_de_plantilla(plantilla);            
        END LOOP;

        SELECT * INTO h FROM higiene_para_profesor WHERE periodo = idperiodo and profesor = idprofesor;

        IF FOUND THEN
            IF h.gasto_maximo_diario < gasto THEN
                RETURN FALSE;
            END IF;
        END IF;
        
        RETURN TRUE;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.dame_hora_inicial_turno ------------------------- */ 
/*

    hi := (turno-1) * rperiodo.duracion_del_turno;
    hf := hi + rperiodo.duracion_del_turno;

    ti := (hi - per1.hora_inicial) / per1.duracion + 1;
    tf := (hf - per1.hora_inicial) / per1.duracion + 1;
*/
-- devuelve la hora inicial de un turno teniendo en cuenta tambien los recesos
CREATE or replace function dame_hora_inicial_turno(idperiodo VARCHAR, pturno INTEGER) RETURNS DOUBLE PRECISION as
$$
    DECLARE r record;
        h DOUBLE PRECISION;
    BEGIN
        SELECT * into r from periodo where id = idperiodo;
        h := r.hora_inicial + (pturno - 1) * r.duracion_del_turno;

        FOR r IN SELECT * from receso where periodo = idperiodo and despues_del_turno < pturno LOOP
            h := h + r.duracion;
        END LOOP;
        
        RETURN h;

    END;
$$
LANGUAGE 'plpgsql';
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.herencia.check_hrt ------------------------- */ 
CREATE or replace function check_hrt(hijo_brigada VARCHAR, hijo_profesor VARCHAR, hijo_lugar VARCHAR, hijo_otro_recurso VARCHAR, 
                     padre_brigada VARCHAR, padre_profesor VARCHAR, padre_lugar VARCHAR, padre_otro_recurso VARCHAR 
                    ) RETURNS BOOLEAN as
$$
    DECLARE c1 INTEGER; c2 INTEGER;
        ta INTEGER; tb INTEGER;
        ida VARCHAR; idb VARCHAR;
    BEGIN
        c1 := 0;
        c1 := c1 + iif(hijo_brigada is null,0,1);      
        c1 := c1 + iif(hijo_profesor is null,0,1);
        c1 := c1 + iif(hijo_lugar is null,0,1);
        c1 := c1 + iif(hijo_otro_recurso is null,0,1);

        IF (c1 > 1 OR c1 = 0) THEN
            RETURN FALSE;
        END IF;
        
        c2 := 0;
        c2 := c2 + iif(padre_brigada is null,0,1);
        c2 := c2 + iif(padre_profesor is null,0,1);
        c2 := c2 + iif(padre_lugar is null,0,1);
        c2 := c2 + iif(padre_otro_recurso is null,0,1);

        IF (c2 > 1 OR c2 = 0) THEN
            RETURN FALSE;
        END IF;

        ta := iif(hijo_brigada is null,0,1);
        ta := iif(hijo_profesor is null,0,2);
        ta := iif(hijo_lugar is null,0,3);
        ta := iif(hijo_otro_recurso is null,0,4);

        tb := iif(padre_brigada is null,0,1);
        tb := iif(padre_profesor is null,0,2);
        tb := iif(padre_lugar is null,0,3);
        tb := iif(padre_otro_recurso is null,0,4);        

        ida := iif (ta = 1, hijo_brigada, '');
        ida := iif (ta = 2, hijo_profesor, '');
        ida := iif (ta = 3, hijo_lugar, '');
        ida := iif (ta = 4, hijo_otro_recurso, '');

        idb := iif (ta = 1, padre_brigada, '');
        idb := iif (ta = 2, padre_profesor, '');
        idb := iif (ta = 3, padre_lugar, '');
        idb := iif (ta = 4, padre_otro_recurso, '');

        IF (ida = '' OR idb = '') THEN
            RETURN FALSE;
        END IF;
        
        IF (ta = tb AND ida = idb) THEN
            RETURN FALSE;
        END IF;

        RETURN TRUE;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.herencia.herencia_de_restricciones ------------------------- */ 
DROP table IF EXISTS herencia_de_restricciones CASCADE;
CREATE TABLE herencia_de_restricciones(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'herencia_de_restricciones', 'id', 'herencia_de_restricciones'),
    planificacion VARCHAR REFERENCES planificacion(id)  ON DELETE CASCADE ON UPDATE CASCADE, 
    activa BOOLEAN default TRUE,
        
    -- los foreign keys no llevan NOT NULL pues es uno de ellos el que debe llevarlo
    hijo_brigada VARCHAR REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    hijo_profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    hijo_lugar VARCHAR REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    hijo_otro_recurso VARCHAR REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 

    padre_brigada VARCHAR REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    padre_profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    padre_lugar VARCHAR REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    padre_otro_recurso VARCHAR REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 

    CHECK (check_hrt(hijo_brigada, hijo_profesor, hijo_lugar, hijo_otro_recurso, padre_brigada, padre_profesor, padre_lugar, padre_otro_recurso) = TRUE),
    unique (hijo_brigada, hijo_profesor, hijo_lugar, hijo_otro_recurso, padre_brigada, padre_profesor, padre_lugar, padre_otro_recurso)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.herencia.dame_tipo_hijo_hrt ------------------------- */ 
CREATE or replace function dame_tipo_hijo_hrt(pid VARCHAR) RETURNS VARCHAR AS
$$
    DECLARE t VARCHAR;
        r record;
    BEGIN
        SELECT * INTO r FROM herencia_de_restricciones WHERE id = pid;
        
        t := iif(r.hijo_brigada is null,'','BRIGADA');
        t := iif(r.hijo_profesor is null,'','PROFESOR');
        t := iif(r.hijo_lugar is null,'','LUGAR');
        t := iif(r.hijo_otro_recurso is null,'','OTRORECURSO');
        
        RETURN t;

    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.herencia.dame_tipo_padre_hrt ------------------------- */ 
CREATE or replace function dame_tipo_padre_hrt(pid VARCHAR) RETURNS VARCHAR AS
$$
    DECLARE t VARCHAR;
    r record;
    BEGIN
        SELECT * INTO r FROM herencia_de_restricciones WHERE id = pid;

        t := iif(r.padre_brigada is null,'','BRIGADA');
        t := iif(r.padre_profesor is null,'','PROFESOR');
        t := iif(r.padre_lugar is null,'','LUGAR');
        t := iif(r.padre_otro_recurso is null,'','OTRORECURSO');
        
        RETURN t;

    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.herencia.excepcion_de_hrt ------------------------- */ 
DROP table IF EXISTS excepcion_de_hrt CASCADE;
CREATE TABLE excepcion_de_hrt(
    herencia VARCHAR NOT NULL REFERENCES herencia_de_restricciones(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER NOT NULL,
    turno INTEGER NOT NULL,
    CHECK (dia > 0),
    CHECK (turno > 0),
    PRIMARY KEY (herencia,periodo,dia,turno)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.condicional.restriccion_condicional ------------------------- */ 

-- RESTRICCIONES CONDICIONALES

-- si ocurre tal cosa restringo tal objeto

/* RESTRICCION CONDICIONAL */
 DROP table IF EXISTS restriccion_condicional CASCADE;
 CREATE TABLE restriccion_condicional(
        id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'restriccion_condicional', 'id', 'restriccion_condicional'),
        planificacion VARCHAR REFERENCES planificacion(id)  ON DELETE CASCADE ON UPDATE CASCADE, 
        activa BOOLEAN default TRUE,
        
        /** CAUSA O PRECONDICION */   

        /** Los param para especificar actividad no llevan NOT NULL pues NULL significa cualquiera */
        /** Si una actividad se identifica con los atributos no nulos entonces es valida la causa  */
        /** y si todos son nulos tambien es valida la causa                                        */
            
        clasif_act VARCHAR REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        asig VARCHAR REFERENCES asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        brigada VARCHAR REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        lugar VARCHAR REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE,
        otro_recurso VARCHAR REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        especialidad VARCHAR REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        nivel VARCHAR REFERENCES nivel(id) ON DELETE CASCADE ON UPDATE CASCADE, 

        /* si la actividad o parte de la actividad cae en este rango */
        
        -- PARA RANGO EXACTO
            
        fecha_ini integer default null,
        fecha_fin integer default null,
        hora_ini double precision default null,
        hora_fin double precision default null,
        discreto_exacto boolean default null,
            
        -- PARA RANGO EXACTO RELATIVO A LA PLANIFICACION
        
        dia_ini_planif integer default null,
        dia_fin_planif integer default null,
        hora_ini_planif double precision default null,
        hora_fin_planif double precision default null,
        discreto_exacto_planif boolean default null,
        
        -- PARA RANGO EXACTO RELATIVO AL PERIODO
        
        periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        dia_ini INTEGER default null, 
        dia_fin INTEGER default null, 
        turno_ini INTEGER default null, 
        turno_fin INTEGER default null, 

        discreto BOOLEAN default null,

        /* si la actividad o parte ocurre en correspondiencia */
        /* con los siguientes parametros de tiempo nominal    */
        
        /** @note: los nulos se ignoran */
        
        -- PARA CASO NOMINAL
         -- tiempo de la causa en forma nominal
                 
        hoy BOOLEAN default null, 
        ahora BOOLEAN default null,
         
        dia_relativo_anterior INTEGER default 0,  -- ayer, hace 2 dias
        cant_dias_relativo_anterior INTEGER default 0,  -- cantidad antes de ese dia
        
        CHECK (dia_relativo_anterior > -1),
        CHECK (cant_dias_relativo_anterior > -1),
        
        dia_relativo_posterior INTEGER default 0, -- manana, dentro de 3 dias
        cant_dias_relativo_posterior INTEGER default 0, -- cantidad despues de ese dia

        CHECK (dia_relativo_posterior > -1),
        CHECK (cant_dias_relativo_posterior > -1),
        
        rango_tiempo_relativo_anterior_ini double precision default null, 
        rango_tiempo_relativo_anterior_tamano double precision default null, 

        turno_relativo_anterior integer default null,
        cant_turnos_relativo_anterior integer default null,

        CHECK (turno_relativo_anterior > -1),
        CHECK (cant_turnos_relativo_anterior > -1),

        turno_relativo_posterior INTEGER default 0, -- esto es despues de se que acabe la actividad
                                -- si es 1, entonces es el turno siguiente a la actividad
                                
        cant_turnos_relativo_posterior INTEGER default 0, -- cuantos despues
        
        CHECK (turno_relativo_posterior > -1),
        CHECK (cant_turnos_relativo_posterior > -1),

        rango_tiempo_relativo_posterior_ini double precision default null, 
        rango_tiempo_relativo_posterior_tamano double precision default null, 
                        
        -- entre x dias antes y z dias despues
        
        entre_dias_antes INTEGER default null,
        entre_dias_despues INTEGER default null,
        
        -- entre n turnos antes y m turno despues
        
        entre_turnos_antes integer default null, 
        entre_turnos_despues integer default null, 
        
        entre_tiempo_antes double precision DEFAULT NULL,
        entre_tiempo_despues double precision DEFAULT NULL,
        
        -- cantidad de actividades que coincidan con la condicion
        
        cant_exa INTEGER default 1, /** @significa exactamente */
        cant_min INTEGER default 1, /** @significa al menos o como minimo */
        cant_max INTEGER default 1, /** @significa no mas de, como maximo */

        /** EFECTO o POSTCONDICION */
        
        -- si la causa existe entonces se restringe a ... en el rango ...
        
        consec_brigada VARCHAR REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        consec_profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        consec_lugar VARCHAR REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        consec_otro_recurso VARCHAR REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 

        consec_periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        consec_dia_ini INTEGER DEFAULT NULL,
        consec_dia_fin INTEGER DEFAULT NULL, 
        consec_turno_ini INTEGER DEFAULT NULL, 
        consec_turno_fin INTEGER DEFAULT NULL, 

        consec_discreto BOOLEAN default TRUE,
        siempre BOOLEAN default null, -- siempre que sea ahora, hoy, hace tres dias, en este periodo....
        valor INTEGER default 1, -- valor de la restriccion

        CHECK (valor > 0)
    );
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.condicional.dame_tipo_consec_rest_cond ------------------------- */ 

-- CONOCER EL TIPO DE CONSECUENTE EN UNA RESTRICCION CONDICIONAL
CREATE or replace function dame_tipo_consec_rest_cond(pid VARCHAR) RETURNS VARCHAR AS
$$
    DECLARE t VARCHAR;
        r record;
    BEGIN
        SELECT * INTO r FROM restriccion_condicional WHERE id = pid;
        
        t := iif(r.hijo_brigada is null,'','BRIGADA');
        t := iif(r.hijo_profesor is null,'','PROFESOR');
        t := iif(r.hijo_lugar is null,'','LUGAR');
        t := iif(r.hijo_otro_recurso is null,'','OTRORECURSO');
        
        RETURN t;

    END;
$$
LANGUAGE 'plpgsql';
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.esta_dia_en_rango_planificacion ------------------------- */ 
CREATE or replace function esta_dia_en_rango_planificacion(pdia INTEGER, idperiodo VARCHAR, dia_ini INTEGER, dia_fin INTEGER) RETURNS BOOLEAN as
$$
    DECLARE r record;
    BEGIN
        SELECT * into r from periodicidad where periodo = idperiodo and (dia+pdia >=dia_ini AND dia+pdia<=dia_fin);
        IF FOUND THEN
            RETURN TRUE;
        END IF;
        
        RETURN FALSE;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_periodo ------------------------- */ 
DROP table IF EXISTS restriccion_de_periodo CASCADE;
CREATE TABLE restriccion_de_periodo(
    id VARCHAR NOT NULL PRIMARY KEY,
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    UNIQUE (periodo,dia,turno),
    CHECK (valor > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_especialidad ------------------------- */ 
DROP table IF EXISTS restriccion_de_especialidad CASCADE;
CREATE TABLE restriccion_de_especialidad(
    id VARCHAR NOT NULL PRIMARY KEY,
    especialidad VARCHAR NOT NULL REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    unique (especialidad,periodo,dia,turno),
    CHECK (valor > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_brigada ------------------------- */ 
DROP table IF EXISTS restriccion_de_brigada CASCADE;
CREATE TABLE restriccion_de_brigada(
    id VARCHAR NOT NULL PRIMARY KEY,
    brigada VARCHAR NOT NULL REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    por_actividad BOOLEAN default FALSE,
    actividad VARCHAR REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    unique(brigada,periodo,dia,turno),
    CHECK (valor > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_asignatura ------------------------- */ 
DROP table IF EXISTS restriccion_de_asignatura CASCADE;
CREATE TABLE restriccion_de_asignatura(
    id VARCHAR NOT NULL PRIMARY KEY,
    asignatura VARCHAR NOT NULL REFERENCES asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    unique (asignatura,periodo,dia,turno),
    CHECK (valor > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_clasif_act ------------------------- */ 
DROP table IF EXISTS restriccion_de_clasif_act CASCADE;
CREATE TABLE restriccion_de_clasif_act(
    id VARCHAR NOT NULL PRIMARY KEY,
    clasificacion VARCHAR NOT NULL REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    unique (clasificacion,periodo,dia,turno),
    CHECK (valor > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_profesor ------------------------- */ 
DROP table IF EXISTS restriccion_de_profesor CASCADE;
CREATE TABLE restriccion_de_profesor(
    id VARCHAR NOT NULL PRIMARY KEY,
    profesor VARCHAR NOT NULL REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    por_actividad BOOLEAN default FALSE,
    actividad VARCHAR REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    unique (profesor,periodo,dia,turno),
    CHECK (valor > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_lugar ------------------------- */ 
DROP table IF EXISTS restriccion_de_lugar CASCADE;
CREATE TABLE restriccion_de_lugar(
    id VARCHAR NOT NULL PRIMARY KEY,
    lugar VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    por_actividad BOOLEAN default FALSE,
    actividad VARCHAR REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    unique (lugar,periodo,dia,turno),
    CHECK (valor > 0)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_otro_recurso ------------------------- */ 
DROP table IF EXISTS restriccion_de_otro_recurso CASCADE;
CREATE TABLE restriccion_de_otro_recurso(
    id VARCHAR NOT NULL PRIMARY KEY,
    otro_recurso VARCHAR NOT NULL REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    por_actividad BOOLEAN default FALSE,
    actividad VARCHAR REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    unique (otro_recurso,periodo,dia,turno),
    CHECK (valor > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real CASCADE;
CREATE TABLE restriccion_real(
    restriccion_nominal VARCHAR NOT NULL,
    periodicidad VARCHAR NOT NULL,
    fecha BIGINT,
    hora_inicial DOUBLE PRECISION,
    hora_final DOUBLE PRECISION,
    por_actividad BOOLEAN NOT NULL,
    actividad VARCHAR, -- puede ser nula, pues todas las restricciones no son por actividad
    objeto VARCHAR NOT NULL,
    CHECK (hora_final > hora_inicial),
    PRIMARY KEY (restriccion_nominal, periodicidad)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.dame_dia_real_planificacion ------------------------- */ 
-- devuelve el dia real (fecha) segun un dia relativo de la planificacion
CREATE OR REPLACE FUNCTION dame_dia_real_planificacion(pid VARCHAR, dia INTEGER) RETURNS BIGINT as
$$
    SELECT fecha_inicial + $2 - 1  FROM planificacion WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_periodo ------------------------- */ 
-- Restricciones de periodos
DROP TABLE IF EXISTS restriccion_real_de_periodo CASCADE;
CREATE TABLE restriccion_real_de_periodo(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_periodo(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_asignatura ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real_de_asignatura CASCADE;
CREATE TABLE restriccion_real_de_asignatura(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_brigada ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real_de_brigada CASCADE;
CREATE TABLE restriccion_real_de_brigada(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_brigada(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (actividad) REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_especialidad ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real_de_especialidad CASCADE;
CREATE TABLE restriccion_real_de_especialidad(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_profesor ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real_de_profesor CASCADE;
CREATE TABLE restriccion_real_de_profesor(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_profesor(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_lugar ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real_de_lugar CASCADE;
CREATE TABLE restriccion_real_de_lugar(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_lugar(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_otro_recurso ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real_de_otro_recurso CASCADE;
CREATE TABLE restriccion_real_de_otro_recurso( 
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_clasif_act ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real_de_clasif_act CASCADE;
CREATE TABLE restriccion_real_de_clasif_act(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_clasif_act(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_por_actividad ------------------------- */ 
-- ------------------------------
-- RESTRICCIONES POR ACTIVIDAD
-- ------------------------------

DROP TABLE IF EXISTS restriccion_por_actividad CASCADE;
CREATE TABLE restriccion_por_actividad(
	restriccion_nominal varchar not null,
	actividad varchar not null,
	primary key (restriccion_nominal, actividad)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_por_actividad_de_profesor ------------------------- */ 
/** restricciones por actividad de profesores */

CREATE TABLE restriccion_por_actividad_de_profesor(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_profesor(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (actividad) REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal)
) INHERITS (restriccion_por_actividad);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_por_actividad_de_lugar ------------------------- */ 
CREATE TABLE restriccion_por_actividad_de_lugar(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_lugar(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (actividad) REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal)
) INHERITS (restriccion_por_actividad);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_por_actividad_de_otro_recurso ------------------------- */ 
CREATE TABLE restriccion_por_actividad_de_otro_recurso(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (actividad) REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal)
) INHERITS (restriccion_por_actividad);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_por_actividad_de_brigada ------------------------- */ 
CREATE TABLE restriccion_por_actividad_de_brigada(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_brigada(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (actividad) REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal)
) INHERITS (restriccion_por_actividad);/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.dame_lista_id_brigadas_de_grupo ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_lista_id_brigadas_de_grupo(pgrupo VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r RECORD;
    BEGIN
        FOR r IN SELECT brigada FROM brigada_de_grupo WHERE grupo = pgrupo LOOP
            arr := array_append(arr,r.brigada);
        END LOOP;
        RETURN arr;
    END;
    
$$
LANGUAGE 'plpgsql';
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restringe_segun_actividad ------------------------- */ 
CREATE OR REPLACE FUNCTION restringe_segun_actividad(
    idactividad VARCHAR, 
    idplantilla VARCHAR, 
    idprofesor VARCHAR, 
    idlugar VARCHAR, 
    pdia INTEGER, 
    pturno INTEGER, 
    idgurpo VARCHAR
) 
RETURNS VOID AS
$$
DECLARE 
    pid VARCHAR;
    listaidbrg VARCHAR[];
    cant BIGINT;
    i BIGINT; j INTEGER;
    rplantilla record;
    r record;
BEGIN

    SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = idplantilla;

    -- restringir a los implicados

    -- restringiendo a las brigadas
    listaidbrg := dame_lista_id_brigadas_de_grupo(idgrupo);
    cant := matrix.get_length_of(listaidbrg);

    i := 1;
    LOOP
        j := pturno;

        LOOP
            pid := crea_id_de_replica("ancoragoh","restriccion_de_brigada","id","restriccion_de_brigada");
            INSERT INTO restriccion_de_brigada (id, brigada, periodo, dia, turno, valor, por_actividad, actividad) VALUES (pid, listaidbrg[i], idperiodo, pdia, j, 1, TRUE, idactividad);
            INSERT INTO restriccion_por_actividad_de_brigada (restriccion_nominal, actividad) VALUES (pid, idactividad);
            
            EXIT WHEN j = pturno + rplantilla.duracion - 1;
            j := j + 1;
        END LOOP;
        
        EXIT WHEN i = cant;

        i := i + 1;
    END LOOP;
    
    -- restringiendo al profesor y lugar
    j := pturno;

    LOOP
        pid := crea_id_de_replica("ancoragoh","restriccion_de_profesor","id","restriccion_de_profesor");
        INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad, actividad) VALUES (pid, idprofesor, idperiodo, pdia, j, 1, TRUE, idactividad);
        INSERT INTO restriccion_por_actividad_de_profesor (restriccion_nominal, actividad) VALUES (pid, idactividad);
        
        pid := crea_id_de_replica("ancoragoh","restriccion_de_lugar","id","restriccion_de_lugar");
        INSERT INTO restriccion_de_lugar (id, lugar, periodo, dia, turno, valor, por_actividad, actividad) VALUES (pid, idlugar, idperiodo, pdia, j, 1, TRUE, idactividad);
        INSERT INTO restriccion_por_actividad_de_lugar (restriccion_nominal, actividad) VALUES (pid, idactividad);
        
        EXIT WHEN j = pturno + rplantilla.duracion - 1;
        j := j + 1;
    END LOOP;
   
END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restringe_por_uso_de_otro_recurso ------------------------- */ 
CREATE OR REPLACE FUNCTION restringe_por_uso_de_otro_recurso(
    idactividad VARCHAR, 
    idrecurso VARCHAR
) 
RETURNS void as
$$
DECLARE 
    cant BIGINT;
    i BIGINT; j INTEGER;
    rplantilla record;
    ractividad RECORD;
    r record;
    pid varchar;
BEGIN
    SELECT * INTO ractividad FROM actividad WHERE id = idactividad;
    SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = idplantilla;

    j := ractividad.turno;

    LOOP
       pid := crea_id_de_replica("ancoragoh","restriccion_de_otro_recurso","id","restriccion_de_otro_recurso");
       INSERT INTO restriccion_de_otro_recurso (otro_recurso, periodo, dia, turno, valor, por_actividad, actividad) VALUES (idrecurso, ractividad.periodo, ractividad.dia, j, 1, TRUE, idactividad);
       INSERT INTO restriccion_por_actividad_de_otro_recurso (restriccion_nominal, actividad) VALUES (pid, idactividad);

       EXIT WHEN j = ractividad.turno + rplantilla.duracion - 1;
       j := j + 1;
    END LOOP;
    
    EXIT WHEN i = cant;
    
END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.dame_duracion_turno_periodo ------------------------- */ 
    CREATE OR REPLACE FUNCTION dame_duracion_turno_periodo(pid VARCHAR) RETURNS DOUBLE PRECISION as
    $$
        SELECT duracion_del_turno FROM periodo WHERE id = $1;
    $$
    LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.turnos_se_solapan ------------------------- */ 
-- conocer si dos turnos de diferentes periodos se solapan
    CREATE OR REPLACE FUNCTION turnos_se_solapan(idperiodo1 VARCHAR, dia1 INTEGER, turno1 INTEGER, duracion1 INTEGER, idperiodo2 VARCHAR, dia2 INTEGER, turno2 INTEGER, duracion2 INTEGER) RETURNS BOOLEAN as
    $$
        DECLARE r1 record;
            r2 record;
            dia1 BIGINT;
            dia2 BIGINT;
            planificacion1 VARCHAR;
            planificacion2 VARCHAR;
            dia_real1 BIGINT;
            dia_real2 BIGINT;
            duracionper2 DOUBLE PRECISION;
            hora_inicial2 DOUBLE PRECISION;
            ti1 INTEGER;
            tf1 INTEGER;
            ti2 INTEGER;
            tf2 INTEGER;
            hi DOUBLE PRECISION;
            hf DOUBLE PRECISION;
        BEGIN
            planificacion1 := dame_planificacion_de_periodo(idperiodo1);
            planificacion2 := dame_planificacion_de_periodo(idperiodo2);
            dia_real1 := dame_dia_real_planificacion(planificacion1, 1);
            dia_real2 := dame_dia_real_planificacion(planificacion2, 1);

            hi := dame_hora_inicial_turno(idperiodo1, turno);
            hf := hi + dame_duracion_turno_periodo(idperiodo1) * duracion1;

            duracionper2 := dame_duracion_turno_periodo(idperiodo2);
            hora_inicial2 := dame_hora_inicial_periodo(idperiodo2);
            
            ti2 := (hi - hora_inicial2) / duracionper2 + 1;
            tf2 := (hf - hora_inicial2) / duracionper2 + 1;

            ti1 := turno2;
            tf1 := turno2 + duracion2 - 1; 

            -- verifico que se solpen segun el horario, esto es mas eficiente que recorrer
            -- cada periodicidad y preguntar lo mismo n veces

            IF tf1 < ti2 OR tf2 < ti1 THEN
                RETURN FALSE;
            END IF;
            
            -- recorro cada periodicidad del segundo por cada periodicidad del primero
            FOR r1 IN SELECT * FROM periodicidad WHERE periodo = idperiodo1 LOOP
                dia1 := dia_real1 + r1.dia - 1;
                FOR r2 IN SELECT * FROM periodicidad WHERE periodo = idperiodo2 LOOP
                    dia2 := dia_real2 + r2.dia - 1;
                    IF dia1 = dia2 THEN
                        RETURN TRUE;
                    END IF;
                END LOOP;
            END LOOP;

            RETURN FALSE;
        END;
    $$
    LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.dias_se_solapan ------------------------- */ 
CREATE or replace function dias_se_solapan (idperiodo1 VARCHAR, dia1 INTEGER, idperiodo2 VARCHAR, dia2 INTEGER) RETURNS BOOLEAN as
    $$
        DECLARE r1 record;
            r2 record;
            dia1 BIGINT;
            dia2 BIGINT;
            planificacion1 VARCHAR;
            planificacion2 VARCHAR;
            dia_real1 BIGINT;
            dia_real2 BIGINT;

        BEGIN
            planificacion1 := dame_planificacion_de_periodo(idperiodo1);
            planificacion2 := dame_planificacion_de_periodo(idperiodo2);
            dia_real1 := dame_dia_real_planificacion(planificacion1,1);
            dia_real2 := dame_dia_real_planificacion(planificacion2,1);

            -- recorro cada periodicidad del segundo por cada periodicidad del primero
            FOR r1 IN SELECT * FROM periodicidad WHERE periodo = idperiodo1 LOOP
                dia1 := dia_real1 + r1.dia - 1;
                FOR r2 IN SELECT * FROM periodicidad WHERE periodo = idperiodo2 LOOP
                    dia2 := dia_real2 + r2.dia - 1;    
                    IF dia1 = dia2 THEN
                        RETURN TRUE;
                    END IF;
                END LOOP;
            END LOOP;

            RETURN FALSE;
        END;
    $$
    LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_asignatura_desglose ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_asignatura_desglose(VARCHAR) RETURNS VARCHAR as
$$
    SELECT asignatura FROM desglose WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_asignatura_plantilla_de_actividad ------------------------- */ 

CREATE OR REPLACE FUNCTION dame_asignatura_plantilla_de_actividad(id VARCHAR) RETURNS VARCHAR as
$$
    SELECT dame_asignatura_desglose(desglose) FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_especialidad_de_asignatura ------------------------- */ 

CREATE OR REPLACE FUNCTION dame_especialidad_de_asignatura (idasig VARCHAR) RETURNS VARCHAR AS
$$
    SELECT especialidad FROM asignatura WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_nivel_de_asignatura ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_nivel_de_asignatura (idasig VARCHAR) RETURNS VARCHAR AS
$$
    SELECT nivel FROM asignatura WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.condicional.ocurre_causa_rest_cond ------------------------- */ 

-- CONOCER SI OCURRE LA CAUSA DE UNA RESTRICCION CONDICIONAL

/** <o>   que enredo!  */
/**  |                 */
/** / \                */

CREATE or replace function ocurre_causa_rest_cond(
    rc restriccion_condicional, 
    act actividad, 
    idperiodo VARCHAR, 
    pdia INTEGER, 
    pturno INTEGER, 
    analiza_completo BOOLEAN
) 
RETURNS BOOLEAN AS
$$    
DECLARE 
    r record;
    r1 record;
    tbool boolean;
    duracion_act integer;
    actperiodo varchar;
    hi double precision;
    hf double precision;
    dia_hoy bigint;
BEGIN
	actperiodo := dame_periodo_plantilla_de_actividad(act.plantilla);

    -- analizar que la actividad se identifique con los objetos
    -- y caracteristicas (asig, clasif)
    -- si las propiedades son nulas las ignoro

    -- no analizar completo significa que lo que existen atributos
    -- que se pueden analizar en una clausula WHERE de un SELECT
    -- y queda mas optimo como por ejemplo:
        /**
            SELECT * FROM actividad WHERE 
            (r.clasif_act = dame_clasif_plantilla_de_actividad(act.plantilla) OR rc.clasif_act IS NULL) AND
            (r.asig = dame_asignatura_plantilla_de_actividad(act.plantilla) OR r.asig IS NULL) AND
            (matrix.get_pos_of(rc.brigada, dame_lista_id_brigadas_de_grupo(act.grupo)) > -1 OR r.brigada IS NULL) AND
            (r.profesor = act.profesor OR r.profesor IS NULL) AND
            (r.lugar = act.lugar OR r.lugar IS NULL) AND
            (r.especialidad = dame_especialidad_de_asignatura(dame_asignatura_plantilla_de_actividad(act.plantilla)) OR r.especialidad IS NULL) AND
            (r.nivel = dame_nivel_de_asignatura(dame_asignatura_plantilla_de_actividad(act.plantilla)) OR r.nivel IS NULL) AND
            (r.periodo = actperiodo OR r.periodo IS NULL) LOOP
            
        */
    
    IF analiza_completo = TRUE THEN
        -- clasificacion de actividad
        IF rc.clasif_act IS NOT NULL THEN
            IF rc.clasif_act <> dame_clasif_plantilla_de_actividad(act.plantilla) THEN
                RETURN FALSE;
            END IF;
        END IF;

        -- asignatura
        IF rc.asig IS NOT NULL THEN
            IF rc.asig <> dame_asignatura_plantilla_de_actividad(act.plantilla) THEN
                RETURN FALSE;
            END IF;
        END IF;
        
        -- brigada
        IF rc.brigada IS NOT NULL THEN
            IF matrix.get_pos_of(rc.brigada, dame_lista_id_brigadas_de_grupo(act.grupo)) = -1 THEN
                RETURN FALSE;
            END IF;
        END IF;

        -- profesor
        IF rc.profesor IS NOT NULL THEN
            IF rc.profesor <> act.profesor THEN
                RETURN FALSE;
            END IF;
        END IF;

        -- lugar
        IF rc.lugar IS NOT NULL THEN
            IF rc.lugar <> act.lugar THEN
                RETURN FALSE;
            END IF;
        END IF;
        -- especialidad
        IF rc.especialidad IS NOT NULL THEN
            IF rc.especialidad <> dame_especialidad_de_asignatura(dame_asignatura_plantilla_de_actividad(act.plantilla)) THEN
                RETURN FALSE;
            END IF;
        END IF;

        -- nivel
        IF rc.nivel IS NOT NULL THEN
            IF rc.nivel <> dame_nivel_de_asignatura(dame_asignatura_plantilla_de_actividad(act.plantilla)) THEN
                RETURN FALSE;
            END IF;
        END IF;

        -- analizando periodo
        IF rc.periodo IS NOT NULL THEN
            IF rc.periodo <> actperiodo THEN
                RETURN FALSE;
            END IF;
        END IF;
    END IF;
    
    -- otros recursos
    IF rc.otro_recurso IS NOT NULL THEN
        SELECT * INTO r FROM otros_recursos_usados WHERE r.otro_recurso = rc.otro_recurso AND actividad = act.id;
        
        IF NOT FOUND THEN
            RETURN FALSE;
        END IF;
    END IF;

    /*  ANALIZANDO EL TIEMPO DE LA ACTIVIDAD */
    
    /** RANGO EXACTO */
    
    tbool := false;
    
    duracion_act := dame_duracion_plantilla_de_actividad(act.plantilla); -- creo la variable para calcularla una sola vez 
                                                                         --porque luego hace falta y para calcularla
    
    hi := dame_hora_inicial_turno(actperiodo, act.turno);
    hf := hi + dame_duracion_turno_periodo(actperiodo) * duracion_act;
   
    FOR r IN SELECT * FROM periodicidad WHERE  periodo = idperiodo LOOP
        dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
        
        -- se analiza de forma negativa para analizar los posibles 4*4 casos posibles
        
        /**  @explicacion: no salirse del rango discreto de tiempo por cualquiera de las */
        /**  los lados que no son nulos */
        
        SELECT * INTO r1 FROM restriccion_real WHERE actividad = act.id 
            AND ((rc.discreto_exacto = TRUE 
                    AND NOT (rc.fecha_ini IS NOT NULL AND dia_hoy < rc.fecha_ini)
                    AND NOT (rc.fecha_fin IS NOT NULL AND dia_hoy > rc.fecha_fin)
                    AND NOT (rc.hora_ini IS NOT NULL AND hf < rc.hora_ini)
                    AND NOT (rc.hora_ini IS NOT NULL AND hi > rc.hora_fin)
                  ) OR
                  (rc.discreto_exacto = FALSE 
                    AND NOT (rc.fecha_ini IS NOT NULL AND dia_hoy < rc.fecha_ini)
                    AND NOT (rc.fecha_fin IS NOT NULL AND dia_hoy > rc.fecha_fin)
                    AND NOT (rc.hora_ini IS NOT NULL AND dia_hoy = fecha_ini AND hf < rc.hora_ini)
                    AND NOT (rc.hora_ini IS NOT NULL AND dia_hoy = fecha_fin AND hi > rc.hora_fin)
                  )
                 );
        
        IF FOUND THEN
            tbool := true;
        END IF;
        EXIT WHEN tbool = true;
    END LOOP;

    IF tbool = FALSE AND (
           rc.fecha_ini IS NOT NULL 
        OR rc.fecha_fin IS NOT NULL 
        OR rc.hora_ini IS NOT NULL 
        OR rc.hora_fin IS NOT NULL) THEN
        
        RETURN FALSE;
    END IF;
    
    /** HOY */

    IF rc.hoy IS NOT NULL THEN
    
        -- buscando en todas las periodicidades 
        tbool := false;
        FOR r IN SELECT * FROM periodicidad WHERE  periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            SELECT * INTO r1 FROM restriccion_real WHERE actividad = act.id AND fecha = dia_hoy;
            IF FOUND THEN
                tbool := true;
            END IF;
            EXIT WHEN tbool = true;
        END LOOP;
        
        IF rc.hoy = TRUE THEN
            -- si dice que tiene que ser hoy y si no es hoy me voy
            
            IF tbool = FALSE THEN
                RETURN FALSE;
            END IF;
            
            /** IF dias_se_solapan(actperiodo, act.dia, idperiodo, pdia) = FALSE THEN */
            /**    RETURN FALSE; */
            /** END IF;          */
        ELSE
            -- si dice que no puede ser hoy y si es hoy me voy
            
            IF tbool = TRUE THEN
                RETURN FALSE;
            END IF;
            
            /** IF dias_se_solapan(actperiodo, act.dia, idperiodo, pdia) = TRUE THEN */
            /**    RETURN FALSE;  */
            /** END IF;           */
        END IF;
    END IF;
   
    /** AHORA */
    IF rc.ahora IS NOT NULL THEN
        tbool := false;
        hi := dame_hora_inicial_turno(idperiodo, pturno);
        hf := hi + dame_duracion_turno_periodo(idperiodo);
        
        FOR r IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            
            SELECT * INTO r1 FROM restriccion_real WHERE actividad = act.id AND fecha = dia_hoy AND NOT (hora_inicial > hf OR hora_final < hi);
            IF FOUND THEN
                tbool := true;
            END IF;
            EXIT WHEN tbool = true;
        END LOOP;
        
        IF rc.ahora = TRUE THEN
            -- se dice que tiene que ser ahora y si no es ahora me voy
            
            IF tbool = FALSE THEN
                RETURN FALSE;
            END IF;
            
            /*IF turnos_se_solapan(actperiodo, act.dia, act.turno, idperiodo, pdia, pturno <> pturno) = FALSE  THEN
                RETURN FALSE;
            END IF;*/
        ELSE
            -- se dice que no puede ser ahora y si es ahora me voy
            
            IF tbool = TRUE THEN
                RETURN FALSE;
            END IF;
            
            /*IF turnos_se_solapan(actperiodo, act.dia, act.turno, idperiodo, pdia, pturno <> pturno) = TRUE THEN
                RETURN FALSE;
            END IF;*/
        END IF;
    END IF;

    /** Dias anteriores */

    IF rc.dia_relativo_anterior IS NOT NULL THEN
        tbool := false;
        FOR r IN SELECT * FROM periodicidad WHERE  periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            SELECT * INTO r1 FROM restriccion_real WHERE actividad = act.id AND (fecha >= dia_hoy - rc.dia_relativo_anterior - rc.cant_dias_relativo_anterior + 1 AND fecha <= dia_hoy - rc.dia_relativo_anterior);
            IF FOUND THEN
                tbool := true;
            END IF;
            EXIT WHEN tbool = true;
        END LOOP;
        
        -- si no encuentra actividad en dias anteriores entonces me voy
        IF tbool = FALSE THEN
            RETURN FALSE;
        END IF;
    END IF;

    /** Dias posteriores */
    
    IF rc.dia_relativo_posterior IS NOT NULL THEN
        tbool := false;
        FOR r IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            SELECT * INTO r1 FROM restriccion_real WHERE actividad = act.id AND (fecha >= dia_hoy + rc.dia_relativo_posterior AND fecha <= dia_hoy + rc.cant_dias_relativo_posterior - 1);
            IF FOUND THEN
                tbool := true;
            END IF;
            EXIT WHEN tbool = true;
        END LOOP;
        
        IF tbool = FALSE THEN
            RETURN FALSE;
        END IF;
    END IF;
    
    /**                                                                          */ 
    /**  o   @nota Los turnos anteriores y posteriores son referentes al periodo */--
    /** <|>  No se utilizan para especificar tiempo ante o despues relativo      */--
    /** / \  al dia que se pregunta                                              */--
    /**                                                                          */--
        --                                                                           
    
    /** turnos anteriores */
    IF rc.turno_relativo_anterior IS NOT NULL THEN
        IF act.turno < pturno - rc.turno_relativo_anterior - rc.cant_turnos_relativo_anterior + 1 AND (actperiodo = rc.periodo OR rc.periodo IS NULL) THEN
            RETURN FALSE;
        END IF;
    END IF;

    /** Turnos posteriores */
    IF rc.turno_relativo_posterior IS NOT NULL THEN
        IF act.turno < pturno + rc.turno_relativo_posterior - rc.cant_turnos_relativo_posterior - 1 AND (actperiodo = rc.periodo OR rc.periodo IS NULL) THEN
            RETURN FALSE;
        END IF;
    END IF;
        
    /** Entre dias */
    IF NOT (rc.entre_dias_antes IS NULL AND rc.entre_dias_despues IS NULL) THEN -- al menos  uno de los dos debe ser no nulo
        tbool := false;
        FOR r IN SELECT * FROM periodicidad WHERE  periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            
            SELECT * INTO r1 FROM restriccion_real 
                WHERE actividad = act.id 
                AND (
                     (fecha >= dia_hoy - rc.entre_dias_antes OR rc.entre_dias_antes IS NULL) 
                      AND 
                     (fecha <= dia_hoy + rc.entre_dias_despues OR rc.entre_dias_despues IS NULL)
                    );
            
            IF FOUND THEN
                tbool := true;
            END IF;
            EXIT WHEN tbool = true;
        END LOOP;
        
        -- si no encuentra actividad entre dias
        IF tbool = FALSE THEN
            RETURN FALSE;
        END IF;
    END IF;

    /** Entre turnos */
    IF NOT (rc.entre_turnos_antes IS NULL AND rc.entre_turnos_despues IS NULL) THEN
        IF (act.turno < pturno - rc.entre_turnos_antes OR act.turno > pturno + rc.entre_turnos_despues) AND (actperiodo = rc.periodo OR rc.periodo IS NULL) THEN
            RETURN FALSE;
        END IF;
    END IF;

        -- el rango de dias y turnos real no tiene por que tener periodo
        -- se pregunta de forma general, puede que existan varios periodos
        -- con la misma configuracion de tiempo

        -- tampoco es obligatorio que exista el rango, puede que sea en todo el periodo

    /** En rango en el periodo */
    
    IF rc.periodo IS NOT NULL AND actperiodo <> rc.periodo THEN
        RETURN FALSE;
    END IF;
    
    IF (rc.discreto = TRUE) THEN
      IF (rc.dia_ini IS NOT NULL AND act.dia < rc.dia_ini) 
          OR (rc.dia_fin IS NOT NULL AND act.dia > rc.dia_fin)
          OR (rc.turno_ini IS NOT NULL AND act.turno < rc.turno_ini)
          OR (rc.turno_fin IS NOT NULL AND rc.turno_fin < act.turno + duracion_act) THEN
          RETURN FALSE;
      END IF;
    ELSE
      IF (rc.dia_ini IS NOT NULL AND act.dia < rc.dia_ini) 
          OR (rc.dia_fin IS NOT NULL AND act.dia > rc.dia_fin)
          OR (rc.turno_ini IS NOT NULL AND act.dia = rc.dia_ini AND act.turno < rc.turno_ini)
          OR (rc.turno_fin IS NOT NULL AND act.dia = rc.dia_fin AND rc.turno_fin < act.turno + duracion_act) THEN
          RETURN FALSE;
      END IF;
    END IF;

    /** En rango en la planificacion */
    
    hi := dame_hora_inicial_turno(actperiodo, act.turno);
    hf := hi + dame_duracion_turno_periodo(actperiodo) * duracion_act;
    
    tbool := false;
    FOR r IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP
        dia_hoy := act.dia + pdia - 1;
        
        IF rc.discreto_exacto_planif = TRUE THEN
            IF NOT ((rc.dia_ini_planif IS NOT NULL AND dia_hoy < rc.dia_ini_planif)
                    OR (rc.dia_fin_planif IS NOT NULL AND dia_hoy > rc.dia_fin_planif)
                    OR (rc.hora_ini_planif IS NOT NULL AND hf < rc.hora_ini_planif)
                    OR (rc.hora_fin_planif IS NOT NULL AND hi > rc.hora_fin_planif)
                    ) THEN
            
            tbool := TRUE;
            
            END IF;
        ELSE
             IF NOT ((rc.dia_ini_planif IS NOT NULL AND dia_hoy < rc.dia_ini_planif)
                    OR (rc.dia_fin_planif IS NOT NULL AND dia_hoy > rc.dia_fin_planif)
                    OR (rc.hora_ini_planif IS NOT NULL AND dia_hoy = rc.dia_ini_planif AND hf < rc.hora_ini_planif)
                    OR (rc.hora_fin_planif IS NOT NULL AND dia_hoy = rc.dia_fin_planif AND hi > rc.hora_fin_planif)
                    ) THEN
            
            tbool := TRUE;
            
            END IF;
        END IF;
           
        EXIT WHEN tbool = true;
     END LOOP;
    
     IF tbool = FALSE THEN
         RETURN FALSE;
     END IF;
    
     /** Tiempo relativo anterior */

     hi := dame_hora_inicial_turno(idperiodo, pturno);
     hf := hi + dame_duracion_turno_periodo(idperiodo);
     
     IF rc.rango_tiempo_relativo_anterior_ini IS NOT NULL THEN
         tbool := false;
         FOR r IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            
            IF rc.rango_tiempo_relativo_anterior_tamano IS NOT NULL THEN
                SELECT * INTO r1 FROM restriccion_real 
                    WHERE actividad = act.id 
                    AND NOT tiempo.moment_number(fecha,hora_fin) < tiempo.moment_number(dia_hoy,hi) - rc.rango_tiempo_relativo_anterior_ini - rango_tiempo_relativo_anterior_tamano 
                    AND NOT tiempo.moment_number(fecha,hora_ini) > tiempo.moment_number(dia_hoy,hi) - rc.rango_tiempo_relativo_anterior_ini;
            ELSE
                SELECT * INTO r1 FROM restriccion_real 
                    WHERE actividad = act.id 
                    AND NOT tiempo.moment_number(fecha,hora_ini) > tiempo.moment_number(dia_hoy,hi) - rc.rango_tiempo_relativo_anterior_ini;
            END IF;
            
            IF FOUND THEN
                tbool := true;
            END IF;
            
            EXIT WHEN tbool = true;
          END LOOP;

          IF tbool = FALSE THEN
              RETURN FALSE;
          END IF;
      END IF;
    
    /** Tiempo relativo posterior */
    
     IF rc.rango_tiempo_relativo_posterior_ini IS NOT NULL THEN
         tbool := false;
         FOR r IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            
            IF rc.rango_tiempo_relativo_posterior_tamano IS NOT NULL THEN
                SELECT * INTO r1 FROM restriccion_real 
                    WHERE actividad = act.id 
                    AND NOT tiempo.moment_number(fecha,hora_fin) < tiempo.moment_number(dia_hoy,hi) + rc.rango_tiempo_relativo_posterior_ini
                    AND NOT tiempo.moment_number(fecha,hora_ini) > tiempo.moment_number(dia_hoy,hi) + rc.rango_tiempo_relativo_posterior_ini + rango_tiempo_relativo_posterior_tamano;
            ELSE
                SELECT * INTO r1 FROM restriccion_real 
                    WHERE actividad = act.id 
                    AND NOT tiempo.moment_number(fecha,hora_fin) < tiempo.moment_number(dia_hoy,hi) + rc.rango_tiempo_relativo_posterior_ini;
            END IF;
            
            IF FOUND THEN
                tbool := true;
            END IF;
            
            EXIT WHEN tbool = true;
          END LOOP;

          IF tbool = FALSE THEN
              RETURN FALSE;
          END IF;
      END IF;
      
     /** Entre tiempos */
    
     IF NOT (rc.entre_tiempo_antes IS NULL AND rc.entre_tiempo_despues IS NULL) THEN
         tbool := false;
         FOR r IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP
            dia_hoy := dame_dia_real_planificacion(r.planificacion, r.dia) + pdia - 1;
            
            SELECT * INTO r1 FROM restriccion_real 
                WHERE actividad = act.id AND
                (
                 (tiempo.moment_number(fecha,hora_fin) >= tiempo.moment_number(dia_hoy,hi) - rc.entre_tiempo_antes OR rc.entre_tiempo_antes IS NULL) 
                  AND 
                 (tiempo.moment_number(fecha,hora_ini) <= tiempo.moment_number(dia_hoy,hf) + rc.entre_tiempo_despues OR rc.entre_tiempo_despues IS NULL) 
                ); 
                    
            IF FOUND THEN
                tbool := true;
            END IF;
            
            EXIT WHEN tbool = true;
          END LOOP;

          IF tbool = FALSE THEN
              RETURN FALSE;
          END IF;
      END IF;
      
    RETURN TRUE;
END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.dame_idobjeto_de_restriccion_de_periodo ------------------------- */ 
       CREATE OR REPLACE FUNCTION dame_idobjeto_de_restriccion_de_periodo(pid varchar) returns varchar as
       $$
            SELECT periodo FROM restriccion_de_periodo WHERE id = $1;
       $$
       LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.dame_idobjeto_de_restriccion_de_especialidad ------------------------- */ 
       CREATE OR REPLACE FUNCTION dame_idobjeto_de_restriccion_de_especialidad(pid varchar) returns varchar as
       $$
            SELECT especialidad FROM restriccion_de_especialidad WHERE id = $1;
       $$
       LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.dame_idobjeto_de_restriccion_de_brigada ------------------------- */ 
       CREATE OR REPLACE FUNCTION dame_idobjeto_de_restriccion_de_brigada(pid varchar) returns varchar as
       $$
            SELECT brigada FROM restriccion_de_brigada WHERE id = $1;
       $$
       LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.valor_restriccion_generica ------------------------- */ 
CREATE or replace function valor_restriccion_generica(ptipo VARCHAR, id VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER, tipo_restricc VARCHAR) RETURNS INTEGER as
        $$
        DECLARE r record;
            rperiodo record;
            rper record;
            r1 record;
            r2 record;
            dia_analizando INTEGER;
            hi DOUBLE PRECISION; hf DOUBLE PRECISION;
            ti INTEGER; tf INTEGER;
            i INTEGER;
            per1 record;
            tipo VARCHAR;
        BEGIN
            
            -- restricciones en el periodo

            tipo := upper(ptipo);

            IF tipo = 'PERIODO' THEN
                SELECT valor INTO r from restriccion_de_periodo where periodo = id and dia = pdia and turno = pturno; 
            END IF;
            
            IF tipo = 'ASIGNATURA' THEN
                SELECT valor INTO r from restriccion_de_asignatura where asignatura = id AND periodo = idperiodo and dia = pdia and turno = pturno;
            END IF;

            IF tipo = 'BRIGADA' THEN
                SELECT valor INTO r from restriccion_de_brigada where brigada = id AND periodo = idperiodo and dia = pdia and turno = pturno AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
            END IF;

            IF tipo = 'CLASIFACT' THEN
                SELECT valor INTO r from restriccion_de_clasif_act where clasif_act = id AND periodo = idperiodo and dia = pdia and turno = pturno;
            END IF;

            IF tipo = 'ESPECIALIDAD' THEN
                SELECT valor INTO r from restriccion_de_especialidad where especialidad = id AND periodo = idperiodo and dia = pdia and turno = pturno;
            END IF;

            IF tipo = 'LUGAR' THEN
                SELECT valor INTO r from restriccion_de_lugar where lugar = id AND periodo = idperiodo and dia = pdia and turno = pturno AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
            END IF;

            IF tipo = 'OTRORECURSO' THEN
                SELECT valor INTO r from restriccion_de_otro_recurso where otro_recurso = id AND periodo = idperiodo and dia = pdia and turno = pturno AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
            END IF;

            IF tipo = 'PROFESOR' THEN
                SELECT valor INTO r from restriccion_de_profesor where profesor = id AND periodo = idperiodo and dia = pdia and turno = pturno AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
            END IF;

            IF FOUND THEN
                RETURN r.valor;
            END IF;
            
            /** @nota: Ahora se busca en las restricciones reales. El codigo anterior puede */
            /** quitarse y entonces recorrer todas las restricciones reales,                */
            /** pero se deja para optimizar la busqueda y se recrre entonces despues las    */
            /** demas periodicidades, pues basta preguntar por el turno de un periodo y no  */
            /** por todas sus periodicidades para saber si esta restringido                 */
                        
            -- restricciones en otros periodos
            
            SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;

            hi := dame_hora_inicial_turno(rperiodo.id, pturno);
            hf := hi + rperiodo.duracion_del_turno;

            -- se analizan las periodicidades (recorriendo la temporada)
            FOR r IN SELECT * FROM periodicidad WHERE periodo <> idperiodo LOOP

                -- se calcula la fecha real
                dia_analizando := dame_dia_real_planificacion(rperiodo.planificacion, r.dia + pdia - 1);
                
                -- se busca en las restricciones reales
                
                IF tipo = 'PERIODO' THEN
                    SELECT valor INTO r 
                        FROM restriccion_real_de_periodo 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi);
                END IF;
                
                IF tipo = 'ASIGNATURA' THEN
                    SELECT valor INTO r 
                        FROM restriccion_real_de_asignatura
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi);                 
                END IF;

                IF tipo = 'BRIGADA' THEN
                    SELECT valor INTO r 
                        FROM restriccion_real_de_brigada 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi)
                        AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                END IF;

                IF tipo = 'CLASIFACT' THEN
                    SELECT valor INTO r 
                        FROM restriccion_real_de_clasif_act
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi);                 
                END IF;

                IF tipo = 'ESPECIALIDAD' THEN
                    SELECT valor INTO r 
                        FROM restriccion_real_de_especialidad
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi);                 
                END IF;

                IF tipo = 'LUGAR' THEN
                    SELECT valor INTO r 
                        FROM restriccion_real_de_lugar 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi)
                        AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                END IF;

                IF tipo = 'OTRORECURSO' THEN
                    SELECT valor INTO r 
                        from restriccion_real_de_otro_recurso 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi)
                        AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                END IF;

                IF tipo = 'PROFESOR' THEN
                    SELECT valor INTO r 
                        from restriccion_real_de_profesor 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi)
                        AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                END IF;

                IF FOUND THEN
                    RETURN r2.valor;
                END IF;
            END LOOP;    
            
            /**
            -- se analizan las periodicidades (recorriendo la temporada)
            FOR r IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP

                dia_analizando := r.dia + pdia - 1;
                 
                -- se analizan los otros periodos

                FOR r1 IN SELECT * FROM periodicidad WHERE periodo <> idperiodo AND dia_analizando >= dia AND dia_analizando <= dia + dame_cant_dias(periodo) - 1 LOOP


                    -- hay que normalizar las configuraciones de tiempo de ambos periodos ...
                    
                    SELECT * INTO per1 FROM periodo WHERE id = r1.periodo;
                    
                    hi := dame_hora_inicial_turno(rperiodo.id, pturno);
                    hf := hi + rperiodo.duracion_del_turno;

                    ti := (hi - per1.hora_inicial) / per1.duracion + 1;
                    tf := (hf - per1.hora_inicial) / per1.duracion + 1;

                    -- para despues poder questionar las restricciones del segundo

                    -- nota: el dia a questionar es:  dia_analizando - r1.dia + 1
                    
                    i := ti;
                    LOOP

                        IF tipo = 'PERIODO' THEN
                            SELECT valor INTO r from restriccion_de_periodo where periodo = r1.periodo and dia = dia_analizando - r1.dia + 1  and turno = i;
                        END IF;
                        
                        IF tipo = 'ASIGNATURA' THEN
                            SELECT valor INTO r from restriccion_de_asignatura where asignatura = id AND periodo = r1.periodo and dia = dia_analizando - r1.dia + 1 and turno = i;
                        END IF;

                        IF tipo = 'BRIGADA' THEN
                            SELECT valor INTO r from restriccion_de_brigada where brigada = id AND periodo = r1.periodo and dia = dia_analizando - r1.dia + 1 and turno = i AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        END IF;

                        IF tipo = 'CLASIFACT' THEN
                            SELECT valor INTO r from restriccion_de_clasif_act where clasif_act = id AND periodo = r1.periodo and dia = dia_analizando - r1.dia + 1 and turno = i;
                        END IF;

                        IF tipo = 'ESPECIALIDAD' THEN
                            SELECT valor INTO r from restriccion_de_especialidad where especialidad = id AND periodo = r1.periodo and dia = dia_analizando - r1.dia + 1 and turno = i;
                        END IF;

                        IF tipo = 'LUGAR' THEN
                            SELECT valor INTO r from restriccion_de_lugar where lugar = id AND periodo = r1.periodo and dia = dia_analizando - r1.dia + 1 and turno = i AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        END IF;

                        IF tipo = 'OTRORECURSO' THEN
                            SELECT valor INTO r from restriccion_de_otro_recurso where otro_recurso = id AND periodo = r1.periodo and dia = dia_analizando - r1.dia + 1 and turno = i AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        END IF;

                        IF tipo = 'PROFESOR' THEN
                            SELECT valor INTO r from restriccion_de_profesor where profesor = id AND periodo = r1.periodo and dia = dia_analizando - r1.dia + 1 and turno = i AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        END IF;

                        IF FOUND THEN
                            RETURN r2.valor;
                        END IF;
                        
                        EXIT WHEN i = tf;
                        i := i + 1;
                    END LOOP;
                    
                END LOOP;
            
            END LOOP;*/
            
            RETURN 0;
            
        END;
    $$
    LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.condicional.valor_restriccion_condicional ------------------------- */ 
-- CONOCER RESTRICCIONES CONDICIONALES
    CREATE or replace function valor_restriccion_condicional(tipo_consec VARCHAR, id_consec VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        DECLARE r record;
            ract record;
            c BIGINT;
        BEGIN
            -- se analizan las restricciones condicionales
            -- donde el implicado se encuentren en las consecuencias o
            -- postcondiciones de la restriccion (tipo regla)

            FOR r IN SELECT * FROM restriccion_condicional 
                            WHERE activa = TRUE AND dame_tipo_consec_rest_cond(id) = tipo_consec AND (consec_periodo = idperiodo OR consec_periodo IS NULL) LOOP
              
                IF siempre = FALSE THEN
                    IF (r.discreto = TRUE) THEN
                      CONTINUE WHEN (rcdia_ini IS NOT NULL AND act.dia < r.dia_ini) 
                          OR (r.dia_fin IS NOT NULL AND act.dia > r.dia_fin)
                          OR (r.turno_ini IS NOT NULL AND act.turno < r.turno_ini)
                          OR (r.turno_fin IS NOT NULL AND r.turno_fin < act.turno + duracion_act);
                    ELSE
                      CONTINUE WHEN (r.dia_ini IS NOT NULL AND act.dia < r.dia_ini) 
                          OR (r.dia_fin IS NOT NULL AND act.dia > r.dia_fin)
                          OR (r.turno_ini IS NOT NULL AND act.dia = r.dia_ini AND act.turno < r.turno_ini)
                          OR (r.turno_fin IS NOT NULL AND act.dia = r.dia_fin AND r.turno_fin < act.turno + duracion_act);
                    END IF;
                END IF;
                
                -- ahora se busca si la causa existe

                c := 0;
                
                FOR ract IN SELECT * FROM actividad WHERE 
                    (r.clasif_act = dame_clasif_plantilla_de_actividad(act.plantilla) OR rc.clasif_act IS NULL) AND
                    (r.asig = dame_asignatura_plantilla_de_actividad(act.plantilla) OR r.asig IS NULL) AND
                    (matrix.get_pos_of(rc.brigada, dame_lista_id_brigadas_de_grupo(act.grupo)) > -1 OR r.brigada IS NULL) AND
                    (r.profesor = act.profesor OR r.profesor IS NULL) AND
                    (r.lugar = act.lugar OR r.lugar IS NULL) AND
                    (r.especialidad = dame_especialidad_de_asignatura(dame_asignatura_plantilla_de_actividad(act.plantilla)) OR r.especialidad IS NULL) AND
                    (r.nivel = dame_nivel_de_asignatura(dame_asignatura_plantilla_de_actividad(act.plantilla)) OR r.nivel IS NULL) AND
                    (r.periodo = actperiodo OR r.periodo IS NULL) LOOP
                    
                
                    IF ocurre_causa_rest_cond(r, ract, pdia, pturno, FALSE) THEN
                        c := c + 1;
                    END IF;
                    
                END LOOP;

                -- uno de los siguientes 3 IF debe entrar
                IF r.cant_exa IS NOT NULL THEN
                    CONTINUE WHEN c <> r.cant_exa;            
                END IF;

                IF r.cant_min IS NOT NULL THEN
                    CONTINUE WHEN c < r.cant_exa;            
                END IF;

                IF r.cant_max IS NOT NULL THEN
                    CONTINUE WHEN c > r.cant_exa;            
                END IF;

                -- si no se cumplen la cantidad de casos 
                -- en la causa debe saltarse lo siguiente
                -- y continuar a la proxima restriccion condicional
                
                -- si al menos una restriccion se cumple ...

                RETURN r.valor;

            END LOOP;
            
            RETURN 0;
        END;
    $$
    LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.valor_restriccion_periodo ------------------------- */ 
    CREATE or replace function valor_restriccion_periodo(idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        BEGIN
            RETURN valor_restriccion_generica('PERIODO', idperiodo, '', pdia, pturno, '*');            
        END;
    $$
    LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.valor_restriccion_asignatura ------------------------- */ 
    CREATE or replace function valor_restriccion_asignatura (id VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        BEGIN
            RETURN valor_restriccion_generica('ASIGNATURA',id, idperiodo, pdia, pturno, '*');
        END;
        
    $$
    LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.valor_restriccion_brigada ------------------------- */ 
    -- brigada
    CREATE or replace function valor_restriccion_brigada (id VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        DECLARE v INTEGER;
        BEGIN
            v := valor_restriccion_generica('BRIGADA',id, idperiodo, pdia, pturno, '*');
            
            IF v = 0 THEN
                v := valor_restriccion_heredada('BRIGADA', id, idperiodo, pdia, pturno);
            END IF;
            
            IF v = 0 THEN
                v := valor_restriccion_condicional('BRIGADA', id, idperiodo, pdia, pturno);
            END IF;
            
            RETURN v;
        END;
        
    $$
    LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.valor_restriccion_clasif_act ------------------------- */ 
    -- clasificacion de actividad
    CREATE or replace function valor_restriccion_clasif_act (id VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        BEGIN
            RETURN valor_restriccion_generica('CLASIFACT',id, idperiodo, pdia, pturno, '*');
        END;
        
    $$
    LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.valor_restriccion_especialidad ------------------------- */ 
    -- especialidad
    CREATE or replace function valor_restriccion_especialidad(id VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        BEGIN
            RETURN valor_restriccion_generica('ESPECIALIDAD',id, idperiodo, pdia, pturno, '*');
        END;
        
    $$
    LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.valor_restriccion_lugar ------------------------- */ 
 -- lugar
    CREATE or replace function valor_restriccion_lugar(id VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        DECLARE v INTEGER;
        BEGIN
            v := valor_restriccion_generica('LUGAR', id, idperiodo, pdia, pturno, '*');

            IF v = 0 THEN
                v := valor_restriccion_heredada('LUGAR', id, idperiodo, pdia, pturno);
            END IF;

            IF v = 0 THEN
                v := valor_restriccion_condicional('LUGAR', id, idperiodo, pdia, pturno);
            END IF;
            
            RETURN v;
        END;
        
    $$
    LANGUAGE 'plpgsql';
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.valor_restriccion_profesor ------------------------- */ 
CREATE or replace function valor_restriccion_profesor(id VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        DECLARE v INTEGER;
        BEGIN
            v := valor_restriccion_generica('PROFESOR', id, idperiodo, pdia, pturno, '*');

            IF v = 0 THEN
                v := valor_restriccion_heredada('PROFESOR', id, idperiodo, pdia, pturno);
            END IF;

            IF v = 0 THEN
                v := valor_restriccion_condicional('PROFESOR', id, idperiodo, pdia, pturno);
            END IF;
            
            RETURN v;
        END;
        
    $$
    LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.zpriori.valor_zpriori ------------------------- */ 
-- zpriori
    -- clasificacion de actividad
    CREATE or replace function valor_zpriori(clasif VARCHAR, periodo VARCHAR, dia INTEGER, turno INTEGER) RETURNS INTEGER as
    $$
        SELECT valor from zpriori where clasificacion = $1 and periodo = $2 and dia = $3 and turno = $4
    $$
    LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_duracion_actividad ------------------------- */ 
    -- duracion de una actividad 
    CREATE or replace function dame_duracion_actividad(id VARCHAR) RETURNS INTEGER as
    $$
        SELECT dame_duracion_plantilla_de_actividad(plantilla) from actividad where id =$1
    $$
    LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.dame_creador_de_planificacion ------------------------- */ 
CREATE or replace function dame_creador_de_planificacion(id VARCHAR) RETURNS VARCHAR as
$$
    SELECT creador FROM planificacion WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_max_priori_clasif_act ------------------------- */ 
﻿CREATE or replace function dame_max_priori_clasif_act(pcreador varchar) RETURNS INTEGER as
$$
    DECLARE r record;
    BEGIN
        
        SELECT max(prioridad) as m into r FROM clasificacion_de_actividad WHERE creador='admin-superuser' GROUP BY creador;

        IF r.m is null THEN
            RETURN 1;
        END IF;
        
        RETURN r.m;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_cant_act ------------------------- */ 

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_act
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de actividades de una asignatura en un per?o dado
--     EJEMPLO: SELECT dame_cant_act('mat','si');
-- -----------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION dame_cant_act(asig VARCHAR, periodo VARCHAR) RETURNS BIGINT as
$$
    SELECT count(plantilla_de_actividad.id)
    FROM asignatura 
    INNER JOIN desglose 
        ON asignatura.id = desglose.asignatura
    INNER JOIN plantilla_de_actividad 
        ON plantilla_de_actividad.desglose = desglose.id
    WHERE asignatura.id = $1 AND desglose.periodo = $2;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_asig_segun_esp_anno_per ------------------------- */ 

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_asig_segun_esp_anno_per
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de asignaturas de la esp y anno dados
--        ordebados de mayor a menor por la cantidad de actividades en un per?o dado
--     EJEMPLO: SELECT  dame_asig_segun_esp_anno_per('info','1','sp');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_asig_segun_esp_anno_per(idesp VARCHAR, idnivel VARCHAR, idperiodo VARCHAR) RETURNS VARCHAR[] as
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM asignatura WHERE especialidad = idesp AND nivel = idnivel ORDER BY dame_cant_act(asignatura.id, idperiodo) DESC LOOP
            arr := array_append(arr, r.id);
        END LOOP;
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.asignaturas_cant_act ------------------------- */ 
-- -----------------------------------------------------------------------------------
--      NOMBRE: asignaturas_cant_act
--        TIPO: vista
-- DESCRIPCION: Listado de las asignaturas con sus cantidades de actividades por periodo
--     EJEMPLO: SELECT  * from asignaturas_cant_act;
-- -----------------------------------------------------------------------------------
DROP VIEW IF EXISTS asignaturas_cant_act CASCADE;
CREATE OR REPLACE VIEW asignaturas_cant_act AS
    SELECT asignatura.id as asignatura, especialidad, nivel, periodo, dame_cant_act(asignatura.id, periodo) as cantidad FROM asignatura INNER JOIN desglose ON desglose.asignatura = asignatura.id;
/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.max_cant_act ------------------------- */ 
-- -----------------------------------------------------------------------------------
--      NOMBRE: max_cant_act
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad m?ma de actividades las asignaturas
--              de una especialidad en un per?o espec?co
--     EJEMPLO: SELECT max_cant_act('info','si');
-- -----------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION max_cant_act(especialidad VARCHAR, nivel VARCHAR, periodo VARCHAR) RETURNS BIGINT as
$$
    SELECT max(cantidad)
    FROM asignaturas_cant_act 
    WHERE especialidad = $1 AND nivel = $2 AND periodo = $3;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.uso_de_otros_recursos ------------------------- */ 
-- -----------------------------------------------------------------------------------
--      NOMBRE: uso_de_otros_recursos
--        TIPO: vista
-- DESCRIPCION: Listado del uso de los recursos
--     EJEMPLO: SELECT  * from uso_de_otros_recursos;
-- -----------------------------------------------------------------------------------
DROP VIEW IF EXISTS uso_de_otros_recursos CASCADE;
CREATE VIEW uso_de_otros_recursos AS
    SELECT otro_recurso, count(actividad) FROM otros_recursos_usados
    GROUP BY otro_recurso;/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.dame_uso_de_otro_recurso ------------------------- */ 
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_uso_de_otro_recurso
--        TIPO: funcion
-- DESCRIPCION: Uso de un recurso dado
--     EJEMPLO: SELECT  dame_uso_de_otro_recurso('pc');
-- -----------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION dame_uso_de_otro_recurso(id VARCHAR) RETURNS BIGINT as
$$
    SELECT count(actividad) FROM otros_recursos_usados WHERE otro_recurso = $1
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_asig_segun_esp_anno ------------------------- */ 
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_asig_segun_esp_anno
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de asignaturas de la esp y anno dados
--     EJEMPLO: SELECT  dame_asig_segun_esp_anno('info','1');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_asig_segun_esp_anno(idesp VARCHAR, idnivel VARCHAR) RETURNS VARCHAR[] as
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM asignatura WHERE especialidad = idesp AND nivel = idnivel LOOP
            arr := array_append(arr, r.id);
        END LOOP;
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_cant_asig_de_esp_anno ------------------------- */ 
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
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_clasif_de_asignaturas ------------------------- */ 

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_clasif_de_asignaturas
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de clasificaciones de actividades de una 
--        asignatura en un periodo dado
--     EJEMPLO: SELECT  dame_asig_segun_esp_anno('mat','si');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_clasif_de_asignaturas(asignatura VARCHAR, periodo VARCHAR) RETURNS VARCHAR[] as
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT plantilla_de_actividad.clasificacion as clasif 
             FROM plantilla_de_actividad 
             INNER JOIN desglose ON plantilla_de_actividad.desglose = desglose.id 
             WHERE desglose.periodo = periodo AND desglose.asignatura = asignatura 
             ORDER BY plantilla_de_actividad.orden LOOP
            arr := array_append(arr, r.clasif);
        END LOOP;
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_cant_clasif ------------------------- */ 
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_clasif
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de clasificaciones
--     EJEMPLO: SELECT  dame_cant_clasif();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_clasif() RETURNS BIGINT AS
$$
    SELECT count(id) AS cant FROM clasificacion_de_actividad;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_lista_id_clasif ------------------------- */ 

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_clasif
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de la clasificaciones
--     EJEMPLO: SELECT  dame_lista_id_clasif();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_clasif() RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM clasificacion_de_actividad ORDER BY prioridad LOOP
            arr := array_append(arr, r.id);
        END LOOP;
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.dame_cant_profes_asignados ------------------------- */ 
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_profes_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de profesores asignados a una actividad
--     EJEMPLO: SELECT  dame_cant_profes_asignados('act1');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_profes_asignados(idplantilla VARCHAR) RETURNS BIGINT AS
$$
    SELECT count(id) FROM profesor_para_actividad WHERE plantilla = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_id_plantilla ------------------------- */ 

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_id_plantilla
--        TIPO: funcion
-- DESCRIPCION: Devuelve el id de la plantilla de actividad segun asignatura, periodo y numero de orden
--     EJEMPLO: SELECT  dame_id_plantilla('act1');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_id_plantilla(idasig VARCHAR, idperiodo VARCHAR, orden BIGINT) RETURNS VARCHAR AS
$$
    SELECT plantilla_de_actividad.id FROM plantilla_de_actividad INNER JOIN desglose ON plantilla_de_actividad.desglose = desglose.id WHERE desglose.asignatura = $1 AND desglose.periodo = $2 AND plantilla_de_actividad.orden = $3;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.dame_cant_dias ------------------------- */ 

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_dias
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de dias de un periodo
--     EJEMPLO: SELECT dame_cant_dias('si');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_dias(idperiodo VARCHAR) RETURNS INTEGER AS
$$
    SELECT cant_dias FROM periodo WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.dame_cant_turnos ------------------------- */ 
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_turnos
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de turnos de un periodo
--     EJEMPLO: SELECT dame_cant_turnos('si');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_turnos(idperiodo VARCHAR) RETURNS INTEGER AS
$$
    SELECT cant_turnos FROM periodo WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.existe_grupo_de_profesor ------------------------- */ 
CREATE OR REPLACE FUNCTION existe_grupo_de_profesor(profe_para_act VARCHAR, pgrupo VARCHAR) RETURNS BOOLEAN as
$$
    DECLARE r record;
    BEGIN
        SELECT * INTO r FROM profesor_para_grupo WHERE profesor_para_actividad = profe_para_act AND grupo = pgrupo;    
        IF r.grupo = pgrupo THEN
            RETURN TRUE;
        END IF;
        RETURN FALSE;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.dame_lista_id_profes_asignados_al_grupo ------------------------- */ 

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_profes_asignados_al_grupo
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los PROFESORES asignados a una plantilla 
--              de actividad y a un grupo determinado
--     EJEMPLO: SELECT  dame_lista_id_profes_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_profes_asignados_al_grupo(idplantilla VARCHAR, idgrupo VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM profesor_para_actividad WHERE plantilla = idplantilla AND EXISTS(SELECT * FROM profesor_para_grupo WHERE profesor_para_grupo.profesor_para_actividad = profe_para_act AND profesor_para_grupo.grupo = pgrupo) ORDER BY prioridad LOOP
            arr := array_append(arr,r.profesor);
        END LOOP;

        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.dame_lista_id_profes_asignados ------------------------- */ 

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_profes_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los PROFESORES asignados a una plantilla de actividad
--     EJEMPLO: SELECT  dame_lista_id_profes_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_profes_asignados(idplantilla VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM profesor_para_actividad WHERE plantilla = idplantilla LOOP
            arr := array_append(arr,r.profesor);
        END LOOP;

        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.dame_cant_lugares_asignados ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_cant_lugares_asignados(idplantilla VARCHAR) RETURNS BIGINT AS
$$
    SELECT count(lugar) FROM lugar_para_actividad WHERE plantilla = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.dame_lista_id_lugares_asignados ------------------------- */ 
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_lugares_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los LUGARES asignados a una plantilla de actividad
--     EJEMPLO: SELECT  dame_lista_id_lugares_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_lugares_asignados(idplantilla VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM lugar_para_actividad WHERE plantilla = idplantilla LOOP
            arr := array_append(arr,r.lugar);
        END LOOP;

        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.dame_lista_id_grupos_de_recursos_asignados ------------------------- */ 

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_grupos_de_recursos_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los grupos de recursos asignados a una plantilla de actividad
--     EJEMPLO: SELECT  dame_lista_id_grupos_de_recursos_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_grupos_de_recursos_asignados(idplantilla VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = idplantilla LOOP
            arr := array_append(arr,r.id);
        END LOOP;

        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.dame_lista_id_otros_recursos_asignados ------------------------- */ 

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_otros_recursos_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los otros de recursos asignados 
--              de un grupo de recursos dado
--     EJEMPLO: SELECT  dame_lista_id_otros_recursos_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_otros_recursos_asignados(idgrupo VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM otro_recurso_para_actividad WHERE grupo_de_recursos = idgrupo LOOP
            arr := array_append(arr,r.otro_recurso);
        END LOOP;

        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.dame_cant_grupos_de_recursos_asignados ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_cant_grupos_de_recursos_asignados(idplantilla VARCHAR) RETURNS BIGINT AS
$$
    SELECT count(id) FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.dame_cant_otros_recursos_asignados ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_cant_otros_recursos_asignados(idgrupo VARCHAR) RETURNS BIGINT AS
$$
    SELECT count(otro_recurso) FROM otro_recurso_para_actividad WHERE grupo_de_recursos = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.dame_lista_id_brigadas_de_esp ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_lista_id_brigadas_de_esp(esp VARCHAR, niv VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM brigada WHERE especialidad = esp AND nivel = niv LOOP
            arr := array_append(arr,r.id);
        END LOOP;

        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ generador.posible_inicio ------------------------- */ 

-- -----------------------------------------------------------------------------------
--      NOMBRE: posible_inicio
--        TIPO: funcion
-- DESCRIPCION: Dice si es posible o no colocar una actividad en el dia turno para una brigada
--     EJEMPLO: SELECT  posible_inicio();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION posible_inicio(idplantilla VARCHAR, dia INTEGER, turno INTEGER, idbrigada VARCHAR, plugares VARCHAR[], pprofesores VARCHAR[]) RETURNS BIGINT AS
$$
    DECLARE c BIGINT;
        i BIGINT;
        j BIGINT;
        k INTEGER;
        t BIGINT;
        v INTEGER;
        duracion INTEGER;
        periodo VARCHAR;
        clasif VARCHAR;
        asignatura VARCHAR;
        profesores VARCHAR[];
        lugares VARCHAR[];
        cantProfes BIGINT;
        cantLugares BIGINT;
        gruporecursos VARCHAR[];
        recursos VARCHAR[];
        cantGrupoRecursos BIGINT;
        cantRecursos BIGINT;
        r record;
        malo BOOLEAN;
        puede BOOLEAN;
        exige_lugar BOOLEAN;
        exige_profesor BOOLEAN;
    BEGIN

        duracion := dame_duracion_plantilla_de_actividad(idplantilla);
        periodo := dame_periodo_plantilla_de_actividad(idplantilla);
        clasif := dame_clasif_plantilla_de_actividad(idplantilla);
        asignatura := dame_asignatura_plantilla_de_actividad(idplantilla);

        exige_lugar := plantilla_exige_lugar(idplantilla);
        exige_profesor := plantilla_exige_profesor(idplantilla);

        IF exige_profesor = TRUE THEN
            IF pprofesores is null THEN
                profesores := dame_lista_id_profes_asignados(idplantilla);
            ELSE
                profesores := pprofesores;
            END IF;
            
            cantProfes := matrix.get_length_of(profesores, 1);
        END IF;
        

        IF exige_lugar = TRUE THEN
            IF plugares is null THEN
                lugares := dame_lista_id_lugares_asignados(idplantilla);    
            ELSE
                lugares := plugares;
            END IF;
            cantLugares := matrix.get_length_of(lugares, 1);
        END IF;
        
        
        i := 0;
        LOOP
            EXIT WHEN i = duracion;
            
            -- comprobando que el periodo no est?estringido
            
            v:= valor_restriccion_periodo(periodo, dia, turno + i);

            IF v > 0 THEN
                RETURN FALSE;
            END IF;

            -- comprobando la disponibilidad de la clasificacion

            v:= valor_restriccion_clasif_act(clasif, dia, turno + i);

            IF v > 0 THEN
                RETURN FALSE;
            END IF;

            -- comprobando la disponibilidad de la asignatura

            v:= valor_restriccion_asignatura(asignatura, dia, turno + i);

            IF v > 0 THEN
                RETURN FALSE;
            END IF;
            
            -- compoobando la disponibilidad de la brigada

            v:= valor_restriccion_brigada(idbrigada, dia, turno + i);

            IF v > 0 THEN
                RETURN FALSE;
            END IF;
            
            i := i + 1;
        END LOOP;


        IF exige_profesor = TRUE THEN
            -- comprobar la disponibilidad de profesores
            
            j := 0;

            k := 0;
            
            LOOP
                EXIT WHEN j = cantProfes;

                puede := TRUE;
                
                i := 0;
                LOOP
                    EXIT WHEN i = duracion;
                                
                    v:= valor_restriccion_profesor(profesores[j], dia, turno + i);

                    IF v > 0 THEN
                        puede := FALSE;
                        EXIT;
                    END IF;
                    i := i + 1;
                END LOOP;

                IF puede = TRUE THEN
                    k := k + 1;
                    EXIT;
                END IF;
                
                j := j + 1;
            END LOOP;

            IF k = 0 THEN
                RETURN FALSE;
            END IF;
        END IF;

        IF exige_lugar = TRUE THEN    
            -- comprobar la disponibilidad de lugares
            j := 0;

            k := 0;
            
            LOOP
                EXIT WHEN j = cantLugares;

                puede := TRUE;
                
                i := 0;
                LOOP
                    EXIT WHEN i = duracion;
                                
                    v := valor_restriccion_lugar(lugares[j], dia, turno + i);

                    IF v > 0 THEN
                        puede := FALSE;
                        EXIT;
                    END IF;
                    i := i + 1;
                END LOOP;

                IF puede = TRUE THEN
                    k := k + 1;
                    EXIT;
                END IF;
                
                j := j + 1;
            END LOOP;

            IF k = 0 THEN
                RETURN FALSE;
            END IF;
        END IF;
        
        -- comprobar la disponibilidad de otros recursos

        gruporecursos := dame_lista_id_grupos_de_recursos_asignados(idplantilla);
        cantGrupoRecursos := matrix.get_length_of(gruporecursos,1);

        i := 0;
        LOOP
            EXIT WHEN i = cantGrupoRecursos;

            recursos := dame_lista_id_otros_recursos_asignados(gruporecursos[i]);
            cantRecursos := matrix_get_length_of(recursos, 1);

            SELECT * INTO r FROM otro_grupo_de_recursos_para_actividad WHERE id = gruporecursos[i];

            -- me preocupo solo por lo imprescindible
            
            IF r.imprescindibles = TRUE THEN
            
                j := 0;
                t := 0; -- va a contar cuantos hay disponibles para luego compararlo contra los necesarios
                
                LOOP

                    EXIT WHEN j = cantRecursos;

                    malo := FALSE;
                    
                    LOOP
                        EXIT WHEN k = duracion;
                        v := valor_restriccion_otro_recurso(recursos[j],dia,turno + k);
                    
                        IF v > 0 THEN 
                            malo := TRUE;
                            EXIT;
                        END IF;
                        
                        k:= k + 1;
                    END LOOP;

                    IF malo = FALSE THEN
                        t := t + 1;
                    END IF;
                    
                    j := j + 1;
                END LOOP;

                -- si t no alcanza los necesarios entonces no se puede,
                -- y solo basta con que uno de los grupos de recursos 
                -- no sea satisfecho para decir que no se puede

                IF t < r.necesarios THEN
                    RETURN FALSE;
                END IF;
            END IF;
            
            i := i + 1;
        END LOOP;

        RETURN TRUE;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.dame_cant_rest_asignatura ------------------------- */ 
/**
 * @name dame_cant_rest_asignatura
 * @desc Obtener la cantidad de restricciones de una asignatura. Se tiene en cuenta las restricciones reales
 * @author Rafael Rodriguez Ramirez
 * @old  SELECT count(valor) FROM restriccion_de_asignatura WHERE asignatura = $1 AND periodo = $2;
 * 
 */
CREATE OR REPLACE FUNCTION dame_cant_rest_asignatura(idasig VARCHAR, idperiodo VARCHAR) RETURNS BIGINT AS
$$
	declare cd integer;
			ct integer;
			c bigint;
			i integer;
			j integer;
			v integer;
	begin
		cd := dame_cant_dias(idperiodo);
		ct := dame_cant_turnos(idperiodo);
		c := 0;
		i:=1;
		LOOP
			j:=1;
			LOOP
				v := valor_restriccion_asignatura(idasig, idperiodo, i,j);
				if v > 0 then
					c := c +1;
				end if;
				EXIT WHEN j>=ct;
				j:=j+1;
			END LOOP;
			EXIT WHEN i>=cd;
			i:=i+1;
		END LOOP;
		
		return c;
	end;
  
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.dame_cant_rest_profe ------------------------- */ 
/**
 * @name dame_cant_rest_profe
 * @desc Obtener la cantidad de restricciones de un profesor. Se tiene en cuenta las restricciones reales
 * @author Rafael Rodriguez Ramirez
 * @old  SELECT count(valor) FROM restriccion_de_profesor WHERE profesor = $1 AND periodo = $2;
 * 
 */
CREATE OR REPLACE FUNCTION dame_cant_rest_profe(idprofe VARCHAR, idperiodo VARCHAR) RETURNS BIGINT AS
$$
	declare cd integer;
			ct integer;
			c bigint;
			i integer;
			j integer;
			v integer;
	begin
		cd := dame_cant_dias(idperiodo);
		ct := dame_cant_turnos(idperiodo);
		c := 0;
		i:=1;
		LOOP
			j:=1;
			LOOP
				v := valor_restriccion_profesor(idprofe, idperiodo, i,j);
				if v > 0 then
					c := c +1;
				end if;
				EXIT WHEN j>=ct;
				j:=j+1;
			END LOOP;
			EXIT WHEN i>=cd;
			i:=i+1;
		END LOOP;
		
		return c;
	end;
  
$$
LANGUAGE 'plpgsql';
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.dame_cant_otros_recursos_imprescindibles ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_cant_otros_recursos_imprescindibles(idplantilla VARCHAR) RETURNS BIGINT AS
$$
    SELECT sum(otro_grupo_de_recursos_para_actividad.necesarios) FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = $1 AND imprescindibles = TRUE;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.dame_total_otros_recursos_asignados ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_total_otros_recursos_asignados(idplantilla VARCHAR) RETURNS NUMERIC AS
$$
    SELECT sum(dame_cant_otros_recursos_asignados(id)) FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.dame_cant_accesos_prohibidos_al_grupo ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_cant_accesos_prohibidos_al_grupo(VARCHAR) RETURNS BIGINT AS
$$
    SELECT count(lugar) FROM acceso_prohibido WHERE grupo = $1;
$$
LANGUAGE 'sql';
/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.dame_especialidad_de_brigada ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_especialidad_de_brigada (idbrigada VARCHAR) RETURNS VARCHAR AS
$$
    SELECT especialidad FROM brigada WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.dame_nivel_de_brigada ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_nivel_de_brigada (idbrigada VARCHAR) RETURNS VARCHAR AS
$$
    SELECT nivel FROM brigada WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.dame_clasificacion_de_grupo ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_clasificacion_de_grupo (idgrupo VARCHAR) RETURNS VARCHAR AS
$$
    SELECT clasificacion FROM grupo WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.dame_grupos ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_grupos(idesp VARCHAR, idnivel VARCHAR, idclasif VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r RECORD;
    BEGIN
        FOR r IN SELECT grupo FROM brigada_de_grupo WHERE dame_especialidad_de_brigada(brigada) = idesp AND dame_nivel_de_brigada(brigada) = idnivel AND dame_clasificaicon_de_grupo(grupo) = idclasif GROUP BY grupo LOOP
            arr := array_append(arr,r.grupo);
        END LOOP;
        RETURN arr;
    END;
    
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.dame_matricula ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_matricula(brg VARCHAR) RETURNS INTEGER AS
$$
    SELECT matricula FROM brigada WHERE id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.dame_matricula_total ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_matricula_total(brigadas VARCHAR[]) RETURNS BIGINT AS
$$
    DECLARE i BIGINT;
        total BIGINT;
        cantBrg BIGINT;
    BEGIN
        
        total := 0;
        cantBrg := matrix.get_length_of(brigadas, 1);

        i := 1;
        LOOP
            total := total + dame_matricula(brigadas[i]);
            EXIT WHEN i = cantBrg;
            i := i+ 1;
        END LOOP;

        RETURN total;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.dame_capacidad ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_capacidad(lug VARCHAR) RETURNS INTEGER AS
$$
    SELECT capacidad FROM lugar WHERE id = $1;
$$
LANGUAGE 'sql';
/* --------------------------------------------------------- */ 
/* ------------------------------ generador.quita_segun_prohibidos ------------------------- */ 

CREATE OR REPLACE FUNCTION quita_segun_prohibidos(grupos VARCHAR[], lugares VARCHAR[]) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r RECORD;
        cantLug BIGINT; cantGrupos BIGINT;
        i BIGINT; j BIGINT;
        puede BOOLEAN;
    BEGIN
        
        cantLug := matrix.get_length_of(lugares);
        cantGrupos := matrix.get_length_of(grupos);
        
        i := 1;
        LOOP
            puede := TRUE;

            j := 1;

            LOOP
                IF es_lugar_prohibido (grupos[j], lugares[i]) THEN
                    puede := FALSE;
                    EXIT;
                END IF;
                
                EXIT WHEN j = cantGrupos;
                j := j + 1;
            END LOOP;

            IF puede = TRUE THEN
                arr := array_append(arr, lugares[i]);
            END IF;
            
            EXIT WHEN i = lugares;
            i := i + 1;
        END LOOP;
        
        RETURN arr;
    END;
    
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ generador.quita_segun_capacidad ------------------------- */ 
CREATE OR REPLACE FUNCTION quita_segun_capacidad(brigadas VARCHAR[], lugares VARCHAR[]) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR[];
        r RECORD;
        cantLug BIGINT;
        i BIGINT;
        matricula BIGINT;
        capacidad INTEGER;
    BEGIN
        matricula := dame_matricula_total(brigadas);
        cantLug := matrix.get_length_of(lugares);

        i := 1;
        LOOP
            capacidad := dame_capacidad(lugares[i]); 

            -- si la capacidad es 0 significa que no importa o que es infinita

            IF capacidad = 0 OR capacidad >= matricula THEN
                arr := array_append(arr, lugares[i]);
            END IF;
            
            EXIT WHEN i = lugares;
            i := i + 1;
        END LOOP;
        
        RETURN arr;
    END;
    
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.es_lugar_prohibido ------------------------- */ 
CREATE OR REPLACE FUNCTION es_lugar_prohibido(pgrupo VARCHAR, plugar VARCHAR) RETURNS BOOLEAN AS
$$
    DECLARE r record;
    BEGIN
        SELECT * INTO r FROM acceso_prohibido WHERE grupo = pgrupo AND lugar = plugar;
        IF r.grupo = pgrupo THEN
            RETURN TRUE;
        END IF;
        RETURN FALSE;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.dame_grupos_de_brigadas ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_grupos_de_brigadas(brigadas VARCHAR[], idplantilla VARCHAR) RETURNS VARCHAR[] AS
$$
    DECLARE arr VARCHAR;
        cantBrg BIGINT;
        i BIGINT;
        g VARCHAR;
        clasif VARCHAR;
    BEGIN 
        cantBrg := matrix.get_length_of(brigadas,1);
        clasif := dame_clasif_plantilla_de_actividad(idplantilla);

        i := 1;
        LOOP
            g := dame_grupo(brigadas[i], clasif);

            IF g IS NOT NULL THEN
                IF matrix.get_pos_of(arr, g) <> -1 THEN
                    arr := array_append(arr, g);
                END IF;
            END IF;
            EXIT WHEN i = cantBrg;
            i := i + 1;
        END LOOP;
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.dame_grupo ------------------------- */ 
CREATE OR REPLACE FUNCTION dame_grupo(brg VARCHAR, clasif VARCHAR ) RETURNS VARCHAR AS
$$
    SELECT grupo FROM brigada_de_grupo WHERE brigada = $1 AND dame_clasificacion_de_grupo(grupo) = $2;
$$
LANGUAGE 'sql';
/* --------------------------------------------------------- */ 
/* ------------------------------ generador.and_mpi ------------------------- */ 
CREATE OR REPLACE FUNCTION and_mpi(listaidbrg VARCHAR[], cd INTEGER, ct INTEGER, idplantilla VARCHAR, zona BIGINT, excepto BOOLEAN) RETURNS BOOLEAN[][] AS
$$
    DECLARE mpi BOOLEAN[][];
        r RECORD;
        i INTEGER;
        j INTEGER;
        k INTEGER;
        cantBrg BIGINT;
        pi BOOLEAN;
        lugares VARCHAR[];
        cantLugares BIGINT;
        profesores VARCHAR[];
        cantProfes BIGINT;
        grupos VARCHAR[];
    BEGIN

        cantBrg := matrix.get_length_of(listaidbrg, 1);

        -- obtengo los lugares y profesores asignados a la plantilla 
        lugares := dame_lista_id_lugares_asignados(idplantilla);
        cantLugares := matrix.get_length_of(lugares, 1);

        profesores := dame_lista_id_profes_asignados(idplantilla);
        cantProfes := matrix.get_length_of(profesores, 1);

        -- obtengo los grupos involucrados segun la plantilla y las brigadas
        -- generalmente debe ser uno solo

        grupos := dame_grupos_de_brigadas (listaidbrg, idplantilla);
        
        -- creo la mpi
        mpi := matrix.create_boolean_matrix(cd,ct);

        -- inicializo la mpi con TRUE en todas las casillas
        i := 1;

        LOOP
            j := 1;
            LOOP
                mpi[i][j] := TRUE;
                EXIT WHEN j = ct;
                j := j + 1;
            END LOOP;
            
            EXIT WHEN i = cd;
            i := i + 1;
        END LOOP;

        
        -- recorro cada brigada comprobando la disponibilidad en cada turno
        k:=1;
        LOOP
            -- verifico para cada turno si es posible inicio
            i := 1;
            LOOP
                j := 1;
                LOOP
                    pi := posible_inicio(idplantilla, i, j, listaidbrg[k], lugares, profesores);

                    IF excepto = TRUE THEN
                        SELECT * INTO r FROM actividad WHERE plantilla = idplantilla AND dia = i AND turno = j;
                        IF r.plantilla = idplantilla THEN
                            pi = TRUE;
                        END IF;
                    END IF;

                    -- me quedo con los lugares que tengan capacidad
                    lugares := quita_segun_capacidad(listaidbrg, lugares);
                    
                    -- me quedo con los lugares que no est?prohibidos

                    lugares := quita_segun_prohibidos(grupos, lugares);

                    IF matrix.get_length_of(lugares) = 0 THEN
                        pi := FALSE;
                    END IF;

                    mpi[i][j] := pi;

                    EXIT WHEN j = ct;
                    j := j + 1;
                END LOOP;
                
                EXIT WHEN i = cd;
                i := i + 1;
            END LOOP;

            EXIT WHEN k = cantBrg;
            k := k + 1;
        END LOOP;
        
        RETURN mpi;
    END;
    
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ generador.TRango ------------------------- */ 
DROP TYPE IF EXISTS TRango CASCADE;
CREATE TYPE TRango AS (
    ini INTEGER,
    fin INTEGER
);/* --------------------------------------------------------- */ 
/* ------------------------------ generador.rango ------------------------- */ 
CREATE OR REPLACE FUNCTION rango(idplantilla VARCHAR, idgrupo VARCHAR, minimo INTEGER, maximo INTEGER) RETURNS TRango AS
$$    
    DECLARE ran TRango;
        i INTEGER; 
        rplantilla record;
        rperiodo record;
        rdesglose record;
        n INTEGER;
        arr INTEGER[];
        CD INTEGER;
        r record;
        w record;
        c INTEGER;
        da INTEGER; db INTEGER; dif INTEGER;
    BEGIN

        SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = idplantilla;
        SELECT * INTO rdesglose FROM desglose WHERE id = rplantilla.desglose;
        SELECT * INTO rperiodo FROM periodo WHERE id = rdesglose.periodo;

        ran.ini = 1;
        CD := rperiodo.cant_dias;
        ran.fin = CD;
        n := rplantilla.orden;
        
        IF rdesglose.respetar_orden = FALSE THEN
            RETURN ran;
        END IF;
        
        arr := matrix.create_numeric_list(CD);

        FOR r IN SELECT * FROM actividad WHERE grupo = idgrupo AND dame_desglose_de_plantilla(plantilla) = rdesglose.id LOOP
            SELECT * INTO w FROM plantilla_de_actividad WHERE id = r.plantilla;
            arr[r.dia] := w.orden;
        END LOOP;

        c := matrix.get_count_of_value_in(arr, 0);

        -- SI c = 0 ninguna actividad, el rango es todo el periodo

        IF c = 1 THEN
            -- una actividad
            i := 1;
            LOOP
                IF arr[i]<>0 THEN
                    IF arr[i] < n THEN
                        ran.ini = i + min;
                        ran.fin = CD;
                    ELSE
                        ran.ini = 1;
                        ran.fin = i - min;
                    END IF;
                    
                    EXIT;
                END IF;
                EXIT WHEN i = CD;
                i := i + 1;
            END LOOP;
        END IF;

        IF c > 1 THEN
            -- mas de una actividad

            da := 0;
            db := CD + 1;

            i := 1;
            LOOP
                IF arr[i] = n - 1 AND arr[i] <> 0 THEN
                    da = i;
                    EXIT;
                END IF;
                EXIT WHEN i = CD;            
                i := i + 1;
            END LOOP;


            i := 1;
            LOOP
                IF arr[i] = n + 1 AND arr[i] <> 0 THEN
                    db = i;
                    EXIT;
                END IF;
                EXIT WHEN i = CD;            
                i := i + 1;
            END LOOP;            

            dif := db - da;

            ran.ini := da + min;

            IF dif <= max * 2 THEN
                ran.fin = db - min;
            ELSE
                ran.fin = da + max;
            END IF;
        END IF;

        RETURN ran;
    
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.zpriori.dame_zpriori ------------------------- */ 

CREATE OR REPLACE FUNCTION dame_zpriori(clasif VARCHAR, idperiodo VARCHAR) RETURNS INTEGER[][] AS
$$
    DECLARE zp INTEGER[][];
        i BIGINT; j BIGINT;
        CD INTEGER; CT INTEGER;
        r record;
    BEGIN 
        
        CD := dame_cant_dias(idperiodo);
        CT := dame_cant_turnos(idperiodo);
        
        zp := matrix.create_boolean_matrix(CD,CT);
    
        FOR r IN SELECT * FROM zpriori WHERE clasificacion = clasif AND periodo = idperiodo LOOP
            zp[r.dia][r.turno] := r.valor;
        END LOOP;
        
        RETURN zp;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_desglose_de_plantilla ------------------------- */ 

CREATE OR REPLACE FUNCTION dame_desglose_de_plantilla (idplantilla VARCHAR ) RETURNS VARCHAR as
$$
    SELECT desglose FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';
/* --------------------------------------------------------- */ 
/* ------------------------------ generador.dia_minimo_impacto ------------------------- */ 
CREATE OR REPLACE FUNCTION dia_minimo_impacto(idclasif VARCHAR, idesp VARCHAR, idnivel VARCHAR, idperiodo VARCHAR, dia_ini INTEGER, dia_fin INTEGER) RETURNS INTEGER AS
$$
    DECLARE
        i BIGINT; j BIGINT; k BIGINT; l BIGINT; ll BIGINT; dia INTEGER;
        max BIGINT;
        cant BIGINT;
        r record;
        CD INTEGER;
        CT INTEGER;
        rperiodo RECORD;
        cantBrg BIGINT;
        maxzpriori INTEGER;
        zp INTEGER[][];
        impactodiario INTEGER[];
        min bigint;
        impacto bigint;
    BEGIN
 
        zp := dame_zpriori(idclasif, idperiodo);

        SELECT * INTO r FROM zpriori WHERE clasificacion = idclasif AND periodo = idperiodo ORDER BY valor DESC;

        maxzpriori := r.valor;
            
        SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;

        CD := r.cant_dias;
        CT := r.cant_turnos;
        
        SELECT count(id) as c INTO r FROM brigada WHERE especialidad = idesp AND nivel = idnivel;

        cantBrg := r.c;

        i := dia_ini;
        LOOP
            j := 1;
            LOOP
                zp[i][j] := maxzpriori - zp[i][j];
                EXIT WHEN j = CT;
                j := j + 1;
            END LOOP;
            
            EXIT WHEN i = dia_fin;
            i := i + 1;
        END LOOP;


        -- En los turnos restringidos el impacto es cero tanto para el grupoXClasif en curso
        -- como para los dem?
        -- Poniendo cero seg??estricciones de la clasificacion de actividad
        FOR r IN SELECT * FROM restriccion_de_clasif_act WHERE clasificacion = idclasif AND periodo = idperiodo LOOP
            IF r.valor > 0 THEN 
                zp[r.dia][r.turno] := 0;
            END IF;
        END LOOP;
    
        -- Poniendo cero seg??estricciones de las brigadas
        FOR r IN SELECT * FROM restriccion_de_brigada WHERE dame_especialidad_de_brigada(brigada) = idesp AND dame_nivel_de_brigada(nivel) = idnivel LOOP
            IF r.valor > 0 THEN 
                zp[r.dia][r.turno] := 0;
            END IF;
        END LOOP;

        -- Poniendo cero seg??estricciones del periodo
        FOR r IN SELECT * FROM restriccion_de_periodo WHERE periodo = idperiodo LOOP
            IF r.valor > 0 THEN 
                zp[r.dia][r.turno] := 0;
            END IF;
        END LOOP;

        -- calculando impacto diario y minimizando
        
        i := dia_ini;
        min := -1;
        dia := 0;
        LOOP
            impacto := 0;
            j := 1;
            LOOP
                impacto := impacto + zp[i][j];
                EXIT WHEN j = CT;
                j := j + 1;
            END LOOP;

            -- puede que haya otros minimos, pero me quedo con el primero porque est?as cerca
            -- del origen del periodo (recordar que se colocan actividades por nivel)
            
            IF min = -1 OR min > impacto THEN
                min := impacto;
                dia := i;
            END IF;
            
            EXIT WHEN i = dia_fin;
            i := i + 1;
        END LOOP;

        RETURN dia;
    END;
$$
LANGUAGE 'plpgsql';
/* --------------------------------------------------------- */ 
/* ------------------------------ generador.lugar_permanece_en ------------------------- */ 
CREATE OR REPLACE FUNCTION lugar_permanece_en(plugar VARCHAR, pperiodo VARCHAR, pdia INTEGER, pturno INTEGER, duracion INTEGER) RETURNS BOOLEAN AS
$$
    BEGIN
        RETURN EXISTS(SELECT * FROM restriccion_de_lugar WHERE lugar = plugar AND periodo = pperiodo AND dia = pdia AND turno >= pturno AND turno <= pturno + duracion - 1);
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ generador.extraer_lugares_que_no_permanecen ------------------------- */ 
CREATE OR REPLACE FUNCTION extraer_lugares_que_no_permanecen (lugares VARCHAR[], periodo VARCHAR, dia INTEGER, turno INTEGER, duracion INTEGER) RETURNS VARCHAR[] AS
$$
    DECLARE
        arr VARCHAR[];
        cant BIGINT;
        i BIGINT;
        
    BEGIN
        cant := matrix.get_length_Of(lugares,1);
        
        i := 1;
        LOOP
            IF lugar_permanece_en(lugares[i],periodo,dia,turno,duracion) THEN
                arr := array_append(arr, lugares[i]);
            END IF;
            EXIT WHEN i = cant;
            i := i + 1;
        END LOOP;
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ generador.dame_lugares_utilizados_en_el_dia ------------------------- */ 

CREATE OR REPLACE FUNCTION dame_lugares_utilizados_en_el_dia(pgrupo VARCHAR, periodo VARCHAR, pdia INTEGER) RETURNS VARCHAR[] AS
$$
    DECLARE
        arr VARCHAR[];
        r record;
        
    BEGIN
        FOR r IN SELECT * FROM actividades WHERE grupo = pgrupo AND dia = pdia AND dame_periodo_de_plantilla(plantilla) = periodo LOOP
            arr := array_append (arr,r.lugar);
        END LOOP;
        
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ generador.dame_gasto_lugares_en_el_dia ------------------------- */ 

CREATE OR REPLACE FUNCTION dame_gasto_lugares_en_el_dia(lugares VARCHAR[], periodo VARCHAR, pdia INTEGER) RETURNS BIGINT[] AS
$$
    DECLARE
        arr BIGINT[];
        u BIGINT;
        i BIGINT;
        cant BIGINT;
    BEGIN
        cant := matrix.get_length_of(lugares, 1);

        arr := matrix.create_numeric_list(cant);
        
        i := 1;
        LOOP            
            SELECT * FROM actividad WHERE dia = pdia AND lugar = lugares[i] AND dame_periodo_de_plantilla(plantilla) = periodo GROUP BY lugar;

            IF NOT FOUND THEN
                u:=0;
            ELSE
                GET DIAGNOSTICS u := ROW_COUNT;
            END IF;

            arr[i] := u;
            
            EXIT WHEN i = cant;
            i:= i + 1;
        END LOOP;
    
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ generador.busca_lugar_mas_utilizado_por ------------------------- */ 


CREATE OR REPLACE FUNCTION busca_lugar_mas_utilizado_por(lugares VARCHAR[], idgrupo VARCHAR, los_demas BOOLEAN) RETURNS VARCHAR as
$$
    DECLARE i BIGINT;
        cant BIGINT;
        m INTEGER;
        cantr INTEGER;
        pos BIGINT;
        r record;
    BEGIN
        cant := matrix.get_length_of(lugares, 1);

        m := 0;
        pos := 0;
        
        i := 1;
        LOOP
            SELECT * INTO r FROM actividades WHERE lugar = lugares[i] AND ((grupo = idgrupo AND los_demas = FALSE) OR (grupo <> idgrupo AND los_demas = TRUE)) ;

            IF NOT FOUND THEN
                cantr :=0;
            ELSE
                GET DIAGNOSTICS cantr = ROW_COUNT;
            END IF;

            IF cantr > m OR i = 1 THEN
                m := cantr;
                pos := i;
            END IF;
            
            EXIT WHEN i = cant;
            i := i + 1;
        END LOOP;

        -- al meno uno de los lugares debe ser utilizado
        IF m = 0 THEN 
            RETURN '';
        END IF;
        RETURN lugares[pos];
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ generador.busca_lugar_menos_utilizado_por ------------------------- */ 

-- SELECT busca_lugar_menos_utilizado(array['lug33','lug2','lug3'],'grupo1');
CREATE OR REPLACE FUNCTION busca_lugar_menos_utilizado_por(lugares VARCHAR[], idgrupo VARCHAR, los_demas BOOLEAN) RETURNS VARCHAR as
$$
    DECLARE i BIGINT;
        cant BIGINT;
        m INTEGER;
        cantr INTEGER;
        pos BIGINT;
        r record;
    BEGIN
        cant := matrix.get_length_of(lugares, 1);

        m := 0;
        pos := 0;
        
        i := 1;
        
        LOOP
            SELECT * INTO r FROM actividades WHERE lugar = lugares[i] AND ((grupo = idgrupo AND los_demas = FALSE) OR (grupo <> idgrupo AND los_demas = TRUE)) ;

            IF NOT FOUND THEN
                cantr :=0;
            ELSE
                GET DIAGNOSTICS cantr = ROW_COUNT;
            END IF;

            IF cantr <m OR i = 1 THEN
                m := cantr;
                pos := i;
            END IF;
            
            EXIT WHEN i = cant;
            i := i + 1;
        END LOOP;

        -- al meno uno de los lugares debe ser utilizado
        IF m = 0 THEN 
            RETURN '';
        END IF;
        
        RETURN lugares[pos];
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ generador.busca_lugar_menos_costoso ------------------------- */ 



--
-- Devuelve el id del lugar seg??rigenes[] que es menos costoso de llegar a los destinos[]
--
/*

INSERT INTO lugar (id) VALUES ('201'),('202'),('203'),('204');
INSERT INTO costo_de_traslado (origen,destino,costo) VALUES 
    ('201','202',10),
    ('202','201',10),
    ('201','203',10),
    ('203','201',10),
    ('201','204',5),
    ('204','201',5),
    ('202','203',20),
    ('203','202',20),
    ('202','204',15),
    ('204','202',15),
    ('203','204',6),
    ('204','203',6);
*/

-- SELECT busca_lugar_menos_costoso(array['203','202'],array['201','202','203']);
CREATE OR REPLACE FUNCTION busca_lugar_menos_costoso(origenes VARCHAR[], destinos VARCHAR[]) RETURNS VARCHAR as
$$
    DECLARE i BIGINT; j BIGINT;
        min BIGINT; pos BIGINT;
        cant_origenes BIGINT; 
        cant_destinos BIGINT;
        costo BIGINT;
        r record;
    BEGIN
        cant_origenes := matrix.get_length_of(origenes, 1);
        cant_destinos := matrix.get_length_of(destinos, 1);
    
        min := 0;
        pos := -1;
        
        i := 1;
        LOOP
            costo := 0;
            j := 1;
            LOOP
                SELECT * INTO r FROM costo_de_traslado WHERE origen = origenes[i] AND destino = destinos[j];
                
                costo := costo + r.costo;
                
                EXIT WHEN j = cant_destinos;
                j := j + 1;
            END LOOP;

            IF min > costo OR pos = -1 THEN
                min = costo;
                pos = i;
            END IF;
            
            EXIT WHEN i = cant_origenes;
            i := i + 1;
        END LOOP;

        IF pos = -1 THEN
            RETURN '';
        END IF;
        
        RETURN origenes[pos];
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ generador.dame_lugares_usados_alrededor_de ------------------------- */ 


CREATE OR REPLACE FUNCTION dame_lugares_usados_alrededor_de(brigadas VARCHAR[], idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS VARCHAR[] as
$$
    DECLARE arr VARCHAR[];
        cantbrg BIGINT;
        i BIGINT;
        j INTEGER;
        rperiodo record;
        r record;
    BEGIN
        SELECT * INTO rperiodo FROM periodo WHERE id = idperiodio;
        
        cantbrg := matrix.get_length_of(brigadas,1);

        i := 0;
        LOOP
            -- buscando el anterior
            j := pturno - 1;
            LOOP
                SELECT * INTO r FROM actividad WHERE idperiodo = dame_periodo_plantilla_de_actividad(plantilla) AND dia = pdia AND turno = j AND grupo = dame_grupo(brigdas[i], dame_clasif_plantilla_de_actividad(plantilla));

                IF FOUND THEN
                    arr := array_append(arr, r.lugar);
                    EXIT;
                END IF;
                
                EXIT WHEN j = 1;
                j :=j - 1;
            END LOOP;

            -- buscando el siguiente
            j := pturno + 1;
            LOOP
                SELECT * INTO r FROM actividad WHERE idperiodo = dame_periodo_plantilla_de_actividad(plantilla) AND dia = pdia AND turno = j AND grupo = dame_grupo(brigdas[i], dame_clasif_plantilla_de_actividad(plantilla));

                IF FOUND THEN
                    arr := array_append(arr, r.lugar);
                    EXIT;
                END IF;
                
                EXIT WHEN j = rperiodo.cant_turnos;
                j := j + 1;
            END LOOP;
            
            EXIT WHEN i = cantbrg;
            i := i + 1;
        END LOOP;
        
        RETURN arr;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ generador.dame_costo_trasladar_recurso ------------------------- */ 

-- SELECT dame_costo_trasladar_recurso('r1','l1','si',1,2);
CREATE OR REPLACE FUNCTION dame_costo_trasladar_recurso(idrecurso VARCHAR, idlugar VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS BIGINT as
$$
    DECLARE i BIGINT; j BIGINT;
        costo BIGINT;
        rperiodo record;
        r record;
        id VARCHAR;
    BEGIN

        SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;

        costo := 0;

        IF rperiodo.cant_turnos > 0 THEN
            
            -- busco actividad anterior
            i := pturno - 1;
            LOOP
                SELECT actividad.lugar as lugar INTO r FROM otros_recursos_usados INNER JOIN actividad ON actividad.id = otros_recursos_usados.actividad WHERE actividad.dia = pdia AND actividad.turno = i AND dame_periodo_plantilla_de_actividad(actividad.plantilla) = idperiodo AND otros_recursos_usados.otro_recurso = idrecurso;

                IF FOUND THEN
                    id := r.lugar;
                    SELECT costo INTO r FROM costo_de_traslado WHERE origen = id AND destino = idlugar;
                    IF FOUND THEN
                        costo := r.costo;
                    END IF;
                END IF;

                EXIT WHEN i = 1;
                i := i -1;
            END LOOP;

            
            -- busco actividad siguiente
            i := pturno + 1;
            LOOP
                SELECT actividad.lugar as lugar INTO r FROM otros_recursos_usados INNER JOIN actividad ON actividad.id = otros_recursos_usados.actividad WHERE actividad.dia = pdia AND actividad.turno = i AND dame_periodo_plantilla_de_actividad(actividad.plantilla) = idperiodo AND otros_recursos_usados.otro_recurso = idrecurso;

                IF FOUND THEN
                    id := r.lugar;
                    SELECT costo INTO r FROM costo_de_traslado WHERE destino = id AND origen = idlugar;
                    IF FOUND THEN
                        costo := costo + r.costo;
                    END IF;
                END IF;

                EXIT WHEN i = rperiodo.cant_turnos;
                i := i + 1;
            END LOOP;
        END IF;
        RETURN costo;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ generador.agrega_actividad ------------------------- */ 

CREATE OR REPLACE FUNCTION agrega_actividad(idplantilla VARCHAR, idprofesor VARCHAR, idlugar VARCHAR, pdia INTEGER, pturno INTEGER, idgurpo VARCHAR, idrecursos VARCHAR[], momento DOUBLE PRECISION, pcreador VARCHAR) RETURNS void as
$$
    DECLARE pid VARCHAR;
        listaidbrg VARCHAR[];
        cant BIGINT;
        i BIGINT; j INTEGER;
        rplantilla record;
        r record;
    BEGIN

        SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = idplantilla;

        pid := crear_id_de_replica ('ancoragoh','actividad','id','actividad');
        INSERT INTO actividad (id, plantilla, profesor, lugar, dia, turno, momento_de_colocacion, creador, grupo) VALUES (pid, idplantilla, idprofesor, idlugar, pdia, pturno, momento, pcreador, idgrupo);

        cant := matrix.get_length_of(idrecursos);
        
        i := 1;
        LOOP
            INSERT INTO otros_recursos_usados (otro_recurso, actividad) VALUES (idrecursos[i], id);
            EXIT WHEN i = cant;
            i := i + 1;
        END LOOP;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.valor_rest_entre_config_distintas ------------------------- */ 

-- ----------------------------------------------------------------------------------------------
-- Devuelve si en un (dia,turno) de un per?o, est?estringido en una plantilla del per?o
-- ----------------------------------------------------------------------------------------------
-- El problema se da puesto que ambos periodos pueden tener configuraciones de tiempo distintas
-- y por lo tanto no se puede no corresponder el turno de un periodo con el turno de su plantilla
-- La solucion consiste partiendo del tiempo nominal del per1, llevarlo a su tiempo real, y luego
-- este llevarlo al tiempo nominal de per2 y ver si est? no restringido.
/*
-- Por ejemplo:
-- Periodo 1: 5 dias x 6 turnos, hora inicial 08:00:00 duraci??1:30:00

    Turno   Hora inicio del turno
    -----------------------------
    1    08:00:00 hrs
    2    09:30:00 hrs
    3    11:00:00 hrs
    4    13:30:00 hrs
    5    15:00:00 hrs
    6    16:30:00 hrs
    -----------------------------
    fin    17:59:59 hrs
    
-- Periodo 2(plantilla): 7 dias x 7 turnos, hora inicial 09:00:00 duraci??0:45:00

    Turno   Hora inicio del turno
    -----------------------------
    1    09:00:00 hrs
    2    09:45:00 hrs
    3    10:30:00 hrs
    4    11:15:00 hrs
    5    12:00:00 hrs
    6    12:45:00 hrs
    7    13:30:00 hrs
    -----------------------------
    fin    14:14:59 hrs

-- Encuestar dia 2 turno 2

-- El turno 2 del d?2 del Per?o 1 es de 11:00:00 hrs hasta 13:29:59 hrs
-- Entonces llevando a tiempo nominal del Per?o 2 ser? los turnos 3,4,5,6 del d?2
   pues las 11:00:00 hrs est?n el turno 3 del Per?o 2 y 13:29:59 hrs est?n el turno 6

   Para saber en que turno cae una hora se utiliza la formula

   turno := (hora - hora_inicial) / duracion + 1;

   donde hora_inicial es la hora de comienzo de la jornada en el per?o y duracion es 
   la duraci??el turno.
     

*/

CREATE OR REPLACE FUNCTION valor_rest_entre_config_distintas(rests INTEGER[][], per1 periodo, per2 periodo, dia INTEGER, turno INTEGER) RETURNS INTEGER as
$$
    DECLARE hi DOUBLE PRECISION; hf DOUBLE PRECISION;
        ti INTEGER; tf INTEGER;
        i INTEGER;
    BEGIN
        hi := (turno-1) * per1.duracion_del_turno;
        hf := hi + per1.duracion_del_turno;

        ti := (hi - per2.hora_inicial) / per2.duracion_del_turno + 1;
        tf := (hf - per2.hora_inicial) / per2.duracion_del_turno + 1;

        i := ti;
        LOOP
            IF rests[dia][i] < 1 THEN
                RETURN rests[dia][i];
            END IF;
            EXIT WHEN i = tf;
            i := i + 1;
        END LOOP;
        RETURN 0;
    END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ generador.generar ------------------------- */ 

-- -----------------------------------------------------------------------------------
--      NOMBRE: generar
--        TIPO: funcion
-- DESCRIPCION: Genera el horario de una especialidad, nivel y periodo dados
--     EJEMPLO: SELECT  dame_cant_clasif();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION generar(idesp VARCHAR, idnivel VARCHAR, idperiodo VARCHAR, forma BIGINT, parche BIGINT, compactar BIGINT, parche1 BOOLEAN, buscalugar BOOLEAN) RETURNS void as
$$
    DECLARE
        cantAsigEnCurso INTEGER;
        cantActEnCurso INTEGER;
        cantCriterios INTEGER;
        cantMaxAct INTEGER;
        matrixAct VARCHAR[][];
        plantillasEnCurso VARCHAR[];
        asigEnCurso VARCHAR[];
        cantActs BIGINT[];
        temp VARCHAR[];
        idprofe VARCHAR;
        
        kk INTEGER;
        idxOrdenAct INTEGER[];

        -- contadores
        i BIGINT;
        j BIGINT;
        k BIGINT;
        l BIGINT;
        ll BIGINT;
        r RECORD;
        dia INTEGER;
        turno INTEGER;
        
        voyact BIGINT;
        cantCiclo1 BIGINT;
        cantCiclo2 BIGINT;
        varCiclo1 BIGINT;
        varCiclo2 BIGINT;
        listaid_clasif VARCHAR[];
        cantClasif BIGINT;
        nivel_actual BIGINT;
        clasif_actual BIGINT;
        TablaPrioriza NUMERIC[][];
        cantPriorizando BIGINT;
        CD INTEGER;
        CT INTEGER;

        -- parametros
        profesAsignados VARCHAR[];
        lugaresAsignados VARCHAR[];
        param_cantProfesAsignados BIGINT;
        param_cantLugaresAsignados BIGINT;
        param_cantRestProfe BIGINT;
        param_cantRestLugar BIGINT;
        idxAsigEnPriori BIGINT[];
        param_cantActAsig BIGINT;
        param_cantRestAsig BIGINT;
        param_cantRecursosImprescindibles BIGINT;
        param_cantRecursosAsignados BIGINT;
        niveles NUMERIC[][];
        criterios BOOLEAN[];

        -- colocando actividad
        grupos VARCHAR[];
        cantGrupos BIGINT;
        faltan BIGINT;
        rasignatura record;
        rdesglose record;
        rplantilla record;
        rperiodo record;
        rclasificacion record;
        ztodo INTEGER;
        fatan INTEGER;
        gruposEnCurso BOOLEAN[][][];
        listaidbrg VARCHAR[];
        grupompi BOOLEAN[][];
        idMaxPIGrupo BIGINT[][];
        maxColoca BIGINT;
        diaBooleano BOOLEAN[];
        posibXDia BIGINT[];

        ya BOOLEAN[];
        zp INTEGER[][];

        -- para la optimizacion profesor
        actual_profe VARCHAR[];
        actual_profe_cant BIGINT[][];
        actual_profe_priori BIGINT[][];
        actual_profe_grupos BIGINT[][];
        actual_profe_cgrupos BIGINT[];
        
        -- cada profesor tiene un rango posible de colocacion de la actividad distinto
        -- para cada grupoXclasif
        
        actual_profe_rango INTEGER[][][]; -- profe i, grupo j, actual_profe[i][j][1] = ini actual_profe[i][j][2] = fin
        actual_profe_misact BOOLEAN[][][];

        miDiaSegunProfe INTEGER[];
        
        g BIGINT;
        min INTEGER;
        gmin BIGINT;
        c INTEGER;
        tt INTEGER;
        xasig BOOLEAN[];

        cantActualProfe BIGINT;

        rangobusqueda TRango;

        temp_rango TRango;
        grupos_rangos INTEGER[][];
        actual_profe_gastos INTEGER[];

        idClasifSgte VARCHAR;
        dia_anterior INTEGER;
        esta_colocada BOOLEAN;

        sentido INTEGER;
        ini_sentido INTEGER;
        fin_sentido INTEGER;
        step_sentido INTEGER;
        zona INTEGER;

        etapa INTEGER;
        maxDia INTEGER;
        CicloIni INTEGER;
        CicloFin INTEGER;
        
        puedo BOOLEAN;
        t INTEGER;

        idlugar BIGINT;
        pos BIGINT;
        lugs VARCHAR[];
        
        lugs_priori BIGINT[];
        cantLugs BIGINT;
        lgi VARCHAR;
        
        grupo_anterior BIGINT;

        mejor_profe BIGINT;
        minim BIGINT;

        dias_buenos_profe INTEGER[];
        -- brgsEnCurso VARCHAR[];
        -- cantBrgsEnCurso BIGINT;

        lugares VARCHAR[];
        cantLugares BIGINT;
        maxpriori BIGINT;

        lugsarround VARCHAR[];
        idrecursos VARCHAR[];
        gruporecursos VARCHAR[];
        cantGrupoRecursos BIGINT;
        gr BIGINT;
        rgruporecurso record;

        cantListaIdBrg BIGINT;

    ih INTEGER;
    es_higienico BOOLEAN;
	
    BEGIN

        cantCriterios := 9;
        cantActEnCurso := 0;

        -- brgsEnCurso := dame_lista_id_brigadas_de_esp(idesp, idnivel);
        -- cantBrgsEnCurso := matrix.get_length_of(brgsEnCurso);
                
        -- aqui se obtienen los ids de las asignaturas implicadas en la generaci??       -- ordenadas seg??a cantidad de actividades que tienen en el per?o
        -- especificado "idperiodo"
        
        asigEnCurso := dame_asig_segun_esp_anno_per(idesp, idnivel, idperiodo);

        cantAsigEnCurso := matrix.get_length_of(asigEnCurso,1 );
        
        CD := dame_cant_dias(idperiodo);
        CT := dame_cant_turnos(idperiodo);
        
        SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;
        CD := rperiodo.cant_dias;
        CT := rperiodo.cant_turnos;

        cantMaxAct := max_cant_act(idesp, idnivel, idperiodo);
        matrixAct := new_string_matrix(cantAsigEnCurso, cantMaxAct);
                
        -- preparando la matrix de actividades
        i := 1;
        LOOP
            j := 1;
            
            k := dame_cant_act(asigEnCurso[i],idperiodo);

            cantActs[i] := k;
            
            cantActEnCurso := cantActEnCurso + k;

            temp := dame_clasif_de_asignaturas(asigEnCurso[i],idperiodo);
            LOOP
                matrixAct[i][j] := temp[j];
                EXIT WHEN j = cantActs[i];
                j := j + 1;
            END LOOP;
            EXIT WHEN i = cantAsigEnCurso;
            i := i + 1;
        END LOOP;
        

        -- hasta este momento se ha obtenido lo siguiente

        /*
         i  asigEnCurso[i]  cantActs[i]  matrixAct[i]
         -----------------------------------------
         1  matematica      3            {"conf","conf","cp"}
         2  fisica          2            {"conf","cp"}
         3  ingles          1            {"conf"} 
        */
        
        voyact := 0;

        listaid_clasif := dame_lista_id_clasif();
        cantClasif := matrix.get_length_of(listaid_clasif, 1);
        
        IF forma = 1 THEN
            cantCiclo2 := cantMaxAct;
            cantCiclo1 := cantClasif;
        ELSE
            cantCiclo1 := cantMaxAct;
            cantCiclo2 := cantClasif;        
        END IF;

        
        -- !!! GENERANDO !!!
               
        LOOP
            varCiclo1 := 1;
            LOOP
                varCiclo2 :=1;
                LOOP

                    IF forma = 1 THEN
                         nivel_actual := varCiclo2;
                        clasif_actual := varCiclo1;
                    ELSE
                         nivel_actual := varCiclo1;
                        clasif_actual := varCiclo2;    
                    END IF;


                    -- calcular la cantidad de actividades a priorizar

                    cantPriorizando :=0;
                    i := 1;
                    plantillasEnCurso := '{}'::VARCHAR;
                    idxAsigEnPriori := '{}'::BIGINT;
                    LOOP
                        IF matrixAct[i][nivel_actual] = listaid_clasif[clasif_actual] THEN
                            cantPriorizando := cantPriorizando + 1;
                            plantillasEnCurso := array_append(plantillasEnCurso,dame_id_plantilla(asigEnCurso[i], idperiodo, nivel_actual));
                            idxAsigEnPriori := array_append(idxAsigEnPriori,i);
                        END IF;
                        EXIT WHEN i = cantAsigEnCurso;
                        i := i + 1;
                    END LOOP;

                    -- Llenando la tabla prioriza
                    TablaPrioriza := matrix.create_numeric_matrix(cantPriorizando, 9);

                    i := 1;
                    LOOP
                        -- Cantidad de profesores asignados
                        param_cantProfesAsignados := dame_cant_profes_asignados(plantillasEnCurso[i]);
                        
                        -- Cantidad de restricciones de los profesores asignados
                        profesAsignados := dame_lista_id_profes_asignados(plantillasEnCurso[i]);
                        param_cantRestProfe := 0;

                        j := 0;
                        LOOP
                            EXIT WHEN j = param_cantProfesAsignados;
                            param_cantRestProfe := param_cantRestProfe + dame_cant_rest_profe(profesAsignados[j], idperiodo);
                            j := j + 1;
                        END LOOP;

                        -- Cantiad de lugares asignados
                        param_cantLugaresAsignados := dame_cant_lugares_asignados(idplantilla);

                        -- Cantidad de restricciones de los profesores asignados
                        lugaresAsignados := dame_lista_id_lugares_asignados(plantillasEnCurso[i]);
                        param_cantRestLugar := 0;

                        j := 0;
                        LOOP
                            EXIT WHEN j = param_cantLugaresAsignados;
                            param_cantRestLugar := param_cantRestLugar + dame_cant_rest_profe(lugaresAsignados[j], idperiodo);
                            j := j + 1;
                        END LOOP;

                        -- Cantidad de actividades de la asignatura
                        param_cantActAsig := cantActs[idxAsigEnPriori[i]];
                        
                        -- Cantidad de otros recursos imprescindibles
                        param_cantRecursosImprescindibles := dame_cant_otros_recursos_imprescindibles(idplantilla);
                        
                        -- Cantidad de otros recursos asignados
                        param_cantRecursosAsignados := dame_total_otros_recursos_asignados(idplantilla);
                        
                        -- Cantidad de restricciones de la asignatura
                        param_cantRestAsig := dame_cant_rest_asignatura(asigEnCurso[idxAsigEnPriori[i]]);

                        -- Rellenando la tabla
                        
                        TablaPrioriza[i][1] := param_cantProfesAsignados;          
                        TablaPrioriza[i][2] := param_cantRestProfe;
                        TablaPrioriza[i][3] := param_cantLugaresAsignados;
                        TablaPrioriza[i][4] := param_cantRestLugar;
                        TablaPrioriza[i][5] := param_cantActAsig;
                        TablaPrioriza[i][6] := param_cantRecursosImprescindibles;
                        TablaPrioriza[i][7] := param_cantRecursosAsignados;
                        TablaPrioriza[i][8] := param_cantRestAsig;

                        EXIT WHEN i = cantPriorizando;
                        i := i + 1;
                    END LOOP;

                    -- Priorizando la actividad a colocar
                    criterios := '{t,f,t,f,f,f,t,f}'::BOOLEAN;
                    niveles:= prioriza(TablaPrioriza, cantPriorizando, 8, criterios);

                    -- calculando totales horizontales
                    -- nota: los totales se colocan en la ??ma columna de la TablaPrioriza
                    i := 1;
                    LOOP
                        k := 0; -- va a llevar la suma
                        j := 1;
                        LOOP
                            k := k + niveles[i][j];
                            EXIT WHEN j=8;
                            j := j + 1;
                        END LOOP;
                        -- La ??ma columna puede variar si se agregan nuevos par?tros
                        TablaPrioriza[i][9] := k;
                        EXIT WHEN i = cantPriorizando;
                        i := i + 1;
                    END LOOP;
                    
                    -- Determino el orden de prioridad para colocar las actividades que entraron en juego
                    -- El orden se determina seg??os totales horizontales calculados, de menor a mayor

                    -- Inicializo el arreglo que va a tener el orden
                    
                    idxOrdenAct := '{}'::INTEGER;

                    -- Al principio idxOrdenAct[i] = i
                    i := 1;
                    LOOP
                        idxOrdenAct := array_append(idxOrdenAct,i);
                        EXIT WHEN i = cantPriorizando;
                        i := i + 1;
                    END LOOP;

                    -- Ordeno la novena columna
                    i := 1;
                    LOOP
                        EXIT WHEN i = cantPriorizando;

                        j := i + 1;

                        LOOP
                            IF TablaPrioriza[idxOrdenAct[i]][9] > TablaPrioriza[idxOrdenAct[j]][9] THEN
                                -- Intercambio
                                                               k := TablaPrioriza[idxOrdenAct[j]][9];
                                TablaPrioriza[idxOrdenAct[j]][9] := TablaPrioriza[idxOrdenAct[i]][9];
                                TablaPrioriza[idxOrdenAct[i]][9] := k;

                                             k := idxOrdenAct[j];
                                idxOrdenAct[j] := idxOrdenAct[i];
                                idxOrdenAct[i] := k;
                            END IF;
                            
                            EXIT WHEN j = cantPriorizando;
                            j := j + 1;
                        END LOOP;
                        
                        i := i + 1;
                    END LOOP;

                    -- Calculando la clasificacion de actividad siguiente
                    
                    idClasifSgte := '';

                    i := clasif_actual + 1;
                    LOOP
                        EXIT WHEN i > cantClasif;
                        
                        j :=1;
                        LOOP
                            IF matrixAct[j][nivel_actual] = listaid_clasif[i] THEN
                                idClasifSgte := listaid_clasif[i];
                                i := cantclasif;
                                EXIT;
                            END IF;
                            EXIT WHEN j = cantAsigEnCurso;
                            j := j + 1;
                        END LOOP;
                        
                        i := i + 1;
                    END LOOP;

                    
                    -- -------------------------------------------
                    -- Colocando la actividad 

                    SELECT * INTO rclasificacion FROM clasificacion_de_actividad WHERE id = listaid_clasif[clasif_actual];
                    SELECT valor INTO r FROM zpriori WHERE clasificacion = listaid_clasif[clasif_actual] AND periodo = idperiodo ORDER By valor DESC LIMIT 1 OFFSET 0;

            ztodo := r.valor;
            
                    posibXDia := matrix.create_numeric_list(CD);

                    zp := dame_zpriori(rclasificacion.id, idperiodo);

                    IF ztodo IS NULL THEN
                        ztodo := 1;
                    END IF;

                    i := 1;
                                        
                    LOOP
                        
                        
                        SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = plantillasEnCurso[idxOrdenAct[i]];
                        SELECT * INTO rdesglose FROM desglose WHERE id = rplantilla.desglose;
                        SELECT * INTO rasignatura FROM asignatura WHERE id = rdesglose.asignatura;

			maxDia := CD -((cantAct[fila] - nivel_actual) - compactar) * IIf(rdesglose.respetar_orden, 1, 0);

                        sentido := 2;
                        dia_anterior := 0;

                        grupos := dame_grupos(idesp, idnivel, rplantilla.clasificion);
                        
                        cantGrupos := matrix.get_length_of(grupos,1);

                        idMaxPIGrupo := matrix.create_numeric_matrix(cantGrupos,CD);
                        
                        faltan := cantGrupos;

                        gruposEnCurso := matrix.create_boolean_cube(cantGrupos, CD, CT);

                        j := 1;
                        LOOP
                            listaidbrg := dame_lista_id_brigadas_de_grupo(grupos[j]);
                            
                            grupompi := and_mpi(listaidbrg,CD,CT,rplantilla.id,0,TRUE);

                            gruposEnCurso := matrix.set_matrix_in_cube(gruposEnCurso, grupompi, j);
                            
                            grupompi := and_mpi(listaidbrg,CD,CT,rplantilla.id,0,FALSE);

                            k := 1;

                            LOOP
                                diaBooleano := matrix.extract_booolean_column(grupompi, k);
                                maxColoca := matrix.get_max_colocation(diaBooleano, rclasificacion.duracion);
                                idMaxPIGrupo[j][k] := maxColoca;

                                IF posibXDia[k] > maxColoca or k = 1 THEN
                                    posibXDia[k] = maxColoca;
                                END IF;
                                
                                EXIT WHEN k = CD;
                                k := k + 1;
                            END LOOP;
                            
                            EXIT WHEN j = cantGrupos;
                            j := j + 1;
                        END LOOP;
                        


                        -- calculo de los posibles turnos optimos en zonapriori

                        IF parche1 = TRUE THEN
                            -- busco los turnos optimos de los grupos por dias

                            j := 1;
                            LOOP
                                -- calculo las posibilidades para todos los grupos

                                -- busco el m?mo
                                ya := matrix.create_boolean_list(cantGrupos);

                                k := 1;
                                LOOP
                                    
                                    l := 1;
                                    LOOP
                                        min := CT + 1;
                                        gmin := 0;

                                        -- busco el grupo con el minimo de posibilidades optimas (en zpriori = 1)

                                        g := 1;
                                        LOOP
                                            c := 0;
                                            IF ya[g] = FALSE THEN

                                                t:=1;
                                                LOOP 
                                                    IF gruposEnCurso[g][j][t] = TRUE and zp[j][t] = 1 THEN
                                                        c := c + 1;
                                                    END IF;
                                                    
                                                    EXIT WHEN t = CT;
                                                    t := t + 1;
                                                END LOOP;

                                                IF min > c THEN
                                                    min := c;
                                                    gmin := g;
                                                END IF;
                                            END IF;
                                            
                                            EXIT WHEN g = cantGrupos;
                                            g := g + 1;
                                        END LOOP;

                                        -- busco la primera posibilidad para el grupo encontrado

                                        t := 1;
                                        LOOP 
                                            IF gruposEnCurso[gmin][j][t] = TRUE and zp[j][t] = 1 THEN
                                                idMaxPIGrupo[gmin][j] := t;
                                                ya[gmin] = TRUE;

                                                -- deshabilito para todos los grupos, el tiempo ocupado por el grupo encontrado
                                                -- para que los dem?turnos ??mos no se solapen con los encontrados
                                                g := 1;
                                                LOOP
                                                    tt := 1;
                                                    LOOP

                                                        gruposEnCurso[g][t + tt - 1] = FALSE;
                                                        
                                                        EXIT WHEN tt = rclasificacion.duracion;
                                                        tt := tt + 1;
                                                    END LOOP;
                                                    
                                                    EXIT WHEN g = cantGrupos;
                                                    g := g + 1;
                                                END LOOP;
                                                
                                                t := CT;
                                            END IF;

                                            EXIT WHEN t = CT;
                                            t := t + 1;
                                        END LOOP;
                                        

                                        EXIT WHEN l = cantGrupos;
                                        l := l + 1;
                                    END LOOP;


                                    EXIT WHEN k = cantGrupos;
                                    k := k + 1;
                                END LOOP;
                                
                                EXIT WHEN j = CD;
                                j := j + 1;
                            END LOOP;

                            -- actualizo posibXDias
                            
                            j := 1;
                            LOOP
                                posibXDia[j] := 0;

                                g := 1;
                                
                                LOOP
                                    IF idMaxPIGrupo[g][j] <> 0 THEN
                                        posibXDia[j] := posibXDia[j] + 1;
                                    END IF;
                                    
                                    EXIT WHEN g = cantGrupos;
                                    g := g + 1;
                                END LOOP;
                                
                                EXIT WHEN j = CD;
                                j := j + 1;
                            END LOOP;

                        END IF;

                        xasig := matrix.create_boolean_list(CT);

                        -- empieza la colocaci??                       
                        grupo_anterior := 0; -- verificar que el grupo_anterior puede ser el mismo que j
                        
                        j := 1;
                        LOOP

                            listaidbrg := dame_lista_id_brigadas_de_grupo(grupos[j]);

                            cantListaIdBrg := matrix.get_length_of(listaidbrg, 1);
                            
                            grupompi := and_mpi(listaidbrg,CD,CT,rplantilla.id,0,FALSE);

                            -- calculo el rango de b??eda
                            
                            rangobusqueda := rango(idplantilla, grupos[j], rdesglose.min_dias, rdesglose.max_dias);

               IF rplantilla.exige_profesor THEN
                            -- ----------------------------------------------------------------------------
                            -- OPTIMIZACION DEL PROFESOR
                            -- ----------------------------------------------------------------------------
                                -- Obtengo los profesores asignados al grupo actual para la actividad actual

                                actual_profe := dame_lista_id_profes_asignados_al_grupo(idplantilla, grupos[j]);
                                
                                cantActualProfe := matrix.get_length_of(actual_profe);

                                actual_profe_cant := matrix.create_numeric_matrix(cantActualProfe,CD);

                                -- cantidad de turnos ocupados en actividades por dia
                                -- se calcula el gasto total en el periodo para cada profesor

                                l := 1;
                                LOOP
                                    actual_profe_gastos[l] := 0;
                                    FOR r IN SELECT * FROM actividad WHERE dame_periodo_de_desglose(dame_desglose_de_plantilla(plantilla)) = rperiodo.id AND profesor = actual_profe[l] LOOP
                                        k := dame_duracion_clasif_de_actividad(dame_clasif_plantilla_de_actividad(idplantilla));
                                        actual_profe_cant[l][r.dia] := actual_profe_cant[l][r.dia] + k;
                                        actual_profe_gastos[l] := actual_profe_gastos[l] + k;
                                    END LOOP;

                                    EXIT WHEN l = cantActualProfe;
                                    l := l + 1;
                                END LOOP;
                                
                                -- si hay mas de un profesor se selecciona el mejor con el m?do prioriza 
                                -- dado los criterios:
                                --      gasto (min)
                                --      prioridad segun el seguridad.usuario (min)
                                -- pero como la lista de profesores ya viene ordenada por prioridad
                                -- entonces basta con buscar el primer profesor con el minimo gasto
                                
                                mejor_profe = 1;
                                minim := -1;
                                IF cantActualProfe > 1 THEN
                                    l := 1;
                                    LOOP
                                        IF minim = -1 OR minim > actual_profe_gastos[l] THEN
                                            minim := actual_profe_gastos[l];
                                            mejor_profe := l;
                                        END IF;
                                        
                                        EXIT WHEN l = cantActualProfe;
                                        l := l + 1;
                                    END LOOP;
                                    
                                END IF;
                                
                                -- una vez obtenido el mejor profesor, entonces se crea una lista
                                -- con los mejores dias para el profesor en el rango posible de colocacion
                                -- el dia optimo esta dado por el dia de mas gasto
                                -- pues la idea es minimizar la cantidad de dias que debe venir
                                -- el profesor a dar clases (este parametro puede ser configurable)
                                                                
                                dias_buenos_profe := matrix.create_numeric_list(CD);
                                -- la principio es consecutivo
                                l := 1;
                                LOOP
                                    dias_buenos_profe[l] := l;
                                    EXIT WHEN l = CD;
                                    l := l + 1;
                                END LOOP;
                                
                                -- luego ordeno en el rango, de mayo a menor
                                
                                l := rangobusqueda.ini;
                                LOOP
                                    k := l + 1;
                                    LOOP
                                        IF actual_profe_cant[mejor_profe][l] < actual_profe_cant[mejor_profe][k]  THEN
                                            t := actual_profe_cant[mejor_profe][l];
                                            actual_profe_cant[mejor_profe][l] := actual_profe_cant[mejor_profe][k];
                                            actual_profe_cant[mejor_profe][k] := t;
                                            
                                            t:= dias_buenos_profe[l];
                                            dias_buenos_profe[l] := dias_buenos_profe[k];
                                            dias_buenos_profe[k] := t;
                                        END IF;
                                                                                
                                        EXIT WHEN k = rangobusqueda.fin;
                                        k := k + 1;
                                    END LOOP;
                                    EXIT WHEN l = rangobusqueda.fin - 1;
                                    l := l +1;
                                END LOOP;
                                
                            -- ----------------------------------------------------------------------------
                            END IF;
                            
                            IF j = 1 AND idClasifSgte <> '' AND idClasifSgte <> rclasificacion.id THEN
                                IF parche = FALSE OR dia_anterior <= (CD / 2) -1 THEN
                                    dia_anterior := dia_minimo_impacto (idClasifSgte, idesp, idnivel, rperiodo.id, grupos_rangos[j][1], grupos_rangos[j][2]);
                                END IF;
                            END IF;
                            
                            -- verificando que la actividad no est?signada
                            SELECT count(id) as cant INTO r FROM actividad WHERE plantilla = rplantilla.id AND grupo = grupos[j];

                            esta_colocada := FALSE;
                            IF r.cant > 0 THEN
                                esta_colocada := TRUE;
                            END IF;

                            IF esta_colocada = FALSE THEN

                                -- actualiza sentido para mayor combinacion

                                IF sentido = 2 THEN
                                    sentido := 1;
                                    ini_sentido := 1;
                                    fin_sentido := CT;
                                    step_sentido := 1;
                                ELSE
                                    sentido = 2;
                                    ini_sentido := CT;
                                    fin_sentido := 1;
                                    step_sentido := -1;
                                END IF;


                                /*
                                'BUSQUEDA POR ZONAS
                                    'Polietapas
                                    'zona:
                                    '1-Zona priori
                                    '2-Zona todo
                                    'ciclo por cada etapa
                                    '1- Mismo Dia
                                    '2- Posib>=faltan
                                    '3- Cualquier dia
                                */

                                zona := 1;
                                LOOP
                                
                                    etapa := 1;
                                    LOOP
                                        -- preparo el rango de busqueda por dia
                                        IF etapa = 1 And dia_anterior > rangobusqueda.ini THEN
                                            CicloIni := dia_anterior;
                                            CicloFin := dia_anterior;
                                        ELSE
                                            CicloIni := rangobusqueda.ini;
                                            CicloFin := rangobusqueda.fin;

                                            IF CicloFin > maxDia THEN 
                        CicloFin := maxDia;
                            END IF;
                                        END IF;

                                        kk := CicloIni;
                                        LOOP
                        k := kk;

                        idprofe = NULL;

                        es_higienico := TRUE;
                        
                        IF rplantilla.exige_profesor = TRUE THEN
                            -- optimizando al profesor
                            k := dias_buenos_profe[kk];
                            idprofe := actual_profe[mejor_profe];
                                
                            -- higiene para profesor
                            IF es_higienico_para_profesor(idprofe, idplantilla, k) = FALSE THEN
                            es_higienico := FALSE;
                            END IF;
                        END IF;

                        CONTINUE WHEN es_higienico = FALSE;

                        -- ----------------------
                        -- higiene para brigadas
                        
                        ih := 1;
                        LOOP
                            IF es_higienico_para_brigada(listaidbrg[ih], idplantilla, k) = FALSE THEN
                            es_higienico := FALSE;
                            END IF;
                            
                            EXIT WHEN ih >= cantListaIdBrg;
                        ih := ih + 1;
                        END LOOP;

                        CONTINUE WHEN es_higienico = FALSE;

                                            -- ----------------------
                                            
                                            puedo := TRUE;
                                            
                                            IF rdesglose.respetar_orden = FALSE AND rdesglose.mismo_dia = FALSE THEN
                                            
                                                SELECT * INTO r FROM actividades WHERE grupo = grupos[j] AND dame_desglose_de_plantilla(plantilla) = rdesglose.id;

                                                IF r.grupo = grupos[j] THEN
                                                    puedo := FALSE;
                                                END IF;

                                                IF k <> 0 AND puedo = TRUE THEN

                                                    IF posibXDia[k] >= faltan OR etapa = 3 THEN

                                                        t := ini_sentido;
                                                        LOOP

                                                            IF parche1 = TRUE THEN
                                                                IF etapa = 1 AND idMaxPIGrupo[j][k] <> 0 THEN
                                                                    IF grupompi[k][idMaxPiGrupo[j][k]] = TRUE AND zp[k][idMaxPiGrupo[j][k]] = zona THEN
                                                                        t := idMaxPiGrupo[j][k];
                                                                    END IF;
                                                                END IF;
                                                            END IF;
                                                            
                                                            CONTINUE WHEN grupompi[k][t] = TRUE AND etapa = 1 AND xasig[t] = TRUE;
                                                            
                                                            IF grupompi[k][t] = TRUE THEN
                                                                IF etapa = 2 AND dia_anterior = 0 THEN
                                                                    xasig[t] = TRUE;
                                                                END IF;
                                                                
                                                            END IF;

                                                            IF rplantilla.exige_lugar = TRUE THEN
                                                            -- asignando lugar
                                                            
                                                                idlugar := 1;
                                                                
                                                                -- preparando...
                                                                    lugares := dame_lista_id_lugares_asignados(idplantilla);
                                                                    
                                                                    cantLugares := matrix.get_length_of(lugares, 1);
                                                                    
                                                                    -- extraer los que no permanecen en el turno
                                                                    
                                                                    lugares := extraer_lugares_que_no_permanecen(lugares, rperiodo.id, k, t, rplantilla.duracion);
                                                                
                                                                    -- extraer los lugares que no tienen capacidad
                                                                    
                                                                    lugares := quita_segun_capacidad(listaidbrg, lugares);
                                                                    
                                                                    -- extraer los lugares prohibidos
                                                                
                                                                    lugares := quita_segun_prohibidos(('{' || quote_literal(grupos[j]) || '}')::VARCHAR[], lugares);
                                                                
                                                                -- colocando ...   
                                                                    -- tratar de colocar en el aula fija
                                                                    IF EXISTS(SELECT * FROM lugar_fijo WHERE grupo = grupos[j]) THEN
                                                                        SELECT * INTO r FROM lugar_fijo WHERE grupo = grupos[j];
                                                                        pos := matrix.get_pos(lugares, r.lugar);
                                                                        IF pos > -1 THEN
                                                                            idlugar := pos;
                                                                        END IF;
                                                                    END IF;
                                                                    
                                                                    -- buscar el mejor lugar
                                                                    
                                                                        -- buscar segun prioridades
                                                                                                                                                
                                                                        IF pos = -1 THEN
                                                                        
                                                                            -- obtengo la maxima prioridad dada a los lugares para la plantilla de actividad actual
                                                                            
                                                                            SELECT * INTO r FROM lugar_para_actividad WHERE plantilla = rplantilla.id ORDER BY priodidad DESC LIMIT 1 OFFSET 0;
                                                                           
                                                                            maxpriori := r.prioridad;
                                                                            
                                                                            -- tanto para la busqueda por el lugar mas utilizado como para el mas cercano
                                                                            -- se busca segun las prioridades dadas por el seguridad.usuario a los lugares 
                                                                            
                                                                            -- obtengo la prioridad de cada lugar
                                                                            
                                                                            lugs_priori := matrix.create_numeric_list(cantLugares);
                                                                            
                                                                            ll := 1;
                                                                            LOOP
                                                                                SELECT * INTO r FROM lugar_para_actividad WHERE lugar = lugares[ll] AND plantilla = rplantilla.id;
                                                                                
                                                                                lugs_priori[ll] := r.prioridad;
                                                                                
                                                                                EXIT WHEN ll = cantLugares;
                                                                                ll := ll + 1;
                                                                            END LOOP;
                                                                            
                                                                            -- comienza la colocacion
                                                                            l := 1;
                                                                            LOOP
                                                                                -- seleccionar los lugares que tienen la prioridad actual
                                                                                
                                                                                lugs := '{}'::VARCHAR[];
                                                                                
                                                                                ll := 1;
                                                                                LOOP
                                                                                    IF lugs_priori[ll] = l THEN
                                                                                        lugs := array_append(lugs, lugares[ll]);
                                                                                    END IF;
                                                                                    
                                                                                    EXIT WHEN ll = cantLugares;
                                                                                    ll := ll + 1;
                                                                                END LOOP;
                                                                                
                                                                                cantLugs := matrix.get_length_of(lugs);
                                                                                
                                                                                idlugar := '';
                                                                                
                                                                                IF cantLugs > 0 THEN
                                                                                    -- asignando...
                                                                                    -- TODO: pensar en una variante conjunta entre el mas usado y mas cercano
                                                                                    IF buscalugar = FALSE THEN
                                                                                        -- el mas utilizado por el grupo actual
                                                                                        idlugar := busca_lugar_mas_utilizado_por(lugs, grupos[j], FALSE);
                                                                                        
                                                                                        IF idlugar = '' THEN
                                                                                            -- el menos utilizado por los demas
                                                                                            idlugar := busca_lugar_menos_utilizado_por(lugs, grupos[j], TRUE);
                                                                                            
                                                                                            IF idlugar = '' THEN
                                                                                                -- se coge el primero
                                                                                                idlugar := lugs[1];
                                                                                            END IF;
                                                                                        END IF;
                                                                                    ELSE
                                                                                        -- ... el mas cercano
                                                                                        lugsarround := dame_lugares_usados_alrededor_de(listaidbrg, rperiodo.id, k, t);
                                                                                        
                                                                                        idlugar := busca_lugar_menos_costoso(lugs, lugsarraound);
                                                                                        
                                                                                    END IF;
                                                                                END IF;

                                                                                EXIT WHEN l = maxpriori OR idlugar <> '';
                                                                                l := l + 1;
                                                                            END LOOP;
                                                                        END IF;
                                                                END IF;   
                                                                -- asignando otros recursos
                                                                
                                                                idrecursos := '{}'::VARCHAR[];
                                                                
                                                                -- comprobar la disponibilidad de otros recursos

                                                                gruporecursos := dame_lista_id_grupos_de_recursos_asignados(idplantilla);
                                                                cantGrupoRecursos := matrix.get_length_of(gruporecursos,1);

                                                                gr := 0;
                                                                LOOP
                                                                    EXIT WHEN gr = cantGrupoRecursos;
                                                                    
                                                                    SELECT * INTO rgruporecurso FROM otro_grupo_de_recursos_para_actividad WHERE id = gruporecursos[i];
                                                                    
                                                                    l := 0;
                                                                    FOR r IN SELECT *, dame_uso_de_otro_recurso(otro_recurso) as uso, dame_costo_trasladar_recurso(otro_recurso, idlugar, rperiodo.id, k, t) as costo FROM otro_recurso_para_actividad WHERE grupo_de_recursos = gruporecursos[gr] ORDER BY prioridad, uso, costo LOOP
                                                                        idrecursos := array_append(idrecursos, r.otro_recurso);
                                                                        l := l + 1;
                                                                        EXIT WHEN l = rgruporecurso.necesarios;
                                                                    END LOOP;
                                                                    
                                                                    EXIT WHEN gr = cantGrupoRecursos;
                                                                    gr := gr + 1;
                                                                END LOOP;
                                                                
                                                                -- agregando la actividad
                                                                IF rplantilla.exige_lugar = FALSE THEN
                                    idlugar = NULL;
                                END IF;                        
                                
                                                                SELECT agrega_actividad(rplantilla.id, idprofe, idlugar, k, t, grupos[j], idrecursos, tiempo.moment_now(), creador);
                                                                
                                                                -- --------------------------------------------
                                                            EXIT WHEN t = fin_sentido;
                                                            t := t + step_sentido;
                                                        END LOOP;
                                                    END IF;
            
                                                END IF;
                                                
                                            END IF;

                                            EXIT WHEN kk = CicloFin;
                                            kk := kk + 1;
                                        END LOOP;
                                        
                                        EXIT WHEN etapa = 3;
                                        etapa := etapa + 1;
                                    END LOOP;

                                    EXIT WHEN zona = ztodo.valor;
                                    zona := zona + 1;
                                END LOOP;
                            END IF;

                            EXIT WHEN j = cantGrupos;
                            
                            grupo_anterior := j;
                            
                            j := j + 1;
                        END LOOP;
                        
                        -- --------------------------------------------
                        EXIT WHEN i = cantPriorizando;
                        i := i + 1;
                    END LOOP;

                    EXIT WHEN varCiclo2 = cantCiclo2;
                    varCiclo2 := varCiclo2 + 1;
                END LOOP;
                EXIT WHEN varCiclo1 = cantCiclo1;
                varCiclo1 := varCiclo1 + 1;
            END LOOP;
            
            EXIT WHEN voyact = cantActEnCurso;
        END LOOP;
        
    END;
$$
LANGUAGE 'plpgsql'; /* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_max_orden_act ------------------------- */ 
create or replace function dame_max_orden_act(iddesglose varchar) returns integer as
$$ 
	select max(orden) from plantilla_de_actividad where desglose = $1;
$$
language 'sql';
/* --------------------------------------------------------- */ 
/* ------------------------------ disparadores.nuevo_desglose_cuando_nuevo_periodo ------------------------- */ 
﻿CREATE OR REPLACE FUNCTION nuevo_desglose_cuando_nuevo_periodo() returns trigger as
$$
	DECLARE r record;
	    pcreador varchar;
	begin
		pcreador := dame_creador_de_planificacion(dame_planificacion_de_periodo(NEW.id));

		FOR r IN SELECT * FROM asignatura WHERE creador = pcreador LOOP
			INSERT INTO desglose (asignatura, periodo) VALUES (r.id, NEW.id);
		END LOOP;
		
		return NEW;
	end;
$$
language 'plpgsql';

CREATE TRIGGER nuevo_desglose_si_nuevo_periodo
  AFTER INSERT
  ON periodo
  FOR EACH ROW
  EXECUTE PROCEDURE nuevo_desglose_cuando_nuevo_periodo();/* --------------------------------------------------------- */ 
/* ------------------------------ disparadores.nuevo_desglose_cuando_nueva_asignatura ------------------------- */ 
﻿CREATE OR REPLACE FUNCTION nuevo_desglose_cuando_nueva_asignatura() returns trigger as
$$
	DECLARE r record;
	    pcreador varchar;
	begin
		
		FOR r IN SELECT * FROM periodo WHERE dame_creador_de_planificacion(dame_planificacion_de_periodo(id)) = NEW.creador LOOP
			INSERT INTO desglose (asignatura, periodo) VALUES (NEW.id, r.id);
		END LOOP;
		
		return NEW;
	end;
$$
language 'plpgsql';

CREATE TRIGGER nuevo_desglose_si_nueva_asignatura
  AFTER INSERT
  ON asignatura
  FOR EACH ROW
  EXECUTE PROCEDURE nuevo_desglose_cuando_nueva_asignatura();/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.dame_cant_act_desglose ------------------------- */ 
-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_act_desglose
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de actividades de un desglose
--     EJEMPLO: SELECT dame_cant_act_desglose('iddesglose');
-- -----------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION dame_cant_act_desglose(desglose varchar) RETURNS BIGINT as
$$
    SELECT count(plantilla_de_actividad.id)
    FROM desglose 
    INNER JOIN plantilla_de_actividad 
        ON plantilla_de_actividad.desglose = desglose.id
    WHERE desglose.id = $1;
$$
LANGUAGE 'sql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.actualiza_orden_plantillas ------------------------- */ 
create or replace function actualiza_orden_plantillas(iddesglose varchar) returns integer
as 
$$
	declare r record;
		c bigint;
		ids varchar[];
		i bigint;
	begin
		c := dame_cant_act_desglose(iddesglose);
		ids := matrix.create_string_list(c);
		
		i := 0;

		for r in select * from plantilla_de_actividad where desglose = iddesglose order by orden loop
		  i:=i+1;
		  ids[i] := r.id;
		end loop;
		
	        i := 0;
	        loop
		  i := i + 1;
		  update plantilla_de_actividad set orden = i, actualizacion_interna = true where id = ids[i] AND orden <> i;
		  update plantilla_de_actividad set actualizacion_interna = false where id = ids[i];
		  exit when i >= c;
		end loop;
		
		return c;
	end;
$$
language 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ disparadores.actualiza_orden_act_after_delete ------------------------- */ 
create or replace function actualiza_orden_act_after_delete() returns trigger as
$$
	declare r integer;
	begin

		r := actualiza_orden_plantillas(OLD.desglose);
		return OLD;
	end;
$$
language 'plpgsql';

create trigger trigger_actualiza_orden_act_after_delete after delete on plantilla_de_actividad
for each row
execute procedure actualiza_orden_act_after_delete();/* --------------------------------------------------------- */ 
/* ------------------------------ disparadores.actualiza_orden_act_after_insert ------------------------- */ 
create or replace function actualiza_orden_act_after_insert() returns trigger as
$$
	declare r integer;
	
	begin
		r := actualiza_orden_plantillas(NEW.desglose);
		
		return NEW;
	end;
$$
language 'plpgsql';

create trigger trigger_actualiza_orden_act_after_insert after insert on plantilla_de_actividad
for each row
execute procedure actualiza_orden_act_after_insert();/* --------------------------------------------------------- */ 
/* ------------------------------ disparadores.actualiza_orden_act_after_update ------------------------- */ 
create or replace function actualiza_orden_act_after_update() returns trigger as
$$
	declare r integer;
	begin

		IF NEW.actualizacion_interna = FALSE THEN
			IF OLD.orden <> NEW.orden THEN
				r := actualiza_orden_plantillas(NEW.desglose);
			END IF;
		END IF;
		
		return NEW;
	end;
$$
language 'plpgsql';

create trigger trigger_actualiza_orden_act_after_update after update on plantilla_de_actividad
for each row
execute procedure actualiza_orden_act_after_update();
/* --------------------------------------------------------- */ 
/* ------------------------------ disparadores.actualiza_orden_act_before_insert ------------------------- */ 
create or replace function actualiza_orden_act_before_insert() returns trigger as
$$
	declare r integer;
		rr record;
	begin
		SELECT * into rr from plantilla_de_actividad WHERE desglose = NEW.desglose AND orden = NEW.orden;

		IF FOUND THEN
			NEW.orden := dame_max_orden_act(NEW.desglose) + 1;
		END IF;
		
		return NEW;
	end;
$$
language 'plpgsql';

create trigger trigger_actualiza_orden_act_before_insert before insert on plantilla_de_actividad
for each row
execute procedure actualiza_orden_act_before_insert();/* --------------------------------------------------------- */ 
/* ------------------------------ disparadores.actualiza_orden_act_before_update ------------------------- */ 
create or replace function actualiza_orden_act_before_update() returns trigger as
$$
	declare r integer;
		rr record;
	begin
		IF NEW.actualizacion_interna = FALSE THEN
			IF (OLD.orden <> NEW.orden AND OLD.desglose = NEW.desglose) OR OLD.desglose <> NEW.desglose THEN
			
				SELECT * INTO rr FROM plantilla_de_actividad WHERE desglose = NEW.desglose AND orden = NEW.orden;

				IF FOUND THEN
					IF OLD.desglose = NEW.desglose THEN
						NEW.orden := OLD.orden;
					ELSE
						NEW.orden := dame_max_orden_act(NEW.desglose) + 1;
					END IF;
				END IF;
				
			END IF;
		END IF;
		
		return NEW;
	end;
$$
language 'plpgsql';

create trigger trigger_actualiza_orden_act_before_update before update on plantilla_de_actividad
for each row
execute procedure actualiza_orden_act_before_update();
/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.baja_orden_de_plantilla ------------------------- */ 
create or replace function baja_orden_de_plantilla(idplantilla varchar) returns boolean as
$$
	declare r record;
		rplantilla1 record;
		rplantilla2 record;
		m integer;
		o1 integer;
		o2 integer;
		
	begin
		
		select * into rplantilla1 from plantilla_de_actividad where id = idplantilla;

		-- asegurando que este en orden las plantillas, de paso devuelve la cantidad y max
		
		m := actualiza_orden_plantillas(rplantilla1.desglose);

		
		if found then
			if rplantilla1.orden < m then

				-- obtengo la plantilla de abajo
				select * into rplantilla2 from plantilla_de_actividad where desglose = rplantilla1.desglose and orden = rplantilla1.orden + 1;

				-- hago el intercambio

				o1 := rplantilla1.orden;
				o2 := rplantilla2.orden;
				
				update plantilla_de_actividad set orden = m + 1, actualizacion_interna = true where id = rplantilla1.id;
				update plantilla_de_actividad set orden = o1, actualizacion_interna = true where id = rplantilla2.id;
				update plantilla_de_actividad set orden = o2, actualizacion_interna = true where id = rplantilla1.id;

				-- quitando actualizacion interna
				update plantilla_de_actividad set actualizacion_interna = false where id = rplantilla1.id or id = rplantilla2.id;
			end if;
			return true;
		end if;
		return false;
	end;
$$
language 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.sube_orden_de_plantilla ------------------------- */ 
create or replace function sube_orden_de_plantilla(idplantilla varchar) returns boolean as
$$
	declare r record;
		rplantilla1 record;
		rplantilla2 record;
		m integer;
		o1 integer;
		o2 integer;
	begin
		select * into rplantilla1 from plantilla_de_actividad where id = idplantilla;

		-- asegurando que este en orden las plantillas, de paso devuelve la cantidad y max
		
		m := actualiza_orden_plantillas(rplantilla1.desglose);
		
		if found then
			if rplantilla1.orden > 1 then

				-- obtengo la plantilla de arriba
				select * into rplantilla2 from plantilla_de_actividad where desglose = rplantilla1.desglose and orden = rplantilla1.orden - 1;

				-- hago el intercambio

				o1 := rplantilla1.orden;
				o2 := rplantilla2.orden;
				
				update plantilla_de_actividad set orden = m + 1, actualizacion_interna = true where id = rplantilla1.id;
				update plantilla_de_actividad set orden = o1, actualizacion_interna = true where id = rplantilla2.id;
				update plantilla_de_actividad set orden = o2, actualizacion_interna = true where id = rplantilla1.id;

				-- quitando actualizacion interna
				update plantilla_de_actividad set actualizacion_interna = false where id = rplantilla1.id or id = rplantilla2.id;
			end if;
			return true;
		end if;
		return false;
	end;
$$
language 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.actualiza_restriccion_real ------------------------- */ 

--                     Calculo de la fecha exacta de un dia relativo de un periodo                
/*                                                                                               */
/*     o/  Supongamos una planificacion de 10 dias que comienza el dia real 20.                  */
/*    <|   Si el dia nominal es el 4to del periodo que se encuentra a partir del dia real 3,     */
/*    / \  entonces el dia exacto o fecha exacta es el 25.                                       */
/*                                                                                               */
/*                          dia_inicial                 fecha                                    */
/*                               |                        |                                      */
/*                               v                        v                                      */
/* *//** ---------------------------------------------------------------------------------  *//* */
/* *//** fecha exacta                  | 20 | 21 | 22 | 23 | 24 | 25 | 26 | 27 | 28  | 29 | *//* */
/* *//** ---------------------------------------------------------------------------------- *//* */
/* *//** numero consecutivo del dia    |  1 |  2 | 3  |  4 |  5 | 6  |  7 |  8 |  9  | 10 | *//* */
/* *//** ---------------------------------------------------------------------------------- *//* */
/* *//** periodicidad 1                |    |    |####|####|####|####|####|    |     |    | *//* */
/* *//** ---------------------------------------------------------------------------------- *//* */
/*                                                 ^              ^                              */
/*                                                 |              |                              */
/*                                     rperiodicidad.dia   rnominal.dia                          */
/*                                                                                               */
/*          *//** -----------------------------------------------------------------*//*          */
/*          *//** @formula: fecha = dia_inicial + rperiodicidad.dia - 1 + pdia - 1 *//*          */
/*          *//** @ejemplo: fecha = 20 + 3 - 1 + 4 - 1 = 23 - 1 + 3  = 22 + 3 = 25 *//*          */
/*          *//** -----------------------------------------------------------------*//*          */
/*                                                                                               */
/* ----------------------------------------------------------------------------------------------*/
    
CREATE OR REPLACE FUNCTION actualiza_restriccion_real(
    idobjeto varchar,
    idactividad varchar,
    idnominal varchar,
    idperiodo varchar,
    pdia integer,
    pturno integer,
    por_actividad boolean,
    tabla_real varchar
)
RETURNS VOID AS
$$
DECLARE 
    rperiodicidad RECORD;
    rreal RECORD;
    rplanificacion RECORD;
    idact varchar;
    rperiodo RECORD;
    c bigint;
    
    -- campos a actualizar
    fecha bigint;
    dia_inicial bigint;
    hora_inicial double precision;
    hora_final double precision;
BEGIN   
    
    
    IF idact IS NULL THEN
        idact := 'NULL';
    ELSE
        idact := quote_literal(idactividad);
    END IF;
    
    -- se obtienen otros datos de la restriccion nominal
    -- nota: la tabla de restricciones de periodos no es igual a las demas pero 
        -- todas contiene un campo llamado 'periodo' por lo que no hay problema
        -- en utilizarlo
    SELECT * INTO rplanificacion FROM planificacion WHERE id = dame_planificacion_de_periodo(idperiodo);
    SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;
    
    -- se calculan los demas datos de la fecha real
    dia_inicial := dame_dia_real_planificacion(rplanificacion.id, 1);
    hora_inicial := dame_hora_inicial_turno(idperiodo, pturno); 
    hora_final := hora_inicial + rperiodo.duracion_del_turno;
        
    -- en rnominal se tiene el registro de la restriccion nominal
    -- ahora hay que actualizar o insertar las restricciones reales correspondientes
    -- para eso se recorren todas la periodicidades de la restriccion nominal y si no se encuentra
    -- se inserta y si ya existe se actualiza
    
    FOR rperiodicidad IN SELECT * FROM periodicidad WHERE periodo = idperiodo LOOP
       
       -- se busca si existe
       EXECUTE 'SELECT * FROM ' || tabla_real || ' WHERE restriccion_nominal = ' || quote_literal(idnominal) || ' AND periodicidad = ' || quote_literal(rperiodicidad.id) || ';' INTO rreal;
       GET DIAGNOSTICS c = ROW_COUNT;
       
       fecha := fecha + rperiodicidad.dia - 1 + pdia - 1;
       
       IF c > 0 THEN
            -- actualizar
            EXECUTE 'UPDATE ' || tabla_real || ' SET fecha = ' || fecha || ', hora_inicial = ' || hora_inicial || ', hora_final = ' || hora_final || ', por_actividad = ' || bool_to_str(por_actividad) || ', actividad = ' || idact || ', objeto = ' || quote_literal(idobjeto) || ' WHERE restriccion_nominal = ' || quote_literal(idnominal) || ' AND periodicidad = ' || quote_literal(rperiodicidad.id) || ';';
            
       ELSE
            -- insertar
            EXECUTE 'INSERT INTO ' || tabla_real || '(restriccion_nominal, periodicidad, fecha, hora_inicial, hora_final, por_actividad, actividad, objeto) VALUES (' || quote_literal(idnominal) || ',' || quote_literal(rperiodicidad.id) || ',' || fecha || ',' || hora_inicial || ',' || hora_final || ',' || bool_to_str(por_actividad) || ',' || idact || ',' || quote_literal(idobjeto) || ');';
       END IF;
    END LOOP;
END;
$$
LANGUAGE 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.dame_brigadas_sin_grupo ------------------------- */ 
-- drop function dame_brigadas_sin_grupo(varchar,varchar,varchar, varchar);
create or replace function dame_brigadas_sin_grupo(idesp varchar, idnivel varchar, idclasif varchar, idplanificacion varchar) returns setof varchar as
$$
	declare r brigada%rowtype;
		r1 record;
		-- ids varchar[];
	begin
		for r in select * from brigada where especialidad = $1 and nivel = $2 loop
			select * into r1 from brigada_de_grupo where brigada = r.id and dame_clasificacion_de_grupo(grupo) = idclasif and dame_planificacion_de_grupo(grupo) = idplanificacion;
			if not found then
				return next r.id; -- ids := array_append(ids, r.id);
			end if;
		end loop;
		return; -- ids;
	end;
$$
language 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.dame_nivel_de_grupo ------------------------- */ 
create or replace function dame_nivel_de_grupo(idgrupo varchar) returns varchar as
$$
	select nivel from grupo where id = $1;
$$
language 'sql';
/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.dame_brigadas_de_grupo ------------------------- */ 
﻿create or replace function dame_brigadas_de_grupo(idgrupo varchar) returns setof brigada as
$$ 
	select brigada.* from brigada_de_grupo inner join brigada on brigada_de_grupo.brigada = brigada.id
	where brigada_de_grupo.grupo = $1;
$$
language 'sql';
/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.mueve_o_crea_brigada_de_grupo ------------------------- */ 
create or replace function mueve_o_crea_brigada_de_grupo(idbrg varchar, idgrupo varchar) returns boolean as
$$
	declare r record;
		rr record;
		rgrupo record;
	begin
		-- verificando que el grupo destino no tenga actividad

		select * into r from actividad where grupo = idgrupo;

		if found then
			return false;
		end if;

		-- verificando que ya este asignado a ese grupo
		
		select * into r from brigada_de_grupo where brigada = idbrg and grupo = idgrupo;

		if found then
			return true;
		end if;

		-- verifico que el grupo no tenga actividad asignada
		
		-- obtengo el grupo
		select * into rgrupo from grupo where id = idgrupo;

		-- si no existe me voy
		if not found then
			return false;
		end if;
			
		-- verifico si esta asignada a otro grupo de la misma (esp,nivel,clasif)

		select * into r from brigada_de_grupo 
			where brigada = idbrg 
			and dame_especialidad_de_grupo(grupo) = rgrupo.especialidad 
			and dame_nivel_de_grupo(grupo) = rgrupo.nivel
			and dame_clasificacion_de_grupo(grupo) = rgrupo.clasificacion
			and dame_planificacion_de_grupo(grupo) = rgrupo.planificacion;

		-- elimino el grupo origen
		if found then
			-- verificando que el grupo origen no tenga actividad
			select * into rr from actividad where grupo = r.grupo;

			if found then
				return false;
			end if;
			
			delete from brigada_de_grupo where brigada = idbrg and grupo = r.grupo;
		end if;

		-- creo el grupo destino
		insert into brigada_de_grupo (brigada,grupo) values (idbrg, idgrupo);
		
		return true;
	end;
$$
language 'plpgsql';/* --------------------------------------------------------- */ 
/* ------------------------------ datos.datos_por_defecto ------------------------- */ 
-- Datos por defecto

-- Roles
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('superusuario','Usuario con todos los privilegios');
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('administrador','Administrador');
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('planificador','Planificador');
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('profesor','Profesor');
INSERT INTO seguridad.rol (nombre, descripcion) VALUES ('invitado','Invitado');

-- Usuarios
INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena) VALUES ('admin-superuser','admin','administrador superusuario','admin@example.com','admin',md5('changeit'));

-- Roles de usuarios

INSERT INTO seguridad.rol_de_usuario (usuario, rol) VALUES ('admin-superuser','superusuario');

-- Permisos

INSERT INTO seguridad.permiso (permiso, rol) VALUES ('todos','superusuario');
/* --------------------------------------------------------- */ 
