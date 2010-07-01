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
