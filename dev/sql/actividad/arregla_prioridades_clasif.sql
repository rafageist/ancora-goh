CREATE OR REPLACE FUNCTION arregla_prioridades_clasif(pcreador varchar) returns void as
$$
	declare r record;
		cla varchar[];
		c bigint;
		i bigint;
	begin
	
		FOR r IN SELECT * FROM clasificacion_de_actividad WHERE creador = pcreador ORDER BY prioridad LOOP
		    cla := array_append(cla, r.id);
		END LOOP;
       
		c := matrix.get_length_of(cla,1);
		i := 1;
		LOOP
			UPDATE clasificacion_de_actividad SET prioridad = i WHERE id = cla[i];
			EXIT WHEN i >= c;
			i := i + 1;
		END LOOP;

	end;
$$
language 'plpgsql';