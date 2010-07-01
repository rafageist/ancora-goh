DROP SCHEMA IF EXISTS matrix CASCADE;

CREATE SCHEMA matrix;

-- select matrix.create_numeric_list(10);

CREATE OR REPLACE FUNCTION matrix.create_numeric_list(filas bigint) returns numeric[] as
$$
	DECLARE
		fila varchar;
		s varchar;
		matrix numeric[];
		i bigint;
	BEGIN
		IF filas IS NULL OR filas = 0 THEN
			return '{}'::numeric[];
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

		matrix := s::numeric[];
		return matrix;
	END;
$$
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION matrix.create_boolean_list(filas bigint) returns boolean[] as
$$
	DECLARE
		fila varchar;
		s varchar;
		matrix boolean[];
		i bigint;
	BEGIN
		
		s := '{';
		i := 1;

		IF filas IS NULL or filas = 0 THEN
			return '{}'::boolean[];
		END IF;
		
		LOOP
			s := s || 'false';

			IF i <> filas THEN
				s := s || ',';
			END IF;
			
			EXIT WHEN i >= filas;
			i := i + 1;
		END LOOP;
		
		s := s || '}';

		matrix := s::boolean[];
		return matrix;
	END;
$$
LANGUAGE 'plpgsql';

-- select matrix.create_string_list(10);
CREATE OR REPLACE FUNCTION matrix.create_string_list(filas bigint) returns varchar[] as
$$
	DECLARE
		fila varchar;
		s varchar;
		matrix varchar[];
		i bigint;
	BEGIN
		
		s := '{';
		i := 1;

		IF filas IS NULL or filas = 0THEN
			return '{}'::varchar[];
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

		matrix := s::varchar[];
		return matrix;
	END;
$$
LANGUAGE 'plpgsql';


CREATE OR REPLACE FUNCTION matrix.create_numeric_matrix(filas bigint, columnas bigint) returns numeric[][] as
$$
	DECLARE
		fila varchar;
		s varchar;
		matrix numeric[][];
		i bigint;
		j bigint;
	BEGIN
		IF filas IS NULL OR columnas is NULL or filas = 0 or columnas = 0 THEN
			return '{}'::numeric[][];
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

		matrix := s::numeric[][];
		return matrix;
	END;
$$
LANGUAGE 'plpgsql';


CREATE OR REPLACE FUNCTION matrix.create_string_matrix(filas bigint, columnas bigint) returns varchar[][] as
$$
	DECLARE
		fila varchar;
		s varchar;
		matrix varchar[][];
		i bigint;
		j bigint;
	BEGIN
		IF filas IS NULL OR columnas is NULL or filas = 0 or columnas = 0 THEN
			return '{}'::varchar[][];
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

		matrix := s::varchar[][];
		return matrix;
	END;
$$
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION matrix.create_boolean_matrix(filas bigint, columnas bigint) returns boolean[][] as
$$
	DECLARE
		fila varchar;
		s varchar;
		matrix boolean[][];
		i bigint;
		j bigint;
	BEGIN
		IF filas IS NULL OR columnas is NULL OR filas = 0 OR columnas = 0 THEN
			return '{}'::boolean[][];
		END IF;
		
		s := '{';
		i := 1;
		LOOP
			j := 1;
			fila := '{';
			LOOP
				fila := fila || 'false';

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

		matrix := s::boolean[][];
		return matrix;
	END;
$$
LANGUAGE 'plpgsql';

-- EXAMPLE select matrix.create_boolean_cube (3,3,3);
CREATE OR REPLACE FUNCTION matrix.create_boolean_cube(x bigint, y bigint, z bigint) returns boolean[][][] as
$$
	DECLARE
		xs varchar;
		ys varchar;
		zs varchar;
		matrix boolean[][][];
		i bigint;
		j bigint;
		k bigint;
	BEGIN
		IF x IS NULL OR y is NULL OR z IS NULL OR x = 0 OR y = 0 OR z = 0 THEN
			return '{}'::boolean[][][];
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
					zs := zs || 'false';

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

		matrix := xs::boolean[][][];
		return matrix;
	END;
$$
LANGUAGE 'plpgsql';


-- -----------------------------------------------------------------------------------
--      NOMBRE: matrix.get_length_of
--        TIPO: funcion
-- DESCRIPCION: Devuelve la longitud de la dimension de un arrgelo.
-- 		Si es una lista, tiene una dimension y significa devolver la longitud del arreglo
--		Si es una tabla, tiene dos dimensiones y signiica devolver la altura y ancho de la tabla
--		Si es un cubo, tiene 3 dimensiones y significa devolver la altura, ancho y profundidad
--     EJEMPLO: select matrix.get_length_of(array[1,2,6,7,8],1) = 5;
--              select matrix.get_length_of(array[[1,2],[6,7]],1) = 2;
--              select matrix.get_length_of(array[[[1],[2]],[[6],[7]]],1) = 2;
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION matrix.get_length_of(arr anyelement, dimension integer) returns bigint as
$$
	begin
		return to_number(split_part(split_part(array_dims(arr),'][',dimension),':',2),'9999999999');
	end;
$$
language 'plpgsql';



-- -----------------------------------------------------------------------------------
--      NOMBRE: matrix.get_count_of_dims
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de dimensiones de una arreglo (1 - lista, 2 - matrix o 3 - cubo...)
--     EJEMPLO: select matrix.get_count_of_dims(array[1,2,3]) = 1;
--		select matrix.get_count_of_dims(array[[1,2],[1,2]]) = 2;
--		select matrix.get_count_of_dims(array[[[1],[2]],[[1],[2]]]) = 3;
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION matrix.get_count_of_dims(arr anyelement) RETURNS integer AS
$$
	DECLARE c integer;
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
LANGUAGE 'plpgsql';

-- -----------------------------------------------------------------------------------
--      NOMBRE: matrix.get_max_colocation
--        TIPO: funcion
-- DESCRIPCION: Dado un arreglo de posibles inicios de una actividad devuelve la máxima cantidad 
--              de colocaciones posibles
--     EJEMPLO: select matrix.get_max_colocation(array[false,true,true,true,false], 2);

	/* Nota:
		Un arreglo con true al final no debe ocurrir para actividades de más de 1 turno de
		duracion. Por ejemplo array[false,true,true,true]  con una actividad de 2 turnos no
		debe ser un arreglo de posibles inicios, pues en el último turno no se pueden dar dos.
		De todas maneras la funcion verifica eso para un posible arreglo false de posibles
		inicios, de modo tal que 
		
			matrix.get_max_colocation(array[false,true,true,true],2) = 1;
			
		y por su puesto
			matrix.get_max_colocation(array[false,true,true,true],3) = 1;
			matrix.get_max_colocation(array[false,true,true,true],1) = 3;
	*/

-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION matrix.get_max_colocation(arr boolean[], duracion integer) RETURNS integer AS
$$
	DECLARE crows bigint;
		i integer;
		inipi integer;
		ini integer;
		c integer;
	BEGIN
		crows := matrix.lengthof(arr);

		IF crows = 0 THEN
			return 0;
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

COMMENT ON FUNCTION matrix.get_max_colocation(arr boolean[], duracion integer) IS 'Dado un arreglo de posibles inicios de una actividad devuelve la máxima cantidad de colocaciones posibles';

-- -----------------------------------------------------------------------------------
--      NOMBRE: matrix.get_count_of_value_in
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad valores encontrados en un arreglo
--              de colocaciones posibles
--     EJEMPLO: select matrix.get_count_of_value_in(array[false,true,true,true,false], true) = 3;
--              select matrix.get_count_of_value_in(array[[1,2],[2,4]], 2) = 2;
--              select matrix.get_count_of_value_in(array[[[1,2],[2,3]],[[2,4],[4,5]]], 2) = 3;
--              select matrix.get_count_of_value_in(array[[[[1],[2]],[[2],[3]]],[[[2],[4]],[[4],[5]]]], 4) = 2;
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION matrix.get_count_of_value_in(arr anyarray, valor anyelement) RETURNS integer AS
$$
	DECLARE c bigint;
		i integer;
		j integer;
		k integer;
		dims integer;	
		x integer; 
		y integer; 
		z integer;
		narr text;
		nvalor text;
	BEGIN
		c := 0;
		
		dims := matrix.get_count_of_dims(arr);

		-- más de 3 es un caso excepcional:
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


-- --------------------------------
--          NOMBRE: 
--            TIPO:
--     DESCRIPCION:
--         EJEMPLO:
-- ---------------------------------
CREATE OR REPLACE FUNCTION matrix.extract_string_column(arr anyelement, c bigint) RETURNS text[] AS
$$
	DECLARE narr text[];
		i bigint;
		crows bigint;
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
		return narr;
	END;
$$
LANGUAGE 'plpgsql';
CREATE OR REPLACE FUNCTION matrix.extract_numeric_column(arr anyelement, c bigint) RETURNS numeric[] AS
$$
	DECLARE narr numeric[];
		i bigint;
		crows bigint;
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
		return narr;
	END;
$$
LANGUAGE 'plpgsql';

-- --------------------------------
--          NOMBRE: 
--            TIPO:
--     DESCRIPCION:
--         EJEMPLO:
-- ---------------------------------
CREATE OR REPLACE FUNCTION matrix.extract_boolean_column(arr anyelement, c bigint) RETURNS boolean[] AS
$$
	DECLARE narr boolean[];
		i bigint;
		crows bigint;
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
		return narr;
	END;
$$
LANGUAGE 'plpgsql';
-- --------------------------------
--          NOMBRE: 
--            TIPO:
--     DESCRIPCION:
--         EJEMPLO:
-- ---------------------------------
CREATE OR REPLACE FUNCTION matrix.set_matrix_in_cube(out pcube anyarray, pmatrix anyarray, pos bigint) RETURNS anyarray AS
$$
	DECLARE x bigint; y bigint; z bigint; r bigint; c bigint; i bigint; j bigint; 
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
LANGUAGE 'plpgsql';

-- select matrix.get_pos_of(array['a','b','c'],'c');
CREATE OR REPLACE FUNCTION matrix.get_pos_of(arr anyarray, v anyelement) RETURNS bigint AS
$$
	DECLARE i bigint; j bigint; 
		x bigint;
	BEGIN
		x := matrix.get_length_of(arr,1);
		IF x > 0 THEN
			i := 1;
			LOOP
				IF arr[i] = v THEN
					return i;
				END IF;
				
				EXIT WHEN i >= x;
				i := i + 1;
			END LOOP;
		END IF;
		return -1;
		
	END;
$$
LANGUAGE 'plpgsql';

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
LANGUAGE 'plpgsql';

-- -----------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION matrix.prueba() RETURNS boolean AS
$$
	DECLARE cubo boolean[][][];
		matriz boolean[][];
	BEGIN
		matriz := matrix.create_boolean_matrix(2,2);
		matriz[1][1] := true;
		matriz[2][2] := true;
		cubo := matrix.create_boolean_cube(2,2,2);
		cubo := matrix.set_matrix_in_cube(cubo,matriz,1::bigint);
		return cubo[1][1][1];
	END;
$$
LANGUAGE 'plpgsql';

select matrix.prueba();
