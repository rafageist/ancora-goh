-- Ánora GOH 2.0
-- Generador

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_act
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de actividades de una asignatura en un período dado
--     EJEMPLO: select dame_cant_act('mat','si');
-- -----------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION dame_cant_act(asig varchar, periodo varchar) returns bigint as
$$
	SELECT count(plantilla_de_actividad.id)
	FROM asignatura 
	INNER JOIN desglose 
		ON asignatura.id = desglose.asignatura
	INNER JOIN plantilla_de_actividad 
		ON plantilla_de_actividad.desglose = desglose.id
	WHERE asignatura.id = $1 AND desglose.periodo = $2;
$$
LANGUAGE 'sql';
-- -----------------------------------------------------------------------------------

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_asig_segun_esp_anno_per
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de asignaturas de la esp y anno dados
--		ordebados de mayor a menor por la cantidad de actividades en un período dado
--     EJEMPLO: select  dame_asig_segun_esp_anno_per('info','1','sp');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_asig_segun_esp_anno_per(idesp varchar, idnivel varchar, idperiodo varchar) returns varchar[] as
$$
	DECLARE arr varchar[];
		r record;
	BEGIN
		FOR r IN SELECT * FROM asignatura WHERE especialidad = idesp AND nivel = idnivel ORDER BY dame_cant_act(asignatura.id, idperiodo) DESC LOOP
			arr := array_append(arr, r.id);
		END LOOP;
		return arr;
	END;
$$
LANGUAGE 'plpgsql';

-- -----------------------------------------------------------------------------------
--      NOMBRE: asignaturas_cant_act
--        TIPO: vista
-- DESCRIPCION: Listado de las asignaturas con sus cantidades de actividades por periodo
--     EJEMPLO: select  * from asignaturas_cant_act;
-- -----------------------------------------------------------------------------------
DROP VIEW IF EXISTS asignaturas_cant_act CASCADE;
CREATE OR REPLACE VIEW asignaturas_cant_act AS
	SELECT asignatura.id as asignatura, especialidad, nivel, periodo, dame_cant_act(asignatura.id, periodo) as cantidad FROM asignatura INNER JOIN desglose ON desglose.asignatura = asignatura.id;
-- -----------------------------------------------------------------------------------

-- -----------------------------------------------------------------------------------
--      NOMBRE: max_cant_act
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad máxima de actividades las asignaturas
--              de una especialidad en un período específico
--     EJEMPLO: select max_cant_act('info','si');
-- -----------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION max_cant_act(especialidad varchar, nivel varchar, periodo varchar) returns bigint as
$$
	SELECT max(cantidad)
	FROM asignaturas_cant_act 
	WHERE especialidad = $1 AND nivel = $2 AND periodo = $3;
$$
LANGUAGE 'sql';
-- -----------------------------------------------------------------------------------

-- -----------------------------------------------------------------------------------
--      NOMBRE: uso_de_otros_recursos
--        TIPO: vista
-- DESCRIPCION: Listado del uso de los recursos
--     EJEMPLO: select  * from uso_de_otros_recursos;
-- -----------------------------------------------------------------------------------
DROP VIEW IF EXISTS uso_de_otros_recursos CASCADE;
CREATE VIEW uso_de_otros_recursos AS
	SELECT otro_recurso, count(actividad) FROM otros_recursos_usados
	GROUP BY otro_recurso;
	
-- -----------------------------------------------------------------------------------

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_uso_de_otro_recurso
--        TIPO: funcion
-- DESCRIPCION: Uso de un recurso dado
--     EJEMPLO: select  dame_uso_de_otro_recurso('pc');
-- -----------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION dame_uso_de_otro_recurso(id varchar) returns bigint as
$$
	SELECT count(actividad) FROM otros_recursos_usados WHERE otro_recurso = $1
$$
LANGUAGE 'sql';
-- -----------------------------------------------------------------------------------


-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_asig_segun_esp_anno
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de asignaturas de la esp y anno dados
--     EJEMPLO: select  dame_asig_segun_esp_anno('info','1');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_asig_segun_esp_anno(idesp varchar, idnivel varchar) returns varchar[] as
$$
	DECLARE arr varchar[];
		r record;
	BEGIN
		FOR r IN SELECT * FROM asignatura WHERE especialidad = idesp AND nivel = idnivel LOOP
			arr := array_append(arr, r.id);
		END LOOP;
		return arr;
	END;
$$
LANGUAGE 'plpgsql';
-- -----------------------------------------------------------------------------------

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_asig_de_esp_anno
--        TIPO: funcion
-- DESCRIPCION: Devuelve cantidad de asignaturas de la esp y anno dados
--     EJEMPLO: select  dame_cant_asig_de_esp_anno('info','1');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_asig_de_esp_anno(idesp varchar, idnivel varchar) returns bigint as
$$
	SELECT count(id) FROM asignatura WHERE especialidad = $1 AND nivel = $2;
$$
LANGUAGE 'sql';
-- -----------------------------------------------------------------------------------

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_clasif_de_asignaturas
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de clasificaciones de actividades de una 
--		asignatura en un periodo dado
--     EJEMPLO: select  dame_asig_segun_esp_anno('mat','si');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_clasif_de_asignaturas(asignatura varchar, periodo varchar) returns varchar[] as
$$
	DECLARE arr varchar[];
		r record;
	BEGIN
		FOR r IN SELECT plantilla_de_actividad.clasificacion as clasif 
			 FROM plantilla_de_actividad 
			 INNER JOIN desglose ON plantilla_de_actividad.desglose = desglose.id 
			 WHERE desglose.periodo = periodo AND desglose.asignatura = asignatura 
			 ORDER BY plantilla_de_actividad.orden LOOP
			arr := array_append(arr, r.clasif);
		END LOOP;
		return arr;
	END;
$$
LANGUAGE 'plpgsql';
-- -----------------------------------------------------------------------------------

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_clasif
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de clasificaciones
--     EJEMPLO: select  dame_cant_clasif();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_clasif() returns bigint AS
$$
	SELECT count(id) AS cant FROM clasificacion_de_actividad;
$$
LANGUAGE 'sql';

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_clasif
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de la clasificaciones
--     EJEMPLO: select  dame_lista_id_clasif();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_clasif() returns varchar[] AS
$$
	DECLARE arr varchar[];
		r record;
	BEGIN
		FOR r IN SELECT * FROM clasificacion_de_actividad ORDER BY prioridad LOOP
			arr := array_append(arr, r.id);
		END LOOP;
		return arr;
	END;
$$
LANGUAGE 'plpgsql';



-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_profes_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de profesores asignados a una actividad
--     EJEMPLO: select  dame_cant_profes_asignados('act1');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_profes_asignados(idplantilla varchar) returns bigint AS
$$
	SELECT count(id) FROM profesor_para_actividad WHERE plantilla = $1;
$$
LANGUAGE 'sql';


-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_id_plantilla
--        TIPO: funcion
-- DESCRIPCION: Devuelve el id de la plantilla de actividad segun asignatura, periodo y numero de orden
--     EJEMPLO: select  dame_id_plantilla('act1');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_id_plantilla(idasig varchar, idperiodo varchar, orden bigint) returns varchar AS
$$
	SELECT plantilla_de_actividad.id FROM plantilla_de_actividad INNER JOIN desglose ON plantilla_de_actividad.desglose = desglose.id WHERE desglose.asignatura = $1 AND desglose.periodo = $2 AND plantilla_de_actividad.orden = $3;
$$
LANGUAGE 'sql';

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_dias
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de dias de un periodo
--     EJEMPLO: select dame_cant_dias('si');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_dias(idperiodo varchar) returns integer AS
$$
	SELECT cant_dias FROM periodo WHERE id = $1;
$$
LANGUAGE 'sql';

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_turnos
--        TIPO: funcion
-- DESCRIPCION: Devuelve la cantidad de turnos de un periodo
--     EJEMPLO: select dame_cant_turnos('si');
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_turnos(idperiodo varchar) returns integer AS
$$
	SELECT cant_turnos FROM periodo WHERE id = $1;
$$
LANGUAGE 'sql';



-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_cant_numeric_in_matrix
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de la clasificaciones
--     EJEMPLO: select  dame_cant_numeric_in_matrix();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_cant_numeric_in_matrix(matrix numeric[][], filas bigint, columnas bigint, valor numeric) returns bigint AS
$$
	DECLARE c bigint;
		i bigint;
		j bigint;
	BEGIN
		c := 0;
		i := 1;
		j := 1;
		
		LOOP
			EXIT WHEN i > filas;
			LOOP
				EXIT WHEN j > columnas;

				IF matrixj[i][j] = valor THEN
					c := c + 1;
				END IF;
				
				j := j + 1;
			END LOOP;
			i := i + 1;
		END LOOP;
		
		RETURN c;
	END;
$$
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION dame_periodo_desglose(varchar) returns varchar as
$$
	SELECT periodo FROM desglose WHERE id = $1;
$$
LANGUAGE 'sql';


CREATE OR REPLACE FUNCTION dame_periodo_plantilla_de_actividad(varchar) returns varchar as
$$
	SELECT dame_periodo_desglose(desglose) FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION dame_clasif_plantilla_de_actividad(varchar) returns varchar as
$$
	SELECT clasificacion FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION dame_duracion_clasif_de_actividad(varchar) returns integer as
$$
	SELECT duracion FROM clasificacion_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION dame_duracion_plantilla_de_actividad(varchar) returns integer as
$$
	SELECT dame_duracion_clasif_de_actividad(clasificacion) FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION dame_asignatura_desglose(varchar) returns varchar as
$$
	SELECT asignatura FROM desglose WHERE id = $1;
$$
LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION dame_asignatura_plantilla_de_actividad(id varchar) returns varchar as
$$
	SELECT dame_asignatura_desglose(desglose) FROM plantilla_de_actividad WHERE id = $1;
$$
LANGUAGE 'sql';


-- select existe_grupo_de_profesor(1,'grupo1');
CREATE OR REPLACE FUNCTION existe_grupo_de_profesor(profe_para_act varchar, pgrupo varchar) returns boolean as
$$
	DECLARE r record;
	BEGIN
		SELECT * INTO r FROM profesor_para_grupo WHERE profesor_para_actividad = profe_para_act AND grupo = pgrupo;	
		IF r.grupo = pgrupo THEN
			RETURN true;
		END IF;
		RETURN FALSE;
	END;
$$
LANGUAGE 'plpgsql';


-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_profes_asignados_al_grupo
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los PROFESORES asignados a una plantilla 
--              de actividad y a un grupo determinado
--     EJEMPLO: select  dame_lista_id_profes_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_profes_asignados_al_grupo(idplantilla varchar, idgrupo varchar) returns varchar[] AS
$$
	DECLARE arr varchar[];
		r record;
	BEGIN
		FOR r IN SELECT * FROM profesor_para_actividad WHERE plantilla = idplantilla AND EXISTS(SELECT * FROM profesor_para_grupo WHERE profesor_para_grupo.profesor_para_actividad = profe_para_act AND profesor_para_grupo.grupo = pgrupo) ORDER BY prioridad LOOP
			arr := array_append(arr,r.profesor);
		END LOOP;

		RETURN arr;
	END;
$$
LANGUAGE 'plpgsql';

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_profes_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los PROFESORES asignados a una plantilla de actividad
--     EJEMPLO: select  dame_lista_id_profes_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_profes_asignados(idplantilla varchar) returns varchar[] AS
$$
	DECLARE arr varchar[];
		r record;
	BEGIN
		FOR r IN SELECT * FROM profesor_para_actividad WHERE plantilla = idplantilla LOOP
			arr := array_append(arr,r.profesor);
		END LOOP;

		RETURN arr;
	END;
$$
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION dame_cant_profes_asignados(idplantilla varchar) returns bigint AS
$$
	SELECT count(id) FROM profesor_para_actividad WHERE plantilla = $1;
$$
language 'sql';

CREATE OR REPLACE FUNCTION dame_cant_lugares_asignados(idplantilla varchar) returns bigint AS
$$
	SELECT count(lugar) FROM lugar_para_actividad WHERE plantilla = $1;
$$
language 'sql';

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_lugares_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los LUGARES asignados a una plantilla de actividad
--     EJEMPLO: select  dame_lista_id_lugares_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_lugares_asignados(idplantilla varchar) returns varchar[] AS
$$
	DECLARE arr varchar[];
		r record;
	BEGIN
		FOR r IN SELECT * FROM lugar_para_actividad WHERE plantilla = idplantilla LOOP
			arr := array_append(arr,r.lugar);
		END LOOP;

		RETURN arr;
	END;
$$
LANGUAGE 'plpgsql';

-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_grupos_de_recursos_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los grupos de recursos asignados a una plantilla de actividad
--     EJEMPLO: select  dame_lista_id_grupos_de_recursos_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_grupos_de_recursos_asignados(idplantilla varchar) returns varchar[] AS
$$
	DECLARE arr varchar[];
		r record;
	BEGIN
		FOR r IN SELECT * FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = idplantilla LOOP
			arr := array_append(arr,r.id);
		END LOOP;

		RETURN arr;
	END;
$$
LANGUAGE 'plpgsql';


-- -----------------------------------------------------------------------------------
--      NOMBRE: dame_lista_id_otros_recursos_asignados
--        TIPO: funcion
-- DESCRIPCION: Devuelve un arreglo con los id de los otros de recursos asignados 
--              de un grupo de recursos dado
--     EJEMPLO: select  dame_lista_id_otros_recursos_asignados();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION dame_lista_id_otros_recursos_asignados(idgrupo varchar) returns varchar[] AS
$$
	DECLARE arr varchar[];
		r record;
	BEGIN
		FOR r IN SELECT * FROM otro_recurso_para_actividad WHERE grupo_de_recursos = idgrupo LOOP
			arr := array_append(arr,r.otro_recurso);
		END LOOP;

		RETURN arr;
	END;
$$
LANGUAGE 'plpgsql';


CREATE OR REPLACE FUNCTION dame_cant_grupos_de_recursos_asignados(idplantilla varchar) returns bigint AS
$$
	SELECT count(id) FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = $1;
$$
language 'sql';

CREATE OR REPLACE FUNCTION dame_cant_otros_recursos_asignados(idgrupo varchar) returns bigint AS
$$
	SELECT count(otro_recurso) FROM otro_recurso_para_actividad WHERE grupo_de_recursos = $1;
$$
language 'sql';

CREATE OR REPLACE FUNCTION dame_lista_id_brigadas_de_esp(esp varchar, niv varchar) returns varchar[] AS
$$
	DECLARE arr varchar[];
		r record;
	BEGIN
		FOR r IN SELECT * FROM brigada WHERE especialidad = esp AND nivel = niv LOOP
			arr := array_append(arr,r.id);
		END LOOP;

		RETURN arr;
	END;
$$
LANGUAGE 'plpgsql';

-- -----------------------------------------------------------------------------------
--      NOMBRE: posible_inicio
--        TIPO: funcion
-- DESCRIPCION: Dice si es posible o no colocar una actividad en el dia turno para una brigada
--     EJEMPLO: select  posible_inicio();
-- -----------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION posible_inicio(idplantilla varchar, dia integer, turno integer, idbrigada varchar, plugares varchar[], pprofesores varchar[]) returns bigint AS
$$
	DECLARE c bigint;
		i bigint;
		j bigint;
		k integer;
		t bigint;
		v integer;
		duracion integer;
		periodo varchar;
		clasif varchar;
		asignatura varchar;
		profesores varchar[];
		lugares varchar[];
		cantProfes bigint;
		cantLugares bigint;
		gruporecursos varchar[];
		recursos varchar[];
		cantGrupoRecursos bigint;
		cantRecursos bigint;
		r record;
		malo boolean;
		puede boolean;
		exige_lugar boolean;
		exige_profesor boolean;
	BEGIN

		duracion := dame_duracion_plantilla_de_actividad(idplantilla);
		periodo := dame_periodo_plantilla_de_actividad(idplantilla);
		clasif := dame_clasif_plantilla_de_actividad(idplantilla);
		asignatura := dame_asignatura_plantilla_de_actividad(idplantilla);

		exige_lugar := plantilla_exige_lugar(idplantilla);
		exige_profesor := plantilla_exige_profesor(idplantilla);

		IF exige_profesor = true THEN
			IF pprofesores is null THEN
				profesores := dame_lista_id_profes_asignados(idplantilla);
			ELSE
				profesores := pprofesores;
			END IF;
			
			cantProfes := matrix.get_length_of(profesores, 1);
		END IF;
		

		IF exige_lugar = true THEN
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
			
			-- comprobando que el periodo no esté restringido
			
			v:= valor_restriccion_periodo(periodo, dia, turno + i);

			IF v > 0 THEN
				RETURN false;
			END IF;

			-- comprobando la disponibilidad de la clasificacion

			v:= valor_restriccion_clasif_act(clasif, dia, turno + i);

			IF v > 0 THEN
				RETURN false;
			END IF;

			-- comprobando la disponibilidad de la asignatura

			v:= valor_restriccion_asignatura(asignatura, dia, turno + i);

			IF v > 0 THEN
				RETURN false;
			END IF;
			
			-- compoobando la disponibilidad de la brigada

			v:= valor_restriccion_brigada(idbrigada, dia, turno + i);

			IF v > 0 THEN
				RETURN false;
			END IF;
			
			i := i + 1;
		END LOOP;


		IF exige_profesor = true THEN
			-- comprobar la disponibilidad de profesores
			
			j := 0;

			k := 0;
			
			LOOP
				EXIT WHEN j = cantProfes;

				puede := true;
				
				i := 0;
				LOOP
					EXIT WHEN i = duracion;
								
					v:= valor_restriccion_profesor(profesores[j], dia, turno + i);

					IF v > 0 THEN
						puede := false;
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

		IF exige_lugar = true THEN	
			-- comprobar la disponibilidad de lugares
			j := 0;

			k := 0;
			
			LOOP
				EXIT WHEN j = cantLugares;

				puede := true;
				
				i := 0;
				LOOP
					EXIT WHEN i = duracion;
								
					v := valor_restriccion_lugar(lugares[j], dia, turno + i);

					IF v > 0 THEN
						puede := false;
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

					malo := false;
					
					LOOP
						EXIT WHEN k = duracion;
						v := valor_restriccion_otro_recurso(recursos[j],dia,turno + k);
					
						IF v > 0 THEN 
							malo := true;
							EXIT;
						END IF;
						
						k:= k + 1;
					END LOOP;

					IF malo = false THEN
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

		RETURN true;
	END;
$$
LANGUAGE 'plpgsql';


CREATE OR REPLACE FUNCTION dame_cant_rest_asignatura(idprofesor varchar, idperiodo varchar) returns bigint AS
$$
	SELECT count(valor) FROM restriccion_de_asignatura WHERE asignatura = $1 AND periodo = $2;
$$
language 'sql';

CREATE OR REPLACE FUNCTION dame_cant_rest_profe(idprofesor varchar, idperiodo varchar) returns bigint AS
$$
	SELECT count(valor) FROM restriccion_de_profesor WHERE profesor = $1 AND periodo = $2;
$$
language 'sql';

CREATE OR REPLACE FUNCTION dame_cant_rest_lugar(idlugar varchar, idperiodo varchar) returns bigint AS
$$
	SELECT count(valor) FROM restriccion_de_lugar WHERE lugar = $1 AND periodo = $2;
$$
language 'sql';

CREATE OR REPLACE FUNCTION dame_cant_otros_recursos_imprescindibles(idplantilla varchar) returns bigint AS
$$
	SELECT sum(otro_grupo_de_recursos_para_actividad.necesarios) FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = $1 AND imprescindibles = TRUE;
$$
language 'sql';

CREATE OR REPLACE FUNCTION dame_total_otros_recursos_asignados(idplantilla varchar) returns numeric AS
$$
	SELECT sum(dame_cant_otros_recursos_asignados(id)) FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = $1;
$$
language 'sql';

CREATE OR REPLACE FUNCTION dame_cant_accesos_prohibidos_al_grupo(varchar) returns bigint AS
$$
	SELECT count(lugar) FROM acceso_prohibido WHERE grupo = $1;
$$
language 'sql';

CREATE OR REPLACE FUNCTION dame_especialidad_de_brigada (idbrigada varchar) RETURNS varchar AS
$$
	SELECT especialidad FROM brigada WHERE id = $1;
$$
language 'sql';

CREATE OR REPLACE FUNCTION dame_nivel_de_brigada (idbrigada varchar) RETURNS varchar AS
$$
	SELECT nivel FROM brigada WHERE id = $1;
$$
language 'sql';

CREATE OR REPLACE FUNCTION dame_especialidad_de_asignatura (idasig varchar) RETURNS varchar AS
$$
	SELECT especialidad FROM asignatura WHERE id = $1;
$$
language 'sql';

CREATE OR REPLACE FUNCTION dame_nivel_de_asignatura (idasig varchar) RETURNS varchar AS
$$
	SELECT nivel FROM asignatura WHERE id = $1;
$$
language 'sql';

CREATE OR REPLACE FUNCTION dame_clasificacion_de_grupo (idgrupo varchar) RETURNS varchar AS
$$
	SELECT clasificacion FROM grupo WHERE id = $1;
$$
language 'sql';


CREATE OR REPLACE FUNCTION dame_grupos(idesp varchar, idnivel varchar, idclasif varchar) returns varchar[] AS
$$
	DECLARE arr varchar[];
		r RECORD;
	BEGIN
		FOR r IN SELECT grupo FROM brigada_de_grupo WHERE dame_especialidad_de_brigada(brigada) = idesp AND dame_nivel_de_brigada(brigada) = idnivel AND dame_clasificaicon_de_grupo(grupo) = idclasif GROUP BY grupo LOOP
			arr := array_append(arr,r.grupo);
		END LOOP;
		return arr;
	END;
	
$$
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION dame_lista_id_brigadas_de_grupo(pgrupo varchar) returns varchar[] AS
$$
	DECLARE arr varchar[];
		r RECORD;
	BEGIN
		FOR r IN SELECT brigada FROM brigada_de_grupo WHERE grupo = pgrupo LOOP
			arr := array_append(arr,r.brigada);
		END LOOP;
		return arr;
	END;
	
$$
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION dame_matricula(brg varchar) returns integer AS
$$
	SELECT matricula FROM brigada WHERE id = $1;
$$
LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION dame_matricula_total(brigadas varchar[]) returns bigint AS
$$
	DECLARE i bigint;
		total bigint;
		cantBrg bigint;
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
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION dame_capacidad(lug varchar) returns integer AS
$$
	SELECT capacidad FROM lugar WHERE id = $1;
$$
LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION quita_segun_capacidad(brigadas varchar[], lugares varchar[]) returns varchar[] AS
$$
	DECLARE arr varchar[];
		r RECORD;
		cantLug bigint;
		i bigint;
		matricula bigint;
		capacidad integer;
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
		
		return arr;
	END;
	
$$
LANGUAGE 'plpgsql';


CREATE OR REPLACE FUNCTION es_lugar_prohibido(pgrupo varchar, plugar varchar) returns boolean AS
$$
	DECLARE r record;
	BEGIN
		SELECT * INTO r FROM acceso_prohibido WHERE grupo = pgrupo AND lugar = plugar;
		IF r.grupo = pgrupo THEN
			return true;
		END IF;
		return false;
	END;
$$
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION quita_segun_prohibidos(grupos varchar[], lugares varchar[]) returns varchar[] AS
$$
	DECLARE arr varchar[];
		r RECORD;
		cantLug bigint; cantGrupos bigint;
		i bigint; j bigint;
		puede boolean;
	BEGIN
		
		cantLug := matrix.get_length_of(lugares);
		cantGrupos := matrix.get_length_of(grupos);
		
		i := 1;
		LOOP
			puede := true;

			j := 1;

			LOOP
				IF es_lugar_prohibido (grupos[j], lugares[i]) THEN
					puede := false;
					EXIT;
				END IF;
				
				EXIT WHEN j = cantGrupos;
				j := j + 1;
			END LOOP;

			IF puede = true THEN
				arr := array_append(arr, lugares[i]);
			END IF;
			
			EXIT WHEN i = lugares;
			i := i + 1;
		END LOOP;
		
		return arr;
	END;
	
$$
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION dame_grupo(brg varchar, clasif varchar ) returns varchar AS
$$
	SELECT grupo FROM brigada_de_grupo WHERE brigada = $1 AND dame_clasificacion_de_grupo(grupo) = $2;
$$
LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION dame_grupos_de_brigadas(brigadas varchar[], idplantilla varchar) returns varchar[] AS
$$
	DECLARE arr varchar;
		cantBrg bigint;
		i bigint;
		g varchar;
		clasif varchar;
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
		return arr;
	END;
$$
LANGUAGE 'plpgsql';


CREATE OR REPLACE FUNCTION and_mpi(listaidbrg varchar[], cd integer, ct integer, idplantilla varchar, zona bigint, excepto boolean) returns boolean[][] AS
$$
	DECLARE mpi boolean[][];
		r RECORD;
		i integer;
		j integer;
		k integer;
		cantBrg bigint;
		pi boolean;
		lugares varchar[];
		cantLugares bigint;
		profesores varchar[];
		cantProfes bigint;
		grupos varchar[];
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

		-- inicializo la mpi con true en todas las casillas
		i := 1;

		LOOP
			j := 1;
			LOOP
				mpi[i][j] := true;
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

					IF excepto = true THEN
						SELECT * INTO r FROM actividad WHERE plantilla = idplantilla AND dia = i AND turno = j;
						IF r.plantilla = idplantilla THEN
							pi = true;
						END IF;
					END IF;

					-- me quedo con los lugares que tengan capacidad
					lugares := quita_segun_capacidad(listaidbrg, lugares);
					
					-- me quedo con los lugares que no estén prohibidos

					lugares := quita_segun_prohibidos(grupos, lugares);

					IF matrix.get_length_of(lugares) = 0 THEN
						pi := false;
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
		
		return mpi;
	END;
	
$$
LANGUAGE 'plpgsql';


CREATE OR REPLACE FUNCTION dame_zpriori(clasif varchar, idperiodo varchar) returns integer[][] AS
$$
	DECLARE zp integer[][];
		i bigint; j bigint;
		CD integer; CT integer;
		r record;
	BEGIN 
		
		CD := dame_cant_dias(idperiodo);
		CT := dame_cant_turnos(idperiodo);
		
		zp := matrix.create_boolean_matrix(CD,CT);
	
		FOR r IN SELECT * FROM zpriori WHERE clasificacion = clasif AND periodo = idperiodo LOOP
			zp[r.dia][r.turno] := r.valor;
		END LOOP;
		
		return zp;
	END;
$$
LANGUAGE 'plpgsql';


/*DROP FUNCTION prueba();
CREATE OR REPLACE FUNCTION prueba () returns integer AS
$$
	declare p TActxProfexDia;
		r trango;
		z trango;
	begin
		r.ini := 203;
		p.rango := r;
		z := p.rango;
		return z.ini;
	end;
$$
language 'plpgsql';

select prueba();
*/


CREATE OR REPLACE FUNCTION dame_desglose_de_plantilla (idplantilla varchar ) returns varchar as
$$
	SELECT desglose FROM plantilla_de_actividad WHERE id = $1;
$$
language 'sql';


CREATE TYPE TRango AS (
	ini integer,
	fin integer
);

CREATE OR REPLACE FUNCTION rango(idplantilla varchar, idgrupo varchar, minimo integer, maximo integer) returns TRango AS
$$	
	declare ran TRango;
		i integer; 
		rplantilla record;
		rperiodo record;
		rdesglose record;
		n integer;
		arr integer[];
		CD integer;
		r record;
		w record;
		c integer;
		da integer; db integer; dif integer;
	begin

		SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = idplantilla;
		SELECT * INTO rdesglose FROM desglose WHERE id = rplantilla.desglose;
		SELECT * INTO rperiodo FROM periodo WHERE id = rdesglose.periodo;

		ran.ini = 1;
		CD := rperiodo.cant_dias;
		ran.fin = CD;
		n := rplantilla.orden;
		
		IF rdesglose.respeta = FALSE THEN
			return ran;
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
	
	end;
$$
language 'plpgsql';


CREATE OR REPLACE FUNCTION dia_minimo_impacto(idclasif varchar, idesp varchar, idnivel varchar, idperiodo varchar, dia_ini integer, dia_fin integer) RETURNS integer AS
$$
	DECLARE
		i bigint; j bigint; k bigint; l bigint; ll bigint; dia integer;
		max bigint;
		cant bigint;
		r record;
		CD integer;
		CT integer;
		rperiodo r;
		cantBrg bigint;
		maxzpriori integer;
		zp integer[][];
		impactodiario integer[];
		
	BEGIN

		zp := dame_zpriori(idclasif, idperiodo);

		SELECT * INTO r FROM zpriori WHERE clasificacion = idclasif AND periodo = idperiodo ORDER BY valor DESC;

		maxzpriori := r.valor;
			
		SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;

		CD := r.cant_dias;
		CT := r.cant_turnos;
		
		SELECT count(id) as cant INTO r FROM brigada WHERE especialidad = idesp AND nivel = idnivel;

		cantBrg := r.cant;

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
		-- como para los demás

		-- Poniendo cero según restricciones de la clasificacion de actividad
		FOR r IN SELECT * FROM restriccion_de_clasif_act WHERE clasificacion = idclasif AND periodo = idperiodo LOOP
			IF r.valor > 0 THEN 
				zp[r.dia][r.turno] := 0;
			END IF;
		END LOOP;
	
		-- Poniendo cero según restricciones de las brigadas
		FOR r IN SELECT * FROM restriccion_de_brigada WHERE dame_especialidad_de_brigada(brigada) = idesp AND dame_nivel_de_brigada(nivel) = idnivel LOOP
			IF r.valor > 0 THEN 
				zp[r.dia][r.turno] := 0;
			END IF;
		END LOOP;

		-- Poniendo cero según restricciones del periodo
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

			-- puede que haya otros minimos, pero me quedo con el primero porque está mas cerca
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
language 'plpgsql';


CREATE OR REPLACE FUNCTION lugar_permanece_en(plugar varchar, pperiodo varchar, pdia integer, pturno integer, duracion integer) RETURNS boolean AS
$$
	BEGIN
		RETURN EXISTS(SELECT * FROM restriccion_de_lugar WHERE lugar = plugar AND periodo = pperiodo AND dia = pdia AND turno >= pturno AND turno <= pturno + duracion - 1);
	END;
$$
language 'plpgsql';


CREATE OR REPLACE FUNCTION extraer_lugares_que_no_permanecen (lugares varchar[], periodo varchar, dia integer, turno integer, duracion integer) RETURNS varchar[] AS
$$
	DECLARE
		arr varchar[];
		cant bigint;
		i bigint;
		
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
language 'plpgsql';


CREATE OR REPLACE FUNCTION dame_lugares_utilizados_en_el_dia(pgrupo varchar, periodo varchar, pdia integer) RETURNS varchar[] AS
$$
	DECLARE
		arr varchar[];
		r record;
		
	BEGIN
		FOR r IN SELECT * FROM actividades WHERE grupo = pgrupo AND dia = pdia AND dame_periodo_de_plantilla(plantilla) = periodo LOOP
			arr := array_append (arr,r.lugar);
		END LOOP;
		
		RETURN arr;
	END;
$$
language 'plpgsql';

CREATE OR REPLACE FUNCTION dame_gasto_lugares_en_el_dia(lugares varchar[], periodo varchar, pdia integer) RETURNS bigint[] AS
$$
	DECLARE
		arr bigint[];
		u bigint;
		i bigint;
		cant bigint;
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
language 'plpgsql';


CREATE OR REPLACE FUNCTION busca_lugar_mas_utilizado_por(lugares varchar[], idgrupo varchar, los_demas boolean) returns varchar as
$$
	DECLARE i bigint;
		cant bigint;
		m integer;
		cantr integer;
		pos bigint;
		r record;
	begin
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
			return '';
		END IF;
		RETURN lugares[pos];
	end;
$$
language 'plpgsql';

-- select busca_lugar_menos_utilizado(array['lug33','lug2','lug3'],'grupo1');
CREATE OR REPLACE FUNCTION busca_lugar_menos_utilizado_por(lugares varchar[], idgrupo varchar, los_demas boolean) returns varchar as
$$
	DECLARE i bigint;
		cant bigint;
		m integer;
		cantr integer;
		pos bigint;
		r record;
	begin
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
			return '';
		END IF;
		
		RETURN lugares[pos];
	end;
$$
language 'plpgsql';



--
-- Devuelve el id del lugar según origenes[] que es menos costoso de llegar a los destinos[]
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

-- select busca_lugar_menos_costoso(array['203','202'],array['201','202','203']);
CREATE OR REPLACE FUNCTION busca_lugar_menos_costoso(origenes varchar[], destinos varchar[]) returns varchar as
$$
	DECLARE i bigint; j bigint;
		min bigint; pos bigint;
		cant_origenes bigint; 
		cant_destinos bigint;
		costo bigint;
		r record;
	begin
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
	end;
$$
language 'plpgsql';

CREATE OR REPLACE FUNCTION dame_lugares_usados_alrededor_de(brigadas varchar[], idperiodo varchar, pdia integer, pturno integer) returns varchar[] as
$$
	declare arr varchar[];
		cantbrg bigint;
		i bigint;
		j integer;
		rperiodo record;
		r record;
	begin
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
	end;
$$
language 'plpgsql';

-- select dame_uso_de_otro_recurso('a');
CREATE OR REPLACE FUNCTION dame_uso_de_otro_recurso(id varchar) returns bigint as 
$$
	SELECT count(otro_recurso) FROM otros_recursos_usados WHERE otro_recurso = $1;
$$
language 'sql';

-- select dame_costo_trasladar_recurso('r1','l1','si',1,2);
CREATE OR REPLACE FUNCTION dame_costo_trasladar_recurso(idrecurso varchar, idlugar varchar, idperiodo varchar, pdia integer, pturno integer) returns bigint as
$$
	DECLARE i bigint; j bigint;
		costo bigint;
		rperiodo record;
		r record;
		id varchar;
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
		return costo;
	END;
$$
language 'plpgsql';

CREATE OR REPLACE FUNCTION agrega_actividad (idplantilla varchar, idprofesor varchar, idlugar varchar, pdia integer, pturno integer, idgurpo varchar, idrecursos varchar[], momento double precision, pcreador varchar) returns void as
$$
	declare pid varchar;
		listaidbrg varchar[];
		cant bigint;
		i bigint; j integer;
		rplantilla record;
		r record;
	BEGIN

		SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = idplantilla;

		pid := util.id_replica ('ancoragoh','actividad','id','actividad');
		INSERT INTO actividad (id, plantilla, profesor, lugar, dia, turno, momento_de_colocacion, creador, grupo) VALUES (pid, idplantilla, idprofesor, idlugar, pdia, pturno, momento, pcreador, idgrupo);

		-- restringir a los implicados

		-- restringiendo a las brigadas
		listaidbrg := dame_lista_id_brigadas_de_grupo(idgrupo);
		cant := matrix.get_length_of(listaidbrg);

		i := 1;
		LOOP
			j := pturno;

			LOOP
				INSERT INTO restriccion_de_brigada (brigada, periodo, dia, turno, valor, por_actividad) VALUES (listaidbrg[i], idperiodo, pdia, j, 1, TRUE);
				
				EXIT WHEN j = pturno + rplantila.duracion - 1;
				j := j + 1;
			END LOOP;
			
			EXIT WHEN i = cant;

			i := i + 1;
		END LOOP;
		
		-- restringiendo al profesor y lugar
		j := pturno;

		LOOP
			INSERT INTO restriccion_de_profesor (profesor, periodo, dia, turno, valor, por_actividad) VALUES (idprofesor, idperiodo, pdia, j, 1, TRUE);
			INSERT INTO restriccion_de_lugar (lugar, periodo, dia, turno, valor, por_actividad) VALUES (idlugar, idperiodo, pdia, j, 1, TRUE);
			
			EXIT WHEN j = pturno + rplantila.duracion - 1;
			j := j + 1;
		END LOOP;

		cant := matrix.get_length_of(idrecursos);
		
		i := 1;
		LOOP

			INSERT INTO otros_recursos_usados (otro_recurso, actividad) VALUES (idrecursos[i], id);

			j := pturno;

			LOOP
				INSERT INTO restriccion_de_otro_recurso (otro_recurso, periodo, dia, turno, valor, por_actividad) VALUES (idrecursos[i], idperiodo, pdia, j, 1, TRUE);
				
				EXIT WHEN j = pturno + rplantila.duracion - 1;
				j := j + 1;
			END LOOP;
			
			EXIT WHEN i = cant;

			i := i + 1;
		END LOOP;
	END;
$$
LANGUAGE 'plpgsql';

-- ----------------------------------------------------------------------------------------------
-- Devuelve si en un (dia,turno) de un período, está restringido en una plantilla del período
-- ----------------------------------------------------------------------------------------------
-- El problema se da puesto que ambos periodos pueden tener configuraciones de tiempo distintas
-- y por lo tanto no se puede no corresponder el turno de un periodo con el turno de su plantilla
-- La solucion consiste partiendo del tiempo nominal del per1, llevarlo a su tiempo real, y luego
-- este llevarlo al tiempo nominal de per2 y ver si está o no restringido.
/*
-- Por ejemplo:
-- Periodo 1: 5 dias x 6 turnos, hora inicial 08:00:00 duración 01:30:00

	Turno   Hora inicio del turno
	-----------------------------
	1	08:00:00 hrs
	2	09:30:00 hrs
	3	11:00:00 hrs
	4 	13:30:00 hrs
	5       15:00:00 hrs
	6       16:30:00 hrs
	-----------------------------
	fin	17:59:59 hrs
	
-- Periodo 2(plantilla): 7 dias x 7 turnos, hora inicial 09:00:00 duración 00:45:00

	Turno   Hora inicio del turno
	-----------------------------
	1	09:00:00 hrs
	2	09:45:00 hrs
	3	10:30:00 hrs
	4	11:15:00 hrs
	5	12:00:00 hrs
	6	12:45:00 hrs
	7	13:30:00 hrs
	-----------------------------
	fin	14:14:59 hrs

-- Encuestar dia 2 turno 2

-- El turno 2 del día 2 del Período 1 es de 11:00:00 hrs hasta 13:29:59 hrs
-- Entonces llevando a tiempo nominal del Período 2 serían los turnos 3,4,5,6 del día 2
   pues las 11:00:00 hrs está en el turno 3 del Período 2 y 13:29:59 hrs está en el turno 6

   Para saber en que turno cae una hora se utiliza la formula

   turno := (hora - hora_inicial) / duracion + 1;

   donde hora_inicial es la hora de comienzo de la jornada en el período y duracion es 
   la duración del turno.
     

*/

CREATE OR REPLACE FUNCTION valor_rest_entre_config_distintas(rests integer[][], per1 periodo, per2 periodo, dia integer, turno integer) RETURNS integer as
$$
	DECLARE hi double precision; hf double precision;
		ti integer; tf integer;
		i integer;
	begin
		hi := (turno-1) * per1.duracion_del_turno;
		hf := hi + per1.duracion_del_turno;

		ti := (hi - per2.hora_inicial) / per2.duracion + 1;
		tf := (hf - per2.hora_inicial) / per2.duracion + 1;

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
LANGUAGE 'plpgsql';


