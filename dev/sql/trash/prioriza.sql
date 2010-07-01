-- Prioriza

drop schema if exists prioriza cascade;
create schema prioriza;

-- BUSCAR MINIMO EN UN ARREGLO

CREATE OR REPLACE FUNCTION prioriza.minimo_pos(arr numeric[], cant integer) returns integer as 
$$
	DECLARE min numeric;
		pos double precision;
		primero boolean;
		i integer;
	BEGIN
		primero := true;
		i := 1;
		LOOP
			IF min > arr[i] or primero = true THEN 
				min := arr[i];
				pos := i;
				primero := false;
			END IF;

			EXIT WHEN i = cant;
			i := i + 1;
			
		END LOOP;

		RETURN pos;
	END;
$$
LANGUAGE  'plpgsql';

-- select PRIORIZA.minimo_pos(array[0,1,7,5,3,5],6);

CREATE OR REPLACE FUNCTION prioriza.minimo(arr numeric[], cant integer) returns numeric as 
$$
	BEGIN
		RETURN arr[prioriza.minimo_pos(arr,cant)];
	END;
$$
LANGUAGE  'plpgsql';

-- select PRIORIZA.minimo(array[0,1,7,5,3,5],6);

-- BUSCAR MAXIMO EN UN ARREGLO

CREATE OR REPLACE FUNCTION prioriza.maximo_pos(arr numeric[], cant integer) returns integer as 
$$
	DECLARE max numeric;
		pos double precision;
		primero boolean;
		i integer;
	BEGIN
		primero := true;
		i := 1;
		LOOP
			IF max < arr[i] or primero = true THEN 
				max := arr[i];
				pos := i;
				primero := false;
			END IF;

			EXIT WHEN i = cant;
			i := i + 1;
			
		END LOOP;

		RETURN pos;
	END;
$$
LANGUAGE  'plpgsql';

-- select PRIORIZA.maximo_pos(array[0,1,7,5,3,5],6);

CREATE OR REPLACE FUNCTION prioriza.maximo(arr numeric[], cant integer) returns numeric as 
$$
	BEGIN
		RETURN arr[prioriza.maximo_pos(arr,cant)];
	END;
$$
LANGUAGE  'plpgsql';

-- select PRIORIZA.maximo(array[0,1,7,5,3,5],6);

-- NIVELAR UN ARREGLO
CREATE OR REPLACE FUNCTION prioriza.nivelar(arr numeric[], cant integer, criterio boolean /* true minimizar, false maximizar*/) returns integer[] as
$$
	declare niveles int[];
		nivel int;
		ya boolean[];
		i integer;
		countya integer;
		optimo integer;
		primero boolean;
	begin
		nivel := 1;
		i := 0;
		countya := 0;

		LOOP
			ya[i] = false;
			EXIT WHEN i = cant;
			i := i + 1;
		END LOOP;

		LOOP
			
			IF criterio = true THEN
				-- buscando minimo
				optimo := 0;
				primero := true;
				i := 1;
				LOOP
					IF ya[i] = false THEN
						IF optimo > arr[i] or primero = true THEN 
							optimo := arr[i];
							primero := false;
						END IF;
					END IF;

					EXIT WHEN i = cant;
					i := i + 1;
					
				END LOOP;
			ELSE
				-- buscando maximo
				optimo := 0;
				primero := true;
				i := 1;
				LOOP
					IF ya[i] = false THEN
						IF optimo < arr[i] or primero = true THEN 
							optimo := arr[i];
							primero := false;
						END IF;
					END IF;
					
					EXIT WHEN i = cant;
					i := i + 1;
					
				END LOOP;
			END IF;

			i := 1;
			LOOP
				IF ya[i] = false THEN
					IF optimo = arr[i] THEN 
						niveles[i] := nivel;
						ya[i] := true;
						countya := countya + 1;					
					END IF;
				END IF;
				
				EXIT WHEN i = cant;
				i := i + 1;
			END LOOP;
			nivel := nivel + 1;
			EXIT WHEN countya = cant;
		END LOOP;
		return niveles;
	end;
$$
LANGUAGE  'plpgsql';

-- select prioriza.nivelar(array[2,4,5,5,6,7,7,3,4,5,6,10,11,11],14,false);


-- NIVELAR UN ARREGLO
CREATE OR REPLACE FUNCTION prioriza.nivelar_matrix(matrix numeric[][], filas integer, columnas integer, criterios boolean[]) returns integer[][] as
$$
	DECLARE
		valores numeric[];
		nivelados integer[];
		niveles integer[][];
		
		i integer;
		j integer;
	BEGIN

		niveles := matrix;

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

			nivelados := prioriza.nivelar(valores, filas, criterios[i]);
			
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

-- select prioriza.nivelar_matrix(array[array[10,10],array[20,20]],2,2,array[true,true]);
-- select prioriza.nivelar(array[5,3],2,false);

