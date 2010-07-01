-- Function: prioriza.nivelar_matrix(numeric[], boolean[])

-- DROP FUNCTION prioriza.nivelar_matrix(numeric[], boolean[]);

CREATE OR REPLACE FUNCTION prioriza.nivelar_matrix(tabla numeric[], criterios boolean[])
  RETURNS integer[] AS
$BODY$
    DECLARE
        valores NUMERIC[];
        nivelados INTEGER[];
        niveles INTEGER[][];
        filas bigint;
        columnas bigint;
        i INTEGER;
        j INTEGER;
    BEGIN

        niveles := tabla;
        filas := matrix.get_length_of(tabla,1);
        columnas := matrix.get_length_of(tabla,2);

	--RAISE NOTICE 'filas %, columnas %', filas, columnas;
        
        i := 1;
        LOOP
            -- tomo la columna actual
            --RAISE NOTICE 'nivelando columna % de %', i, columnas;
            j :=1;
            LOOP
                valores[j] = tabla[j][i];
                EXIT WHEN j >= filas;
                j := j + 1;
            END LOOP;
            
            -- nivelo la columna
	    
	    --EXECUTE matrix.echo(valores);
	    --EXECUTE matrix.echo(criterios);
            nivelados := prioriza.nivelar(valores, criterios[i]);
            
            -- la introduzco en la matriz de respuesta

            j := 1;
            LOOP
                niveles[j][i] := nivelados[j];            
                EXIT WHEN j >= filas;
                j := j + 1;
            END LOOP;            
            
            EXIT WHEN i >= columnas;
            i := i + 1;
        END LOOP;

        RETURN niveles;
    END;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE
  COST 100;
