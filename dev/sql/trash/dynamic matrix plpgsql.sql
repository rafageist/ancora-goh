drop function matrix();
drop function prueba();

CREATE OR REPLACE FUNCTION new_numeric_matrix(filas bigint, columnas bigint) returns numeric[][] as
$$
	DECLARE
		fila varchar;
		s varchar;
		matrix numeric[][];
		i bigint;
		j bigint;
	BEGIN
		
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
				
				EXIT WHEN j = columnas;
				j := j + 1;
			END LOOP;
			
			fila := fila || '}';

			s := s || fila;
			IF i <> filas THEN
				s := s || ',';
			END IF;

			
			EXIT WHEN i = filas;
			i := i + 1;
		END LOOP;
		
		s := s || '}';

		matrix := s::numeric[][];
		return matrix;
	END;
$$
LANGUAGE 'plpgsql';


CREATE OR REPLACE FUNCTION new_string_matrix(filas bigint, columnas bigint) returns varchar[][] as
$$
	DECLARE
		fila varchar;
		s varchar;
		matrix varchar[][];
		i bigint;
		j bigint;
	BEGIN
		
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
				
				EXIT WHEN j = columnas;
				j := j + 1;
			END LOOP;
			
			fila := fila || '}';

			s := s || fila;
			IF i <> filas THEN
				s := s || ',';
			END IF;

			
			EXIT WHEN i = filas;
			i := i + 1;
		END LOOP;
		
		s := s || '}';

		matrix := s::varchar[][];
		return matrix;
	END;
$$
LANGUAGE 'plpgsql';


select new_string_matrix(5,5);

create or replace function prueba() returns integer[][] as
$$
	declare mat integer[][];
	
	begin
		mat := crea_matrix_numeric(5,5);
		FOR i IN 1..5 LOOP
			mat[i][i] = 1;
		END LOOP;
		return mat;
	end;
$$
language 'plpgsql';

select prueba();
