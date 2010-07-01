--
-- PostgreSQL database dump
--

-- Started on 2010-10-06 09:18:45

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- TOC entry 11 (class 2615 OID 36210)
-- Name: generador; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA generador;


ALTER SCHEMA generador OWNER TO postgres;

--
-- TOC entry 5 (class 2615 OID 26170)
-- Name: matrix; Type: SCHEMA; Schema: -; Owner: rafa
--

CREATE SCHEMA matrix;


ALTER SCHEMA matrix OWNER TO rafa;

--
-- TOC entry 6 (class 2615 OID 26171)
-- Name: prioriza; Type: SCHEMA; Schema: -; Owner: rafa
--

CREATE SCHEMA prioriza;


ALTER SCHEMA prioriza OWNER TO rafa;

--
-- TOC entry 8 (class 2615 OID 26172)
-- Name: seguridad; Type: SCHEMA; Schema: -; Owner: rafa
--

CREATE SCHEMA seguridad;


ALTER SCHEMA seguridad OWNER TO rafa;

--
-- TOC entry 9 (class 2615 OID 26173)
-- Name: tiempo; Type: SCHEMA; Schema: -; Owner: rafa
--

CREATE SCHEMA tiempo;


ALTER SCHEMA tiempo OWNER TO rafa;

--
-- TOC entry 735 (class 2612 OID 16386)
-- Name: plpgsql; Type: PROCEDURAL LANGUAGE; Schema: -; Owner: rafa
--

CREATE PROCEDURAL LANGUAGE plpgsql;


ALTER PROCEDURAL LANGUAGE plpgsql OWNER TO rafa;

SET search_path = public, pg_catalog;

--
-- TOC entry 526 (class 1247 OID 26174)
-- Dependencies: 527 10
-- Name: bigint_unsigned; Type: DOMAIN; Schema: public; Owner: rafa
--

CREATE DOMAIN bigint_unsigned AS bigint
	CONSTRAINT bigint_unsigned_check CHECK ((VALUE >= 0));


ALTER DOMAIN public.bigint_unsigned OWNER TO rafa;

--
-- TOC entry 528 (class 1247 OID 26178)
-- Dependencies: 10 1923
-- Name: breakpoint; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE breakpoint AS (
	func oid,
	linenumber integer,
	targetname text
);


ALTER TYPE public.breakpoint OWNER TO postgres;

--
-- TOC entry 530 (class 1247 OID 26181)
-- Dependencies: 10 1924
-- Name: frame; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE frame AS (
	level integer,
	targetname text,
	func oid,
	linenumber integer,
	args text
);


ALTER TYPE public.frame OWNER TO postgres;

--
-- TOC entry 532 (class 1247 OID 26182)
-- Dependencies: 533 10
-- Name: int_unsigned; Type: DOMAIN; Schema: public; Owner: rafa
--

CREATE DOMAIN int_unsigned AS integer
	CONSTRAINT int_unsigned_check CHECK ((VALUE >= 0));


ALTER DOMAIN public.int_unsigned OWNER TO rafa;

--
-- TOC entry 534 (class 1247 OID 26186)
-- Dependencies: 10 1925
-- Name: proxyinfo; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE proxyinfo AS (
	serverversionstr text,
	serverversionnum integer,
	proxyapiver integer,
	serverprocessid integer
);


ALTER TYPE public.proxyinfo OWNER TO postgres;

--
-- TOC entry 536 (class 1247 OID 26187)
-- Dependencies: 537 10
-- Name: smallint_unsigned; Type: DOMAIN; Schema: public; Owner: rafa
--

CREATE DOMAIN smallint_unsigned AS smallint
	CONSTRAINT smallint_unsigned_check CHECK ((VALUE >= 0));


ALTER DOMAIN public.smallint_unsigned OWNER TO rafa;

--
-- TOC entry 538 (class 1247 OID 26191)
-- Dependencies: 10 1926
-- Name: targetinfo; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE targetinfo AS (
	target oid,
	schema oid,
	nargs integer,
	argtypes oidvector,
	targetname name,
	argmodes "char"[],
	argnames text[],
	targetlang oid,
	fqname text,
	returnsset boolean,
	returntype oid
);


ALTER TYPE public.targetinfo OWNER TO postgres;

--
-- TOC entry 540 (class 1247 OID 26194)
-- Dependencies: 10 1927
-- Name: trango; Type: TYPE; Schema: public; Owner: rafa
--

CREATE TYPE trango AS (
	ini integer,
	fin integer
);


ALTER TYPE public.trango OWNER TO rafa;

--
-- TOC entry 542 (class 1247 OID 26197)
-- Dependencies: 10 1928
-- Name: var; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE var AS (
	name text,
	varclass character(1),
	linenumber integer,
	isunique boolean,
	isconst boolean,
	isnotnull boolean,
	dtype oid,
	value text
);


ALTER TYPE public.var OWNER TO postgres;

SET search_path = tiempo, pg_catalog;

--
-- TOC entry 544 (class 1247 OID 26200)
-- Dependencies: 9 1929
-- Name: ancora_date; Type: TYPE; Schema: tiempo; Owner: rafa
--

CREATE TYPE ancora_date AS (
	d integer,
	m integer,
	a integer
);


ALTER TYPE tiempo.ancora_date OWNER TO rafa;

--
-- TOC entry 546 (class 1247 OID 26203)
-- Dependencies: 9 1930
-- Name: ancora_hour; Type: TYPE; Schema: tiempo; Owner: rafa
--

CREATE TYPE ancora_hour AS (
	h integer,
	m integer,
	s integer
);


ALTER TYPE tiempo.ancora_hour OWNER TO rafa;

SET search_path = generador, pg_catalog;

--
-- TOC entry 241 (class 1255 OID 36220)
-- Dependencies: 11 735
-- Name: fue_cancelado(character varying, character varying, character varying); Type: FUNCTION; Schema: generador; Owner: postgres
--

CREATE FUNCTION fue_cancelado(idesp character varying, idnivel character varying, idperiodo character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
	declare
		r record;
	begin
		select * into r from generador.cancelar where especialidad = idesp and nivel = idnivel and periodo = idperiodo;

		if found then
			delete from generador.cancelar where especialidad = idesp and nivel = idnivel and periodo = idperiodo;
			return true;
		end if;

		return false;
	end;
$$;


ALTER FUNCTION generador.fue_cancelado(idesp character varying, idnivel character varying, idperiodo character varying) OWNER TO postgres;

SET search_path = matrix, pg_catalog;

--
-- TOC entry 24 (class 1255 OID 26204)
-- Dependencies: 735 5
-- Name: create_boolean_cube(bigint, bigint, bigint); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION create_boolean_cube(x bigint, y bigint, z bigint) RETURNS boolean[]
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.create_boolean_cube(x bigint, y bigint, z bigint) OWNER TO rafa;

--
-- TOC entry 25 (class 1255 OID 26205)
-- Dependencies: 5 735
-- Name: create_boolean_list(bigint); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION create_boolean_list(filas bigint) RETURNS boolean[]
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.create_boolean_list(filas bigint) OWNER TO rafa;

--
-- TOC entry 26 (class 1255 OID 26206)
-- Dependencies: 735 5
-- Name: create_boolean_matrix(bigint, bigint); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION create_boolean_matrix(filas bigint, columnas bigint) RETURNS boolean[]
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.create_boolean_matrix(filas bigint, columnas bigint) OWNER TO rafa;

--
-- TOC entry 27 (class 1255 OID 26207)
-- Dependencies: 735 5
-- Name: create_numeric_list(bigint); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION create_numeric_list(filas bigint) RETURNS numeric[]
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.create_numeric_list(filas bigint) OWNER TO rafa;

--
-- TOC entry 2771 (class 0 OID 0)
-- Dependencies: 27
-- Name: FUNCTION create_numeric_list(filas bigint); Type: COMMENT; Schema: matrix; Owner: rafa
--

COMMENT ON FUNCTION create_numeric_list(filas bigint) IS 'Construye un arreglo de booleanos';


--
-- TOC entry 30 (class 1255 OID 26208)
-- Dependencies: 5 735
-- Name: create_numeric_matrix(bigint, bigint); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION create_numeric_matrix(filas bigint, columnas bigint) RETURNS numeric[]
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.create_numeric_matrix(filas bigint, columnas bigint) OWNER TO rafa;

--
-- TOC entry 31 (class 1255 OID 26209)
-- Dependencies: 5 735
-- Name: create_string_list(bigint); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION create_string_list(filas bigint) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.create_string_list(filas bigint) OWNER TO rafa;

--
-- TOC entry 224 (class 1255 OID 26210)
-- Dependencies: 5 735
-- Name: create_string_matrix(bigint, bigint); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION create_string_matrix(filas bigint, columnas bigint) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.create_string_matrix(filas bigint, columnas bigint) OWNER TO rafa;

--
-- TOC entry 32 (class 1255 OID 26211)
-- Dependencies: 735 5
-- Name: echo(anyarray); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION echo(arr anyarray) RETURNS void
    LANGUAGE plpgsql
    AS $$
    BEGIN
        RAISE NOTICE '[INFO] array = %', array_to_string(arr,',');
    END;
$$;


ALTER FUNCTION matrix.echo(arr anyarray) OWNER TO rafa;

--
-- TOC entry 33 (class 1255 OID 26212)
-- Dependencies: 5 735
-- Name: extract_boolean_column(anyelement, bigint); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION extract_boolean_column(arr anyelement, c bigint) RETURNS boolean[]
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.extract_boolean_column(arr anyelement, c bigint) OWNER TO rafa;

--
-- TOC entry 34 (class 1255 OID 26213)
-- Dependencies: 735 5
-- Name: extract_numeric_column(anyelement, bigint); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION extract_numeric_column(arr anyelement, c bigint) RETURNS numeric[]
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.extract_numeric_column(arr anyelement, c bigint) OWNER TO rafa;

--
-- TOC entry 35 (class 1255 OID 26214)
-- Dependencies: 5 735
-- Name: extract_string_column(anyelement, bigint); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION extract_string_column(arr anyelement, c bigint) RETURNS text[]
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.extract_string_column(arr anyelement, c bigint) OWNER TO rafa;

--
-- TOC entry 36 (class 1255 OID 26215)
-- Dependencies: 735 5
-- Name: get_count_of_dims(anyelement); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION get_count_of_dims(arr anyelement) RETURNS integer
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.get_count_of_dims(arr anyelement) OWNER TO rafa;

--
-- TOC entry 37 (class 1255 OID 26216)
-- Dependencies: 735 5
-- Name: get_count_of_value_in(anyarray, anyelement); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION get_count_of_value_in(arr anyarray, valor anyelement) RETURNS integer
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.get_count_of_value_in(arr anyarray, valor anyelement) OWNER TO rafa;

--
-- TOC entry 40 (class 1255 OID 26217)
-- Dependencies: 735 5
-- Name: get_length_of(anyelement, integer); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION get_length_of(arr anyelement, dimension integer) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
    BEGIN
	IF arr IS NULL THEN
		return 0;
	END IF;
	
        RETURN to_number(split_part(split_part(array_dims(arr),'][',dimension),':',2),'9999999999');
    END;
$$;


ALTER FUNCTION matrix.get_length_of(arr anyelement, dimension integer) OWNER TO rafa;

--
-- TOC entry 38 (class 1255 OID 26218)
-- Dependencies: 735 5
-- Name: get_max(anyarray); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION get_max(arr anyarray) RETURNS anyelement
    LANGUAGE plpgsql
    AS $$
    BEGIN
        RETURN arr[matrix.get_max_pos(arr)];
    END;
$$;


ALTER FUNCTION matrix.get_max(arr anyarray) OWNER TO rafa;

--
-- TOC entry 39 (class 1255 OID 26219)
-- Dependencies: 735 5
-- Name: get_max_pos(anyarray); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION get_max_pos(arr anyarray) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.get_max_pos(arr anyarray) OWNER TO rafa;

--
-- TOC entry 28 (class 1255 OID 26220)
-- Dependencies: 5 735
-- Name: get_min(anyarray); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION get_min(arr anyarray) RETURNS anyelement
    LANGUAGE plpgsql
    AS $$
    BEGIN
        RETURN arr[matrix.get_min_pos(arr)];
    END;
$$;


ALTER FUNCTION matrix.get_min(arr anyarray) OWNER TO rafa;

--
-- TOC entry 41 (class 1255 OID 26221)
-- Dependencies: 735 5
-- Name: get_min_pos(anyarray); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION get_min_pos(arr anyarray) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.get_min_pos(arr anyarray) OWNER TO rafa;

--
-- TOC entry 42 (class 1255 OID 26222)
-- Dependencies: 735 5
-- Name: get_pos_of(anyarray, anyelement); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION get_pos_of(arr anyarray, v anyelement) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.get_pos_of(arr anyarray, v anyelement) OWNER TO rafa;

--
-- TOC entry 43 (class 1255 OID 26223)
-- Dependencies: 735 5
-- Name: set_matrix_in_cube(anyarray, bigint); Type: FUNCTION; Schema: matrix; Owner: rafa
--

CREATE FUNCTION set_matrix_in_cube(OUT pcube anyarray, pmatrix anyarray, pos bigint) RETURNS anyarray
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION matrix.set_matrix_in_cube(OUT pcube anyarray, pmatrix anyarray, pos bigint) OWNER TO rafa;

SET search_path = prioriza, pg_catalog;

--
-- TOC entry 52 (class 1255 OID 26224)
-- Dependencies: 6 735
-- Name: nivelar(numeric[], boolean); Type: FUNCTION; Schema: prioriza; Owner: rafa
--

CREATE FUNCTION nivelar(arr numeric[], criterio boolean) RETURNS integer[]
    LANGUAGE plpgsql
    AS $$
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
        cant := matrix.get_length_of(arr,1);
        niveles := matrix.create_numeric_list(cant);

	--RAISE NOTICE 'Nivelando una fila';
        
        i := 0;
        countya := 0;

        LOOP
            ya[i] = FALSE;
            EXIT WHEN i >= cant;
            i := i + 1;
        END LOOP;

        LOOP
            
            IF criterio = TRUE THEN
		--RAISE NOTICE 'Criterio TRUE buscando minimo...';
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

                    EXIT WHEN i >= cant;
                    i := i + 1;
                    
                END LOOP;
            ELSE
		--RAISE NOTICE 'Criterio FALSE buscando maximo...';
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
                    
                    EXIT WHEN i >= cant;
                    i := i + 1;
                    
                END LOOP;
            END IF;

	    --RAISE NOTICE 'Optimo %', optimo;
	    --RAISE NOTICE 'Criterio TRUE buscando minimo... %', countya;
	    
            i := 1;
            LOOP
                IF ya[i] = FALSE THEN
                    IF optimo = arr[i] THEN 
                        niveles[i] := nivel;
                        ya[i] := TRUE;
                        countya := countya + 1;                    
                    END IF;
                END IF;
                
                EXIT WHEN i >= cant;
                i := i + 1;
            END LOOP;
            nivel := nivel + 1;
            EXIT WHEN countya = cant;
        END LOOP;
        RETURN niveles;
    END;
$$;


ALTER FUNCTION prioriza.nivelar(arr numeric[], criterio boolean) OWNER TO rafa;

--
-- TOC entry 233 (class 1255 OID 26225)
-- Dependencies: 735 6
-- Name: nivelar_matrix(numeric[], boolean[]); Type: FUNCTION; Schema: prioriza; Owner: rafa
--

CREATE FUNCTION nivelar_matrix(tabla numeric[], criterios boolean[]) RETURNS integer[]
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION prioriza.nivelar_matrix(tabla numeric[], criterios boolean[]) OWNER TO rafa;

SET search_path = public, pg_catalog;

--
-- TOC entry 44 (class 1255 OID 26226)
-- Dependencies: 735 10
-- Name: actualiza_orden_act_after_delete(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION actualiza_orden_act_after_delete() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	declare r integer;
	begin

		r := actualiza_orden_plantillas(OLD.desglose);
		return OLD;
	end;
$$;


ALTER FUNCTION public.actualiza_orden_act_after_delete() OWNER TO rafa;

--
-- TOC entry 45 (class 1255 OID 26227)
-- Dependencies: 735 10
-- Name: actualiza_orden_act_after_insert(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION actualiza_orden_act_after_insert() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	declare r integer;
	
	begin
		r := actualiza_orden_plantillas(NEW.desglose);
		
		return NEW;
	end;
$$;


ALTER FUNCTION public.actualiza_orden_act_after_insert() OWNER TO rafa;

--
-- TOC entry 46 (class 1255 OID 26228)
-- Dependencies: 735 10
-- Name: actualiza_orden_act_after_update(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION actualiza_orden_act_after_update() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	declare r integer;
	begin

		IF NEW.actualizacion_interna = FALSE THEN
			IF OLD.orden <> NEW.orden THEN
				r := actualiza_orden_plantillas(NEW.desglose);
			END IF;
		END IF;
		
		return NEW;
	end;
$$;


ALTER FUNCTION public.actualiza_orden_act_after_update() OWNER TO rafa;

--
-- TOC entry 47 (class 1255 OID 26229)
-- Dependencies: 735 10
-- Name: actualiza_orden_act_before_insert(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION actualiza_orden_act_before_insert() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	declare r integer;
		rr record;
	begin
		SELECT * into rr from plantilla_de_actividad WHERE desglose = NEW.desglose AND orden = NEW.orden;

		IF FOUND THEN
			NEW.orden := dame_max_orden_act(NEW.desglose) + 1;
		END IF;
		
		return NEW;
	end;
$$;


ALTER FUNCTION public.actualiza_orden_act_before_insert() OWNER TO rafa;

--
-- TOC entry 48 (class 1255 OID 26230)
-- Dependencies: 735 10
-- Name: actualiza_orden_act_before_update(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION actualiza_orden_act_before_update() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.actualiza_orden_act_before_update() OWNER TO rafa;

--
-- TOC entry 49 (class 1255 OID 26231)
-- Dependencies: 735 10
-- Name: actualiza_orden_actividades(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION actualiza_orden_actividades() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	declare r integer;
	begin
		r := actualiza_orden_plantillas(NEW.desglose);
		return NEW;
	end;
$$;


ALTER FUNCTION public.actualiza_orden_actividades() OWNER TO rafa;

--
-- TOC entry 50 (class 1255 OID 26232)
-- Dependencies: 735 10
-- Name: actualiza_orden_plantillas(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION actualiza_orden_plantillas(iddesglose character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.actualiza_orden_plantillas(iddesglose character varying) OWNER TO rafa;

--
-- TOC entry 51 (class 1255 OID 26233)
-- Dependencies: 10 735
-- Name: actualiza_profesor_para_grupo(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION actualiza_profesor_para_grupo() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	declare r record;
	begin

		select * into r from profesor_para_grupo where profesor_para_actividad = NEW.profesor_para_actividad and grupo = NEW.grupo;
		if not found then
			insert into profesor_para_grupo (profesor_para_actividad, grupo) VALUES (NEW.profesor_para_actividad, NEW.grupo);
		end if;
		return NEW;
	end;
$$;


ALTER FUNCTION public.actualiza_profesor_para_grupo() OWNER TO rafa;

--
-- TOC entry 222 (class 1255 OID 26234)
-- Dependencies: 735 10
-- Name: actualiza_restriccion_real(character varying, character varying, character varying, character varying, integer, integer, boolean, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION actualiza_restriccion_real(idobjeto character varying, idactividad character varying, idnominal character varying, idperiodo character varying, pdia integer, pturno integer, por_actividad boolean, tabla_real character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
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
    
    
    IF idactividad IS NULL THEN
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
       
       fecha := dia_inicial + rperiodicidad.dia - 1 + pdia - 1;
       
       IF c > 0 THEN
            -- actualizar
            EXECUTE 'UPDATE ' || tabla_real || ' SET fecha = ' || fecha || ', hora_inicial = ' || hora_inicial || ', hora_final = ' || hora_final || ', por_actividad = ' || bool_to_str(por_actividad) || ', actividad = ' || idact || ', objeto = ' || quote_literal(idobjeto) || ' WHERE restriccion_nominal = ' || quote_literal(idnominal) || ' AND periodicidad = ' || quote_literal(rperiodicidad.id) || ';';
            
       ELSE
            -- insertar
        
           EXECUTE 'INSERT INTO ' || tabla_real || '(restriccion_nominal, periodicidad, fecha, hora_inicial, hora_final, por_actividad, actividad, objeto) VALUES (' || quote_literal(idnominal) || ',' || quote_literal(rperiodicidad.id) || ',' || fecha || ',' || hora_inicial || ',' || hora_final || ',' || bool_to_str(por_actividad) || ',' || idact || ',' || quote_literal(idobjeto) || ');';
           
       END IF;
    END LOOP;
END;
$$;


ALTER FUNCTION public.actualiza_restriccion_real(idobjeto character varying, idactividad character varying, idnominal character varying, idperiodo character varying, pdia integer, pturno integer, por_actividad boolean, tabla_real character varying) OWNER TO rafa;

--
-- TOC entry 61 (class 1255 OID 26235)
-- Dependencies: 735 10
-- Name: agrega_actividad(character varying, character varying, character varying, integer, integer, character varying, character varying[], double precision, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION agrega_actividad(idplantilla character varying, idprofesor character varying, idlugar character varying, pdia integer, pturno integer, idgrupo character varying, idrecursos character varying[], momento double precision, pcreador character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
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

        cant := matrix.get_length_of(idrecursos, 1);

        IF cant > 0 THEN
		i := 1;
		LOOP
		    INSERT INTO otros_recursos_usados (otro_recurso, actividad) VALUES (idrecursos[i], pid);
		    EXIT WHEN i = cant;
		    i := i + 1;
		END LOOP;
        END IF;
    END;
$$;


ALTER FUNCTION public.agrega_actividad(idplantilla character varying, idprofesor character varying, idlugar character varying, pdia integer, pturno integer, idgrupo character varying, idrecursos character varying[], momento double precision, pcreador character varying) OWNER TO rafa;

--
-- TOC entry 59 (class 1255 OID 26236)
-- Dependencies: 735 10
-- Name: and_mpi(character varying[], integer, integer, character varying, bigint, boolean); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION and_mpi(listaidbrg character varying[], cd integer, ct integer, idplantilla character varying, zona bigint, excepto boolean) RETURNS boolean[]
    LANGUAGE plpgsql
    AS $$
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

	--RAISE NOTICE '     [INFO] AND MPI ...';
	
        cantBrg := matrix.get_length_of(listaidbrg, 1);

	--RAISE NOTICE '          [INFO] AND MPI: obtengo los lugares y profesores asignados a la plantilla ...';
	
        -- obtengo los lugares y profesores asignados a la plantilla 
        lugares := dame_lista_id_lugares_asignados(idplantilla);
        cantLugares := matrix.get_length_of(lugares, 1);

        profesores := dame_lista_id_profes_asignados(idplantilla);
        cantProfes := matrix.get_length_of(profesores, 1);

	--RAISE NOTICE '          [INFO] AND MPI: obtengo los grupos involucrados segun la plantilla y las brigadas ...';
        -- obtengo los grupos involucrados segun la plantilla y las brigadas
        -- generalmente debe ser uno solo

        grupos := dame_grupos_de_brigadas (listaidbrg, idplantilla);

        --RAISE NOTICE '          [INFO] AND MPI: creo la mpi ...';
        -- creo la mpi
        mpi := matrix.create_boolean_matrix(cd,ct);

	--RAISE NOTICE '          [INFO] AND MPI: inicializo la mpi con TRUE en todas las casillas ...';
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

        --RAISE NOTICE '          [INFO] AND MPI: recorro cada brigada comprobando la disponibilidad en cada turno ...';
        -- recorro cada brigada comprobando la disponibilidad en cada turno
        k := 1;
        LOOP
	    --RAISE NOTICE '          [INFO] AND MPI: % verifico para cada turno si es posible inicio ...', k;
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

		    --RAISE NOTICE '          [INFO] AND MPI: %  me quedo con los lugares que tengan capacidad ...', k;
                    -- me quedo con los lugares que tengan capacidad
                    lugares := quita_segun_capacidad(listaidbrg, lugares);
                    
		    --RAISE NOTICE '          [INFO] AND MPI: %  me quedo con los lugares que no estan prohibidos ...', k;
                    -- me quedo con los lugares que no estan prohibidos

                    lugares := quita_segun_prohibidos(grupos, lugares);

                    IF matrix.get_length_of(lugares, 1) = 0 THEN
                        pi := FALSE;
                    END IF;

                    mpi[i][j] := mpi[i][j] AND pi;

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
    
$$;


ALTER FUNCTION public.and_mpi(listaidbrg character varying[], cd integer, ct integer, idplantilla character varying, zona bigint, excepto boolean) OWNER TO rafa;

--
-- TOC entry 204 (class 1255 OID 34375)
-- Dependencies: 735 10
-- Name: arregla_prioridades_clasif(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION arregla_prioridades_clasif(pcreador character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.arregla_prioridades_clasif(pcreador character varying) OWNER TO postgres;

--
-- TOC entry 203 (class 1255 OID 34376)
-- Dependencies: 10 735
-- Name: arregla_prioridades_clasif_after_delete(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION arregla_prioridades_clasif_after_delete() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	begin
		execute arregla_prioridades_clasif(old.creador);
		return old;
	end;
$$;


ALTER FUNCTION public.arregla_prioridades_clasif_after_delete() OWNER TO postgres;

--
-- TOC entry 53 (class 1255 OID 26237)
-- Dependencies: 10 735
-- Name: baja_orden_de_plantilla(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION baja_orden_de_plantilla(idplantilla character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.baja_orden_de_plantilla(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 202 (class 1255 OID 34371)
-- Dependencies: 735 10
-- Name: baja_prioridad_clasif(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION baja_prioridad_clasif(idclasif character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
	declare 
		r1 record;
		r2 record;
	begin
		SELECT * INTO r1 FROM clasificacion_de_actividad WHERE id = idclasif;

		IF r1.prioridad < dame_cant_clasif() THEN
			SELECT * INTO r2 FROM clasificacion_de_actividad WHERE creador = r1.creador AND prioridad = r1.prioridad + 1;
			UPDATE clasificacion_de_actividad SET prioridad = r1.prioridad WHERE id = r2.id;
			UPDATE clasificacion_de_actividad SET prioridad = r2.prioridad WHERE id = r1.id;
		END IF;
		
		return true;
	end;
$$;


ALTER FUNCTION public.baja_prioridad_clasif(idclasif character varying) OWNER TO postgres;

--
-- TOC entry 54 (class 1255 OID 26238)
-- Dependencies: 735 10
-- Name: bool_to_int(boolean); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION bool_to_int(valor boolean) RETURNS integer
    LANGUAGE plpgsql
    AS $$
BEGIN 
    IF valor = TRUE THEN
        RETURN 1;
    END IF;
    RETURN 0;
END;
$$;


ALTER FUNCTION public.bool_to_int(valor boolean) OWNER TO rafa;

--
-- TOC entry 2772 (class 0 OID 0)
-- Dependencies: 54
-- Name: FUNCTION bool_to_int(valor boolean); Type: COMMENT; Schema: public; Owner: rafa
--

COMMENT ON FUNCTION bool_to_int(valor boolean) IS 'Convierte un valor BOOLEAN a un entero donde 1 = TRUE y 0 = FALSE';


--
-- TOC entry 55 (class 1255 OID 26239)
-- Dependencies: 735 10
-- Name: bool_to_str(boolean); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION bool_to_str(valor boolean) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
BEGIN 
    IF valor = TRUE THEN
        RETURN 'TRUE';
    END IF;
    RETURN 'FALSE';
END;
$$;


ALTER FUNCTION public.bool_to_str(valor boolean) OWNER TO rafa;

--
-- TOC entry 2773 (class 0 OID 0)
-- Dependencies: 55
-- Name: FUNCTION bool_to_str(valor boolean); Type: COMMENT; Schema: public; Owner: rafa
--

COMMENT ON FUNCTION bool_to_str(valor boolean) IS 'Convierte un valor BOOLEAN a un VARCHAR donde "TRUE" = TRUE y "FALSE" = FALSE';


--
-- TOC entry 60 (class 1255 OID 26240)
-- Dependencies: 735 10
-- Name: busca_lugar_mas_utilizado_por(character varying[], character varying, boolean); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION busca_lugar_mas_utilizado_por(lugares character varying[], idgrupo character varying, los_demas boolean) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
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
            SELECT * INTO r FROM actividad WHERE lugar = lugares[i] AND ((grupo = idgrupo AND los_demas = FALSE) OR (grupo <> idgrupo AND los_demas = TRUE)) ;

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
$$;


ALTER FUNCTION public.busca_lugar_mas_utilizado_por(lugares character varying[], idgrupo character varying, los_demas boolean) OWNER TO rafa;

--
-- TOC entry 71 (class 1255 OID 26241)
-- Dependencies: 735 10
-- Name: busca_lugar_menos_costoso(character varying[], character varying[]); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION busca_lugar_menos_costoso(origenes character varying[], destinos character varying[]) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
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
        IF cant_origenes > 0 THEN
		LOOP
		    costo := 0;
		    j := 1;
		    IF cant_destinos > 0 THEN
			    LOOP
				SELECT * INTO r FROM costo_de_traslado WHERE origen = origenes[i] AND destino = destinos[j];
				
				costo := costo + r.costo;
				
				EXIT WHEN j >= cant_destinos;
				j := j + 1;
			    END LOOP;
		    END IF;
		    IF min > costo OR pos = -1 THEN
			min = costo;
			pos = i;
		    END IF;
		    
		    EXIT WHEN i >= cant_origenes;
		    i := i + 1;
		END LOOP;
        END IF;

        IF pos = -1 THEN
            RETURN '';
        END IF;
        
        RETURN origenes[pos];
    END;
$$;


ALTER FUNCTION public.busca_lugar_menos_costoso(origenes character varying[], destinos character varying[]) OWNER TO rafa;

--
-- TOC entry 228 (class 1255 OID 26242)
-- Dependencies: 735 10
-- Name: busca_lugar_menos_utilizado_por(character varying[], character varying, boolean); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION busca_lugar_menos_utilizado_por(lugares character varying[], idgrupo character varying, los_demas boolean) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
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
            SELECT * INTO r FROM actividad WHERE lugar = lugares[i] AND ((grupo = idgrupo AND los_demas = FALSE) OR (grupo <> idgrupo AND los_demas = TRUE)) ;

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
$$;


ALTER FUNCTION public.busca_lugar_menos_utilizado_por(lugares character varying[], idgrupo character varying, los_demas boolean) OWNER TO rafa;

--
-- TOC entry 62 (class 1255 OID 26243)
-- Dependencies: 735 10
-- Name: check_hrt(character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION check_hrt(hijo_brigada character varying, hijo_profesor character varying, hijo_lugar character varying, hijo_otro_recurso character varying, padre_brigada character varying, padre_profesor character varying, padre_lugar character varying, padre_otro_recurso character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.check_hrt(hijo_brigada character varying, hijo_profesor character varying, hijo_lugar character varying, hijo_otro_recurso character varying, padre_brigada character varying, padre_profesor character varying, padre_lugar character varying, padre_otro_recurso character varying) OWNER TO rafa;

--
-- TOC entry 58 (class 1255 OID 26244)
-- Dependencies: 10
-- Name: concat(text, text); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION concat(text, text) RETURNS text
    LANGUAGE sql
    AS $_$SELECT $1 || $2;$_$;


ALTER FUNCTION public.concat(text, text) OWNER TO rafa;

--
-- TOC entry 63 (class 1255 OID 26245)
-- Dependencies: 735 10
-- Name: crear_cadena_aleatoria(character varying, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION crear_cadena_aleatoria(caracteres character varying DEFAULT 'abcdefghijklmnopqrstuvwxyz1234567890'::character varying, longitud integer DEFAULT 20) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
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
    RETURN cadena;
END;
$$;


ALTER FUNCTION public.crear_cadena_aleatoria(caracteres character varying, longitud integer) OWNER TO rafa;

--
-- TOC entry 2774 (class 0 OID 0)
-- Dependencies: 63
-- Name: FUNCTION crear_cadena_aleatoria(caracteres character varying, longitud integer); Type: COMMENT; Schema: public; Owner: rafa
--

COMMENT ON FUNCTION crear_cadena_aleatoria(caracteres character varying, longitud integer) IS 'Devuelve una cadena de caracteres alfanum?ricos aleatorios';


--
-- TOC entry 64 (class 1255 OID 26246)
-- Dependencies: 10 735
-- Name: crear_id_de_replica(character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION crear_id_de_replica(prefijo character varying, tabla character varying, campoid character varying, sufijo character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.crear_id_de_replica(prefijo character varying, tabla character varying, campoid character varying, sufijo character varying) OWNER TO rafa;

--
-- TOC entry 2775 (class 0 OID 0)
-- Dependencies: 64
-- Name: FUNCTION crear_id_de_replica(prefijo character varying, tabla character varying, campoid character varying, sufijo character varying); Type: COMMENT; Schema: public; Owner: rafa
--

COMMENT ON FUNCTION crear_id_de_replica(prefijo character varying, tabla character varying, campoid character varying, sufijo character varying) IS 'Crea una cadena de caracteres ?nica para una columna de una tabla';


--
-- TOC entry 65 (class 1255 OID 26247)
-- Dependencies: 10 735
-- Name: dame_asig_segun_esp_anno(character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_asig_segun_esp_anno(idesp character varying, idnivel character varying) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM asignatura WHERE especialidad = idesp AND nivel = idnivel LOOP
            arr := array_append(arr, r.id);
        END LOOP;
        RETURN arr;
    END;
$$;


ALTER FUNCTION public.dame_asig_segun_esp_anno(idesp character varying, idnivel character varying) OWNER TO rafa;

--
-- TOC entry 66 (class 1255 OID 26248)
-- Dependencies: 735 10
-- Name: dame_asig_segun_esp_anno_per(character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_asig_segun_esp_anno_per(idesp character varying, idnivel character varying, idperiodo character varying) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM asignatura WHERE especialidad = idesp AND nivel = idnivel ORDER BY dame_cant_act(asignatura.id, idperiodo) DESC LOOP
            arr := array_append(arr, r.id);
        END LOOP;
        RETURN arr;
    END;
$$;


ALTER FUNCTION public.dame_asig_segun_esp_anno_per(idesp character varying, idnivel character varying, idperiodo character varying) OWNER TO rafa;

--
-- TOC entry 67 (class 1255 OID 26249)
-- Dependencies: 10
-- Name: dame_asignatura_desglose(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_asignatura_desglose(character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT asignatura FROM desglose WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_asignatura_desglose(character varying) OWNER TO rafa;

--
-- TOC entry 68 (class 1255 OID 26250)
-- Dependencies: 10
-- Name: dame_asignatura_plantilla_de_actividad(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_asignatura_plantilla_de_actividad(id character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT dame_asignatura_desglose(desglose) FROM plantilla_de_actividad WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_asignatura_plantilla_de_actividad(id character varying) OWNER TO rafa;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 1931 (class 1259 OID 26251)
-- Dependencies: 2299 2300 10
-- Name: brigada; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE brigada (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'brigada'::character varying, 'id'::character varying, 'brigada'::character varying) NOT NULL,
    descripcion character varying NOT NULL,
    especialidad character varying NOT NULL,
    nivel character varying NOT NULL,
    matricula integer DEFAULT 0,
    creador character varying NOT NULL
);


ALTER TABLE public.brigada OWNER TO rafa;

--
-- TOC entry 69 (class 1255 OID 26259)
-- Dependencies: 10 548
-- Name: dame_brigadas_de_grupo(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_brigadas_de_grupo(idgrupo character varying) RETURNS SETOF brigada
    LANGUAGE sql
    AS $_$ 
	select brigada.* from brigada_de_grupo inner join brigada on brigada_de_grupo.brigada = brigada.id
	where brigada_de_grupo.grupo = $1;
$_$;


ALTER FUNCTION public.dame_brigadas_de_grupo(idgrupo character varying) OWNER TO rafa;

--
-- TOC entry 70 (class 1255 OID 26260)
-- Dependencies: 10 735
-- Name: dame_brigadas_sin_grupo(character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_brigadas_sin_grupo(idesp character varying, idnivel character varying, idclasif character varying, idplanificacion character varying) RETURNS SETOF character varying
    LANGUAGE plpgsql
    AS $_$
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
$_$;


ALTER FUNCTION public.dame_brigadas_sin_grupo(idesp character varying, idnivel character varying, idclasif character varying, idplanificacion character varying) OWNER TO rafa;

--
-- TOC entry 56 (class 1255 OID 26261)
-- Dependencies: 10
-- Name: dame_cant_accesos_prohibidos_al_grupo(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_cant_accesos_prohibidos_al_grupo(character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$
    SELECT count(lugar) FROM acceso_prohibido WHERE grupo = $1;
$_$;


ALTER FUNCTION public.dame_cant_accesos_prohibidos_al_grupo(character varying) OWNER TO rafa;

--
-- TOC entry 57 (class 1255 OID 26262)
-- Dependencies: 10
-- Name: dame_cant_act(character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_cant_act(asig character varying, periodo character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$
    SELECT count(plantilla_de_actividad.id)
    FROM asignatura 
    INNER JOIN desglose 
        ON asignatura.id = desglose.asignatura
    INNER JOIN plantilla_de_actividad 
        ON plantilla_de_actividad.desglose = desglose.id
    WHERE asignatura.id = $1 AND desglose.periodo = $2;
$_$;


ALTER FUNCTION public.dame_cant_act(asig character varying, periodo character varying) OWNER TO rafa;

--
-- TOC entry 72 (class 1255 OID 26263)
-- Dependencies: 10
-- Name: dame_cant_act_desglose(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_cant_act_desglose(desglose character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$
    SELECT count(plantilla_de_actividad.id)
    FROM desglose 
    INNER JOIN plantilla_de_actividad 
        ON plantilla_de_actividad.desglose = desglose.id
    WHERE desglose.id = $1;
$_$;


ALTER FUNCTION public.dame_cant_act_desglose(desglose character varying) OWNER TO rafa;

--
-- TOC entry 73 (class 1255 OID 26264)
-- Dependencies: 10
-- Name: dame_cant_asig_de_esp_anno(character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_cant_asig_de_esp_anno(idesp character varying, idnivel character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$
    SELECT count(id) FROM asignatura WHERE especialidad = $1 AND nivel = $2;
$_$;


ALTER FUNCTION public.dame_cant_asig_de_esp_anno(idesp character varying, idnivel character varying) OWNER TO rafa;

--
-- TOC entry 74 (class 1255 OID 26265)
-- Dependencies: 10
-- Name: dame_cant_clasif(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_cant_clasif() RETURNS bigint
    LANGUAGE sql
    AS $$
    SELECT count(id) AS cant FROM clasificacion_de_actividad;
$$;


ALTER FUNCTION public.dame_cant_clasif() OWNER TO rafa;

--
-- TOC entry 75 (class 1255 OID 26266)
-- Dependencies: 10
-- Name: dame_cant_dias(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_cant_dias(idperiodo character varying) RETURNS integer
    LANGUAGE sql
    AS $_$
    SELECT cant_dias FROM periodo WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_cant_dias(idperiodo character varying) OWNER TO rafa;

--
-- TOC entry 76 (class 1255 OID 26267)
-- Dependencies: 10
-- Name: dame_cant_grupos_de_recursos_asignados(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_cant_grupos_de_recursos_asignados(idplantilla character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$
    SELECT count(id) FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = $1;
$_$;


ALTER FUNCTION public.dame_cant_grupos_de_recursos_asignados(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 77 (class 1255 OID 26268)
-- Dependencies: 10
-- Name: dame_cant_lugares_asignados(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_cant_lugares_asignados(idplantilla character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$
    SELECT count(lugar) FROM lugar_para_actividad WHERE plantilla = $1;
$_$;


ALTER FUNCTION public.dame_cant_lugares_asignados(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 78 (class 1255 OID 26269)
-- Dependencies: 10
-- Name: dame_cant_otros_recursos_asignados(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_cant_otros_recursos_asignados(idgrupo character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$
    SELECT count(otro_recurso) FROM otro_recurso_para_actividad WHERE grupo_de_recursos = $1;
$_$;


ALTER FUNCTION public.dame_cant_otros_recursos_asignados(idgrupo character varying) OWNER TO rafa;

--
-- TOC entry 92 (class 1255 OID 26270)
-- Dependencies: 10 735
-- Name: dame_cant_otros_recursos_imprescindibles(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_cant_otros_recursos_imprescindibles(idplantilla character varying) RETURNS bigint
    LANGUAGE plpgsql
    AS $_$
	declare r record;
	begin
		SELECT sum(otro_grupo_de_recursos_para_actividad.necesarios) as s INTO r FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = $1 AND imprescindibles = TRUE;
		IF NOT FOUND OR r.s IS NULL THEN
			return 0;
		END IF;
		return r.s;
	end;
$_$;


ALTER FUNCTION public.dame_cant_otros_recursos_imprescindibles(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 79 (class 1255 OID 26271)
-- Dependencies: 10
-- Name: dame_cant_profes_asignados(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_cant_profes_asignados(idplantilla character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$
    SELECT count(id) FROM profesor_para_actividad WHERE plantilla = $1;
$_$;


ALTER FUNCTION public.dame_cant_profes_asignados(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 225 (class 1255 OID 26272)
-- Dependencies: 735 10
-- Name: dame_cant_rest_asignatura(character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_cant_rest_asignatura(idasig character varying, idperiodo character varying) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
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
  
$$;


ALTER FUNCTION public.dame_cant_rest_asignatura(idasig character varying, idperiodo character varying) OWNER TO rafa;

--
-- TOC entry 80 (class 1255 OID 26273)
-- Dependencies: 735 10
-- Name: dame_cant_rest_profe(character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_cant_rest_profe(idprofe character varying, idperiodo character varying) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
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
  
$$;


ALTER FUNCTION public.dame_cant_rest_profe(idprofe character varying, idperiodo character varying) OWNER TO rafa;

--
-- TOC entry 81 (class 1255 OID 26274)
-- Dependencies: 10
-- Name: dame_cant_turnos(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_cant_turnos(idperiodo character varying) RETURNS integer
    LANGUAGE sql
    AS $_$
    SELECT cant_turnos FROM periodo WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_cant_turnos(idperiodo character varying) OWNER TO rafa;

--
-- TOC entry 82 (class 1255 OID 26275)
-- Dependencies: 10
-- Name: dame_capacidad(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_capacidad(lug character varying) RETURNS integer
    LANGUAGE sql
    AS $_$
    SELECT capacidad FROM lugar WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_capacidad(lug character varying) OWNER TO rafa;

--
-- TOC entry 83 (class 1255 OID 26276)
-- Dependencies: 735 10
-- Name: dame_clasif_de_asignaturas(character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_clasif_de_asignaturas(asignatura character varying, periodo character varying) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.dame_clasif_de_asignaturas(asignatura character varying, periodo character varying) OWNER TO rafa;

--
-- TOC entry 84 (class 1255 OID 26277)
-- Dependencies: 10
-- Name: dame_clasif_plantilla_de_actividad(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_clasif_plantilla_de_actividad(character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT clasificacion FROM plantilla_de_actividad WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_clasif_plantilla_de_actividad(character varying) OWNER TO rafa;

--
-- TOC entry 85 (class 1255 OID 26278)
-- Dependencies: 10
-- Name: dame_clasificacion_de_grupo(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_clasificacion_de_grupo(idgrupo character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT clasificacion FROM grupo WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_clasificacion_de_grupo(idgrupo character varying) OWNER TO rafa;

--
-- TOC entry 86 (class 1255 OID 26279)
-- Dependencies: 10 735
-- Name: dame_costo_trasladar_recurso(character varying, character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_costo_trasladar_recurso(idrecurso character varying, idlugar character varying, idperiodo character varying, pdia integer, pturno integer) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.dame_costo_trasladar_recurso(idrecurso character varying, idlugar character varying, idperiodo character varying, pdia integer, pturno integer) OWNER TO rafa;

--
-- TOC entry 87 (class 1255 OID 26280)
-- Dependencies: 10
-- Name: dame_creador_de_asignatura(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_creador_de_asignatura(idasig character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
	select creador from asignatura where id = $1;
$_$;


ALTER FUNCTION public.dame_creador_de_asignatura(idasig character varying) OWNER TO rafa;

--
-- TOC entry 88 (class 1255 OID 26281)
-- Dependencies: 10
-- Name: dame_creador_de_clasifact(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_creador_de_clasifact(idclasif character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
	select creador from clasificacion_de_actividad where id = $1;
$_$;


ALTER FUNCTION public.dame_creador_de_clasifact(idclasif character varying) OWNER TO rafa;

--
-- TOC entry 89 (class 1255 OID 26282)
-- Dependencies: 10
-- Name: dame_creador_de_especialidad(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_creador_de_especialidad(esp character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
	select creador from especialidad where id = $1;
$_$;


ALTER FUNCTION public.dame_creador_de_especialidad(esp character varying) OWNER TO rafa;

--
-- TOC entry 90 (class 1255 OID 26283)
-- Dependencies: 10
-- Name: dame_creador_de_planificacion(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_creador_de_planificacion(id character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT creador FROM planificacion WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_creador_de_planificacion(id character varying) OWNER TO rafa;

--
-- TOC entry 91 (class 1255 OID 26284)
-- Dependencies: 10
-- Name: dame_creador_de_plantilla(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_creador_de_plantilla(idplantilla character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
	select dame_creador_de_asignatura(dame_asignatura_desglose(dame_desglose_de_plantilla($1)));
$_$;


ALTER FUNCTION public.dame_creador_de_plantilla(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 93 (class 1255 OID 26285)
-- Dependencies: 10
-- Name: dame_desglose_de_plantilla(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_desglose_de_plantilla(idplantilla character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT desglose FROM plantilla_de_actividad WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_desglose_de_plantilla(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 94 (class 1255 OID 26286)
-- Dependencies: 10
-- Name: dame_dia_real_planificacion(character varying, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_dia_real_planificacion(pid character varying, dia integer) RETURNS bigint
    LANGUAGE sql
    AS $_$
    SELECT fecha_inicial + $2 - 1  FROM planificacion WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_dia_real_planificacion(pid character varying, dia integer) OWNER TO rafa;

--
-- TOC entry 95 (class 1255 OID 26287)
-- Dependencies: 10
-- Name: dame_duracion_actividad(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_duracion_actividad(id character varying) RETURNS integer
    LANGUAGE sql
    AS $_$
        SELECT dame_duracion_plantilla_de_actividad(plantilla) from actividad where id =$1
    $_$;


ALTER FUNCTION public.dame_duracion_actividad(id character varying) OWNER TO rafa;

--
-- TOC entry 96 (class 1255 OID 26288)
-- Dependencies: 10
-- Name: dame_duracion_clasif_de_actividad(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_duracion_clasif_de_actividad(character varying) RETURNS integer
    LANGUAGE sql
    AS $_$
    SELECT duracion FROM clasificacion_de_actividad WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_duracion_clasif_de_actividad(character varying) OWNER TO rafa;

--
-- TOC entry 97 (class 1255 OID 26289)
-- Dependencies: 10
-- Name: dame_duracion_plantilla_de_actividad(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_duracion_plantilla_de_actividad(character varying) RETURNS integer
    LANGUAGE sql
    AS $_$
    SELECT dame_duracion_clasif_de_actividad(clasificacion) FROM plantilla_de_actividad WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_duracion_plantilla_de_actividad(character varying) OWNER TO rafa;

--
-- TOC entry 98 (class 1255 OID 26290)
-- Dependencies: 10
-- Name: dame_duracion_turno_periodo(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_duracion_turno_periodo(pid character varying) RETURNS double precision
    LANGUAGE sql
    AS $_$
        SELECT duracion_del_turno FROM periodo WHERE id = $1;
    $_$;


ALTER FUNCTION public.dame_duracion_turno_periodo(pid character varying) OWNER TO rafa;

--
-- TOC entry 99 (class 1255 OID 26291)
-- Dependencies: 10
-- Name: dame_especialidad_de_asignatura(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_especialidad_de_asignatura(idasig character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT especialidad FROM asignatura WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_especialidad_de_asignatura(idasig character varying) OWNER TO rafa;

--
-- TOC entry 100 (class 1255 OID 26292)
-- Dependencies: 10
-- Name: dame_especialidad_de_brigada(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_especialidad_de_brigada(idbrigada character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT especialidad FROM brigada WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_especialidad_de_brigada(idbrigada character varying) OWNER TO rafa;

--
-- TOC entry 101 (class 1255 OID 26293)
-- Dependencies: 10
-- Name: dame_especialidad_de_grupo(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_especialidad_de_grupo(idgrupo character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
	select especialidad from grupo where id = $1;
$_$;


ALTER FUNCTION public.dame_especialidad_de_grupo(idgrupo character varying) OWNER TO rafa;

--
-- TOC entry 102 (class 1255 OID 26294)
-- Dependencies: 10
-- Name: dame_gasto_de_plantilla(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_gasto_de_plantilla(idplantilla character varying) RETURNS integer
    LANGUAGE sql
    AS $_$
    SELECT gasto FROM clasificacion_de_actividad WHERE id = dame_clasif_plantilla_de_actividad($1);
$_$;


ALTER FUNCTION public.dame_gasto_de_plantilla(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 103 (class 1255 OID 26295)
-- Dependencies: 10 735
-- Name: dame_gasto_lugares_en_el_dia(character varying[], character varying, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_gasto_lugares_en_el_dia(lugares character varying[], periodo character varying, pdia integer) RETURNS bigint[]
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.dame_gasto_lugares_en_el_dia(lugares character varying[], periodo character varying, pdia integer) OWNER TO rafa;

--
-- TOC entry 104 (class 1255 OID 26296)
-- Dependencies: 10
-- Name: dame_grupo(character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_grupo(brg character varying, clasif character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT grupo FROM brigada_de_grupo WHERE brigada = $1 AND dame_clasificacion_de_grupo(grupo) = $2;
$_$;


ALTER FUNCTION public.dame_grupo(brg character varying, clasif character varying) OWNER TO rafa;

--
-- TOC entry 231 (class 1255 OID 26297)
-- Dependencies: 735 10
-- Name: dame_grupos(character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_grupos(idesp character varying, idnivel character varying, idclasif character varying) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        r RECORD;
    BEGIN
        FOR r IN SELECT grupo FROM brigada_de_grupo WHERE dame_especialidad_de_brigada(brigada) = idesp AND dame_nivel_de_brigada(brigada) = idnivel AND dame_clasificacion_de_grupo(grupo) = idclasif GROUP BY grupo LOOP
            arr := array_append(arr,r.grupo);
        END LOOP;
        RETURN arr;
    END;
    
$$;


ALTER FUNCTION public.dame_grupos(idesp character varying, idnivel character varying, idclasif character varying) OWNER TO rafa;

--
-- TOC entry 232 (class 1255 OID 26298)
-- Dependencies: 735 10
-- Name: dame_grupos_de_brigadas(character varying[], character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_grupos_de_brigadas(brigadas character varying[], idplantilla character varying) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
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
$$;


ALTER FUNCTION public.dame_grupos_de_brigadas(brigadas character varying[], idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 1932 (class 1259 OID 26299)
-- Dependencies: 10
-- Name: grupo; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE grupo (
    id character varying NOT NULL,
    clasificacion character varying NOT NULL,
    descripcion character varying NOT NULL,
    especialidad character varying NOT NULL,
    nivel character varying NOT NULL,
    planificacion character varying NOT NULL
);


ALTER TABLE public.grupo OWNER TO rafa;

--
-- TOC entry 105 (class 1255 OID 26305)
-- Dependencies: 551 10 735
-- Name: dame_grupos_de_plantilla(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_grupos_de_plantilla(idplantilla character varying) RETURNS SETOF grupo
    LANGUAGE plpgsql
    AS $$
	declare r record;
		asig varchar;
	begin
		asig := dame_asignatura_plantilla_de_actividad(idplantilla);
		
		for r in select * from grupo 
			where clasificacion = dame_clasif_plantilla_de_actividad(idplantilla)
			and planificacion = dame_planificacion_de_periodo(dame_periodo_plantilla_de_actividad(idplantilla))
			and especialidad = dame_especialidad_de_asignatura(asig)
			and nivel = dame_nivel_de_asignatura(asig) loop

			return next r;
		end loop;
		
		return;
	end;
$$;


ALTER FUNCTION public.dame_grupos_de_plantilla(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 106 (class 1255 OID 26306)
-- Dependencies: 10 735
-- Name: dame_hora_inicial_turno(character varying, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_hora_inicial_turno(idperiodo character varying, pturno integer) RETURNS double precision
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.dame_hora_inicial_turno(idperiodo character varying, pturno integer) OWNER TO rafa;

--
-- TOC entry 107 (class 1255 OID 26307)
-- Dependencies: 10
-- Name: dame_id_plantilla(character varying, character varying, bigint); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_id_plantilla(idasig character varying, idperiodo character varying, orden bigint) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT plantilla_de_actividad.id FROM plantilla_de_actividad INNER JOIN desglose ON plantilla_de_actividad.desglose = desglose.id WHERE desglose.asignatura = $1 AND desglose.periodo = $2 AND plantilla_de_actividad.orden = $3;
$_$;


ALTER FUNCTION public.dame_id_plantilla(idasig character varying, idperiodo character varying, orden bigint) OWNER TO rafa;

--
-- TOC entry 108 (class 1255 OID 26308)
-- Dependencies: 10
-- Name: dame_idobjeto_de_restriccion_de_brigada(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_idobjeto_de_restriccion_de_brigada(pid character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
            SELECT brigada FROM restriccion_de_brigada WHERE id = $1;
       $_$;


ALTER FUNCTION public.dame_idobjeto_de_restriccion_de_brigada(pid character varying) OWNER TO rafa;

--
-- TOC entry 109 (class 1255 OID 26309)
-- Dependencies: 10
-- Name: dame_idobjeto_de_restriccion_de_especialidad(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_idobjeto_de_restriccion_de_especialidad(pid character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
            SELECT especialidad FROM restriccion_de_especialidad WHERE id = $1;
       $_$;


ALTER FUNCTION public.dame_idobjeto_de_restriccion_de_especialidad(pid character varying) OWNER TO rafa;

--
-- TOC entry 110 (class 1255 OID 26310)
-- Dependencies: 10
-- Name: dame_idobjeto_de_restriccion_de_periodo(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_idobjeto_de_restriccion_de_periodo(pid character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
            SELECT periodo FROM restriccion_de_periodo WHERE id = $1;
       $_$;


ALTER FUNCTION public.dame_idobjeto_de_restriccion_de_periodo(pid character varying) OWNER TO rafa;

--
-- TOC entry 111 (class 1255 OID 26311)
-- Dependencies: 10 735
-- Name: dame_lista_id_brigadas_de_esp(character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_lista_id_brigadas_de_esp(esp character varying, niv character varying) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM brigada WHERE especialidad = esp AND nivel = niv LOOP
            arr := array_append(arr,r.id);
        END LOOP;

        RETURN arr;
    END;
$$;


ALTER FUNCTION public.dame_lista_id_brigadas_de_esp(esp character varying, niv character varying) OWNER TO rafa;

--
-- TOC entry 112 (class 1255 OID 26312)
-- Dependencies: 10 735
-- Name: dame_lista_id_brigadas_de_grupo(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_lista_id_brigadas_de_grupo(pgrupo character varying) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        r RECORD;
    BEGIN
        FOR r IN SELECT brigada FROM brigada_de_grupo WHERE grupo = pgrupo LOOP
            arr := array_append(arr,r.brigada);
        END LOOP;
        RETURN arr;
    END;
    
$$;


ALTER FUNCTION public.dame_lista_id_brigadas_de_grupo(pgrupo character varying) OWNER TO rafa;

--
-- TOC entry 113 (class 1255 OID 26313)
-- Dependencies: 10 735
-- Name: dame_lista_id_clasif(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_lista_id_clasif() RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM clasificacion_de_actividad ORDER BY prioridad LOOP
            arr := array_append(arr, r.id);
        END LOOP;
        RETURN arr;
    END;
$$;


ALTER FUNCTION public.dame_lista_id_clasif() OWNER TO rafa;

--
-- TOC entry 114 (class 1255 OID 26314)
-- Dependencies: 735 10
-- Name: dame_lista_id_grupos_de_recursos_asignados(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_lista_id_grupos_de_recursos_asignados(idplantilla character varying) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = idplantilla LOOP
            arr := array_append(arr,r.id);
        END LOOP;

        RETURN arr;
    END;
$$;


ALTER FUNCTION public.dame_lista_id_grupos_de_recursos_asignados(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 115 (class 1255 OID 26315)
-- Dependencies: 10 735
-- Name: dame_lista_id_lugares_asignados(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_lista_id_lugares_asignados(idplantilla character varying) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM lugar_para_actividad WHERE plantilla = idplantilla LOOP
            arr := array_append(arr,r.lugar);
        END LOOP;

        RETURN arr;
    END;
$$;


ALTER FUNCTION public.dame_lista_id_lugares_asignados(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 116 (class 1255 OID 26316)
-- Dependencies: 10 735
-- Name: dame_lista_id_otros_recursos_asignados(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_lista_id_otros_recursos_asignados(idgrupo character varying) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM otro_recurso_para_actividad WHERE grupo_de_recursos = idgrupo LOOP
            arr := array_append(arr,r.otro_recurso);
        END LOOP;

        RETURN arr;
    END;
$$;


ALTER FUNCTION public.dame_lista_id_otros_recursos_asignados(idgrupo character varying) OWNER TO rafa;

--
-- TOC entry 242 (class 1255 OID 36223)
-- Dependencies: 735 10
-- Name: dame_lista_id_otros_recursos_usados(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dame_lista_id_otros_recursos_usados(idactividad character varying) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM otros_recursos_usados WHERE actividad = idactividad = idgrupo LOOP
            arr := array_append(arr,r.otro_recurso);
        END LOOP;

        RETURN arr;
    END;
$$;


ALTER FUNCTION public.dame_lista_id_otros_recursos_usados(idactividad character varying) OWNER TO postgres;

--
-- TOC entry 117 (class 1255 OID 26317)
-- Dependencies: 735 10
-- Name: dame_lista_id_profes_asignados(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_lista_id_profes_asignados(idplantilla character varying) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM profesor_para_actividad WHERE plantilla = idplantilla LOOP
            arr := array_append(arr,r.profesor);
        END LOOP;

        RETURN arr;
    END;
$$;


ALTER FUNCTION public.dame_lista_id_profes_asignados(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 238 (class 1255 OID 26318)
-- Dependencies: 735 10
-- Name: dame_lista_id_profes_asignados_al_grupo(character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_lista_id_profes_asignados_al_grupo(idplantilla character varying, idgrupo character varying) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        r record;
    BEGIN
        FOR r IN SELECT * FROM profesor_para_actividad 
		WHERE plantilla = idplantilla AND EXISTS(SELECT * FROM profesor_para_grupo 
							  WHERE profesor_para_grupo.profesor_para_actividad = profesor_para_actividad.id 
							  AND profesor_para_grupo.grupo = idgrupo) ORDER BY prioridad 
	LOOP
            arr := array_append(arr,r.profesor);
        END LOOP;

        RETURN arr;
    END;
$$;


ALTER FUNCTION public.dame_lista_id_profes_asignados_al_grupo(idplantilla character varying, idgrupo character varying) OWNER TO rafa;

--
-- TOC entry 1933 (class 1259 OID 26319)
-- Dependencies: 2301 2302 10
-- Name: lugar; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE lugar (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'lugar'::character varying, 'id'::character varying, 'lugar'::character varying) NOT NULL,
    nombre character varying,
    capacidad integer DEFAULT 0,
    creador character varying NOT NULL
);


ALTER TABLE public.lugar OWNER TO rafa;

--
-- TOC entry 118 (class 1255 OID 26327)
-- Dependencies: 735 10 554
-- Name: dame_lugares_no_asignados_a(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_lugares_no_asignados_a(idplantilla character varying) RETURNS SETOF lugar
    LANGUAGE plpgsql
    AS $$
	declare pcreador varchar;
		r record;
		rr record;
	begin
		pcreador := dame_creador_de_plantilla(idplantilla);

		for r in select * from lugar where creador = pcreador loop
			select * into rr from lugar_para_actividad where plantilla = idplantilla and lugar = r.id;
			if not found then
				return next r;
			end if;
		end loop;

		return;
		
	end;
$$;


ALTER FUNCTION public.dame_lugares_no_asignados_a(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 235 (class 1255 OID 26328)
-- Dependencies: 10 735
-- Name: dame_lugares_usados_alrededor_de(character varying[], character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_lugares_usados_alrededor_de(brigadas character varying[], idperiodo character varying, pdia integer, pturno integer) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        cantbrg BIGINT;
        i BIGINT;
        j INTEGER;
        rperiodo record;
        r record;
    BEGIN
        SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;
        
        cantbrg := matrix.get_length_of(brigadas,1);

        i := 0;
        LOOP
            -- buscando el anterior
            j := pturno - 1;
            IF j > 0 THEN
		    LOOP
			SELECT * INTO r FROM actividad WHERE idperiodo = dame_periodo_plantilla_de_actividad(plantilla) AND dia = pdia AND turno = j AND grupo = dame_grupo(brigadas[i], dame_clasif_plantilla_de_actividad(plantilla));

			IF FOUND THEN
			    arr := array_append(arr, r.lugar);
			    EXIT;
			END IF;
			
			EXIT WHEN j <= 1;
			j :=j - 1;
		    END LOOP;
	    END IF;
            -- buscando el siguiente
            j := pturno + 1;
            IF j <= rperiodo.cant_turnos THEN
		    LOOP
			SELECT * INTO r FROM actividad WHERE idperiodo = dame_periodo_plantilla_de_actividad(plantilla) AND dia = pdia AND turno = j AND grupo = dame_grupo(brigadas[i], dame_clasif_plantilla_de_actividad(plantilla));

			IF FOUND THEN
			    arr := array_append(arr, r.lugar);
			    EXIT;
			END IF;
			
			EXIT WHEN j >= rperiodo.cant_turnos;
			j := j + 1;
		    END LOOP;
            END IF;
            
            EXIT WHEN i = cantbrg;
            i := i + 1;
        END LOOP;
        
        RETURN arr;
    END;
$$;


ALTER FUNCTION public.dame_lugares_usados_alrededor_de(brigadas character varying[], idperiodo character varying, pdia integer, pturno integer) OWNER TO rafa;

--
-- TOC entry 119 (class 1255 OID 26329)
-- Dependencies: 10 735
-- Name: dame_lugares_utilizados_en_el_dia(character varying, character varying, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_lugares_utilizados_en_el_dia(pgrupo character varying, periodo character varying, pdia integer) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE
        arr VARCHAR[];
        r record;
        
    BEGIN
        FOR r IN SELECT * FROM actividades WHERE grupo = pgrupo AND dia = pdia AND dame_periodo_de_plantilla(plantilla) = periodo LOOP
            arr := array_append (arr,r.lugar);
        END LOOP;
        
        RETURN arr;
    END;
$$;


ALTER FUNCTION public.dame_lugares_utilizados_en_el_dia(pgrupo character varying, periodo character varying, pdia integer) OWNER TO rafa;

--
-- TOC entry 120 (class 1255 OID 26330)
-- Dependencies: 10
-- Name: dame_matricula(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_matricula(brg character varying) RETURNS integer
    LANGUAGE sql
    AS $_$
    SELECT matricula FROM brigada WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_matricula(brg character varying) OWNER TO rafa;

--
-- TOC entry 121 (class 1255 OID 26331)
-- Dependencies: 10 735
-- Name: dame_matricula_total(character varying[]); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_matricula_total(brigadas character varying[]) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.dame_matricula_total(brigadas character varying[]) OWNER TO rafa;

--
-- TOC entry 122 (class 1255 OID 26332)
-- Dependencies: 10
-- Name: dame_max_orden_act(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_max_orden_act(iddesglose character varying) RETURNS integer
    LANGUAGE sql
    AS $_$ 
	select max(orden) from plantilla_de_actividad where desglose = $1;
$_$;


ALTER FUNCTION public.dame_max_orden_act(iddesglose character varying) OWNER TO rafa;

--
-- TOC entry 124 (class 1255 OID 26333)
-- Dependencies: 735 10
-- Name: dame_max_priori_clasif_act(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_max_priori_clasif_act(pcreador character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
    DECLARE r record;
    BEGIN
        
        SELECT max(prioridad) as m into r FROM clasificacion_de_actividad WHERE creador='admin-superuser' GROUP BY creador;

        IF r.m is null THEN
            RETURN 1;
        END IF;
        
        RETURN r.m;
    END;
$$;


ALTER FUNCTION public.dame_max_priori_clasif_act(pcreador character varying) OWNER TO rafa;

--
-- TOC entry 200 (class 1255 OID 34368)
-- Dependencies: 10
-- Name: dame_max_priori_otro_recurso(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dame_max_priori_otro_recurso(idgr character varying) RETURNS integer
    LANGUAGE sql
    AS $_$
	select max(prioridad) from otro_recurso_para_actividad where grupo_de_recursos = $1;
$_$;


ALTER FUNCTION public.dame_max_priori_otro_recurso(idgr character varying) OWNER TO postgres;

--
-- TOC entry 125 (class 1255 OID 26334)
-- Dependencies: 10
-- Name: dame_max_prioridad_de_pxact(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_max_prioridad_de_pxact(idplantilla character varying) RETURNS integer
    LANGUAGE sql
    AS $_$
	select max(prioridad) from profesor_para_actividad where plantilla = $1;
$_$;


ALTER FUNCTION public.dame_max_prioridad_de_pxact(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 126 (class 1255 OID 26335)
-- Dependencies: 10
-- Name: dame_max_prioridad_lxact(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_max_prioridad_lxact(idplantilla character varying) RETURNS integer
    LANGUAGE sql
    AS $_$
	select max(prioridad) from lugar_para_actividad where plantilla = $1;
$_$;


ALTER FUNCTION public.dame_max_prioridad_lxact(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 127 (class 1255 OID 26336)
-- Dependencies: 10
-- Name: dame_max_prioridad_rxact(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_max_prioridad_rxact(idplantilla character varying) RETURNS integer
    LANGUAGE sql
    AS $_$
	select max(prioridad) from otro_grupo_de_recursos_para_actividad where plantilla = $1;
$_$;


ALTER FUNCTION public.dame_max_prioridad_rxact(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 128 (class 1255 OID 26337)
-- Dependencies: 10
-- Name: dame_nivel_de_asignatura(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_nivel_de_asignatura(idasig character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT nivel FROM asignatura WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_nivel_de_asignatura(idasig character varying) OWNER TO rafa;

--
-- TOC entry 129 (class 1255 OID 26338)
-- Dependencies: 10
-- Name: dame_nivel_de_brigada(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_nivel_de_brigada(idbrigada character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT nivel FROM brigada WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_nivel_de_brigada(idbrigada character varying) OWNER TO rafa;

--
-- TOC entry 130 (class 1255 OID 26339)
-- Dependencies: 10
-- Name: dame_nivel_de_grupo(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_nivel_de_grupo(idgrupo character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
	select nivel from grupo where id = $1;
$_$;


ALTER FUNCTION public.dame_nivel_de_grupo(idgrupo character varying) OWNER TO rafa;

--
-- TOC entry 1953 (class 1259 OID 26583)
-- Dependencies: 2356 10
-- Name: otro_recurso; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE otro_recurso (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'otro_recurso'::character varying, 'id'::character varying, 'otro_recurso'::character varying) NOT NULL,
    nombre character varying,
    creador character varying NOT NULL
);


ALTER TABLE public.otro_recurso OWNER TO rafa;

--
-- TOC entry 199 (class 1255 OID 34364)
-- Dependencies: 613 735 10
-- Name: dame_otros_recursos_sin_asignar(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dame_otros_recursos_sin_asignar(idplantilla character varying) RETURNS SETOF otro_recurso
    LANGUAGE plpgsql
    AS $_$ 
	declare r record;
	begin

	for r in select * from otro_recurso where esta_otro_recurso_asignado(id, $1) = false loop
		return next r;
	end loop;
	end;
$_$;


ALTER FUNCTION public.dame_otros_recursos_sin_asignar(idplantilla character varying) OWNER TO postgres;

--
-- TOC entry 131 (class 1255 OID 26340)
-- Dependencies: 10
-- Name: dame_periodo_desglose(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_periodo_desglose(character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT periodo FROM desglose WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_periodo_desglose(character varying) OWNER TO rafa;

--
-- TOC entry 132 (class 1255 OID 26341)
-- Dependencies: 10
-- Name: dame_periodo_plantilla_de_actividad(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_periodo_plantilla_de_actividad(character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT dame_periodo_desglose(desglose) FROM plantilla_de_actividad WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_periodo_plantilla_de_actividad(character varying) OWNER TO rafa;

--
-- TOC entry 133 (class 1255 OID 26342)
-- Dependencies: 10
-- Name: dame_planificacion_de_grupo(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_planificacion_de_grupo(idgrupo character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
	select planificacion from grupo where id = $1;
$_$;


ALTER FUNCTION public.dame_planificacion_de_grupo(idgrupo character varying) OWNER TO rafa;

--
-- TOC entry 134 (class 1255 OID 26343)
-- Dependencies: 10
-- Name: dame_planificacion_de_periodo(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_planificacion_de_periodo(pid character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT planificacion FROM periodo WHERE id = $1;
$_$;


ALTER FUNCTION public.dame_planificacion_de_periodo(pid character varying) OWNER TO rafa;

--
-- TOC entry 197 (class 1255 OID 34363)
-- Dependencies: 10
-- Name: dame_plantilla_de_grupo_de_recursos(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dame_plantilla_de_grupo_de_recursos(idgrupo character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
	select plantilla from otro_grupo_de_recursos_para_actividad where id = $1;
$_$;


ALTER FUNCTION public.dame_plantilla_de_grupo_de_recursos(idgrupo character varying) OWNER TO postgres;

--
-- TOC entry 135 (class 1255 OID 26344)
-- Dependencies: 10 735
-- Name: dame_profesores_no_asignados_a(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_profesores_no_asignados_a(idplantilla character varying) RETURNS SETOF character varying
    LANGUAGE plpgsql
    AS $$
	declare pcreador varchar;
		r record;
		rr record;
	begin
		pcreador := dame_creador_de_plantilla(idplantilla);

		for r in select * from profesor where creador = pcreador order by nombre loop
			select * into rr from profesor_para_actividad where plantilla = idplantilla and profesor = r.id;
			if not found then
				return next r.id;
			end if;
		end loop;

		return;
		
	end;
$$;


ALTER FUNCTION public.dame_profesores_no_asignados_a(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 136 (class 1255 OID 26345)
-- Dependencies: 10 735
-- Name: dame_tipo_consec_rest_cond(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_tipo_consec_rest_cond(pid character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.dame_tipo_consec_rest_cond(pid character varying) OWNER TO rafa;

--
-- TOC entry 137 (class 1255 OID 26346)
-- Dependencies: 735 10
-- Name: dame_tipo_hijo_hrt(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_tipo_hijo_hrt(pid character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.dame_tipo_hijo_hrt(pid character varying) OWNER TO rafa;

--
-- TOC entry 138 (class 1255 OID 26347)
-- Dependencies: 735 10
-- Name: dame_tipo_padre_hrt(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_tipo_padre_hrt(pid character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.dame_tipo_padre_hrt(pid character varying) OWNER TO rafa;

--
-- TOC entry 229 (class 1255 OID 26348)
-- Dependencies: 10 735
-- Name: dame_total_otros_recursos_asignados(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_total_otros_recursos_asignados(idplantilla character varying) RETURNS numeric
    LANGUAGE plpgsql
    AS $_$
	declare r record;
	begin
		 SELECT sum(dame_cant_otros_recursos_asignados(id)) as s INTO r FROM otro_grupo_de_recursos_para_actividad WHERE plantilla = $1;
		IF NOT FOUND OR r.s IS NULL THEN
			return 0;
		END IF;
		return r.s;
	end;
$_$;


ALTER FUNCTION public.dame_total_otros_recursos_asignados(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 139 (class 1255 OID 26349)
-- Dependencies: 10
-- Name: dame_uso_de_otro_recurso(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_uso_de_otro_recurso(id character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$
    SELECT count(actividad) FROM otros_recursos_usados WHERE otro_recurso = $1
$_$;


ALTER FUNCTION public.dame_uso_de_otro_recurso(id character varying) OWNER TO rafa;

--
-- TOC entry 230 (class 1255 OID 26350)
-- Dependencies: 10 735
-- Name: dame_zpriori(character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dame_zpriori(clasif character varying, idperiodo character varying) RETURNS integer[]
    LANGUAGE plpgsql
    AS $$
    DECLARE zp INTEGER[][];
        i BIGINT; j BIGINT;
        CD INTEGER; CT INTEGER;
        r record;
    BEGIN 
        
        CD := dame_cant_dias(idperiodo);
        CT := dame_cant_turnos(idperiodo);
        
        zp := matrix.create_numeric_matrix(CD,CT);

	i:= 1;
	LOOP
		j:=1;
		LOOP
			zp[i][j] := 1;
			EXIT WHEN j = CT;
			j:=j+1;
		END LOOP;
		EXIT WHEN i = CD;
		i := i + 1;
	END LOOP;
    
        FOR r IN SELECT * FROM zpriori WHERE clasificacion = clasif AND periodo = idperiodo LOOP
            zp[r.dia][r.turno] := r.valor;
        END LOOP;
        
        RETURN zp;
    END;
$$;


ALTER FUNCTION public.dame_zpriori(clasif character varying, idperiodo character varying) OWNER TO rafa;

--
-- TOC entry 237 (class 1255 OID 26351)
-- Dependencies: 735 10
-- Name: dia_minimo_impacto(character varying, character varying, character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dia_minimo_impacto(idclasif character varying, idesp character varying, idnivel character varying, idperiodo character varying, dia_ini integer, dia_fin integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
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

        CD := rperiodo.cant_dias;
        CT := rperiodo.cant_turnos;
        
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
        FOR r IN SELECT * FROM restriccion_de_brigada WHERE dame_especialidad_de_brigada(brigada) = idesp AND dame_nivel_de_brigada(brigada) = idnivel LOOP
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
$$;


ALTER FUNCTION public.dia_minimo_impacto(idclasif character varying, idesp character varying, idnivel character varying, idperiodo character varying, dia_ini integer, dia_fin integer) OWNER TO rafa;

--
-- TOC entry 140 (class 1255 OID 26352)
-- Dependencies: 10 735
-- Name: dias_se_solapan(character varying, integer, character varying, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION dias_se_solapan(idperiodo1 character varying, dia1 integer, idperiodo2 character varying, dia2 integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
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
    $$;


ALTER FUNCTION public.dias_se_solapan(idperiodo1 character varying, dia1 integer, idperiodo2 character varying, dia2 integer) OWNER TO rafa;

--
-- TOC entry 150 (class 1255 OID 26353)
-- Dependencies: 10 735
-- Name: es_higienico_brigada(character varying, character varying, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION es_higienico_brigada(idbrigada character varying, idplantilla character varying, pdia integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
    DECLARE r record;
        r1 record;
        h record;
        idperiodo VARCHAR;
        rclasif VARCHAR;
        gasto INTEGER;
    BEGIN
        idperiodo := dame_periodo_plantilla_de_actividad(idplantilla);
        gasto := dame_gasto_de_plantilla(idplantilla);

        FOR r1 IN SELECT * FROM actividad WHERE dame_periodo_plantilla_de_actividad(plantilla) = idperiodo AND dia = pdia AND matrix.get_pos_of(dame_lista_id_brigadas_de_grupo(grupo), idbrigada) > -1 LOOP
            gasto := gasto + dame_gasto_de_plantilla(idplantilla);            
        END LOOP;

        SELECT * INTO h FROM higiene_para_brigada WHERE periodo = idperiodo and brigada = idbrigada;

        IF FOUND THEN
            IF h.gasto_maximo_diario < gasto THEN
                RETURN FALSE;
            END IF;
        END IF;
        
        RETURN TRUE;
    END;
$$;


ALTER FUNCTION public.es_higienico_brigada(idbrigada character varying, idplantilla character varying, pdia integer) OWNER TO rafa;

--
-- TOC entry 148 (class 1255 OID 26354)
-- Dependencies: 735 10
-- Name: es_higienico_profesor(character varying, character varying, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION es_higienico_profesor(idprofesor character varying, idplantilla character varying, pdia integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
    DECLARE r record;
        r1 record;
        h record;
        idperiodo VARCHAR;
        rclasif VARCHAR;
        gasto INTEGER;
    BEGIN
        idperiodo := dame_periodo_plantilla_de_actividad(idplantilla);
        gasto := dame_gasto_de_plantilla(idplantilla);

        FOR r1 IN SELECT * FROM actividad WHERE dame_periodo_plantilla_de_actividad(plantilla) = idperiodo AND dia = pdia AND profesor = idprofesor LOOP
            gasto := gasto + dame_gasto_de_plantilla(idplantilla);            
        END LOOP;

        SELECT * INTO h FROM higiene_para_profesor WHERE periodo = idperiodo and profesor = idprofesor;

        IF FOUND THEN
            IF h.gasto_maximo_diario < gasto THEN
                RETURN FALSE;
            END IF;
        END IF;
        
        RETURN TRUE;
    END;
$$;


ALTER FUNCTION public.es_higienico_profesor(idprofesor character varying, idplantilla character varying, pdia integer) OWNER TO rafa;

--
-- TOC entry 141 (class 1255 OID 26355)
-- Dependencies: 735 10
-- Name: es_lugar_prohibido(character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION es_lugar_prohibido(pgrupo character varying, plugar character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
    DECLARE r record;
    BEGIN
        SELECT * INTO r FROM acceso_prohibido WHERE grupo = pgrupo AND lugar = plugar;
        IF r.grupo = pgrupo THEN
            RETURN TRUE;
        END IF;
        RETURN FALSE;
    END;
$$;


ALTER FUNCTION public.es_lugar_prohibido(pgrupo character varying, plugar character varying) OWNER TO rafa;

--
-- TOC entry 142 (class 1255 OID 26356)
-- Dependencies: 10 735
-- Name: esta_dia_en_rango_planificacion(integer, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION esta_dia_en_rango_planificacion(pdia integer, idperiodo character varying, dia_ini integer, dia_fin integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
    DECLARE r record;
    BEGIN
        SELECT * into r from periodicidad where periodo = idperiodo and (dia+pdia >=dia_ini AND dia+pdia<=dia_fin);
        IF FOUND THEN
            RETURN TRUE;
        END IF;
        
        RETURN FALSE;
    END;
$$;


ALTER FUNCTION public.esta_dia_en_rango_planificacion(pdia integer, idperiodo character varying, dia_ini integer, dia_fin integer) OWNER TO rafa;

--
-- TOC entry 198 (class 1255 OID 34367)
-- Dependencies: 735 10
-- Name: esta_otro_recurso_asignado(character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION esta_otro_recurso_asignado(idrecurso character varying, idplantilla character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
	declare r record;
	begin
		select * into r from otro_recurso_para_actividad 
		where dame_plantilla_de_grupo_de_recursos(grupo_de_recursos) = idplantilla
		and otro_recurso = idrecurso;

		if found then
			return true;
		end if;

		return false;
	end;
$$;


ALTER FUNCTION public.esta_otro_recurso_asignado(idrecurso character varying, idplantilla character varying) OWNER TO postgres;

--
-- TOC entry 143 (class 1255 OID 26357)
-- Dependencies: 735 10
-- Name: existe_grupo_de_profesor(character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION existe_grupo_de_profesor(profe_para_act character varying, pgrupo character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
    DECLARE r record;
    BEGIN
        SELECT * INTO r FROM profesor_para_grupo WHERE profesor_para_actividad = profe_para_act AND grupo = pgrupo;    
        IF r.grupo = pgrupo THEN
            RETURN TRUE;
        END IF;
        RETURN FALSE;
    END;
$$;


ALTER FUNCTION public.existe_grupo_de_profesor(profe_para_act character varying, pgrupo character varying) OWNER TO rafa;

--
-- TOC entry 149 (class 1255 OID 26358)
-- Dependencies: 735 10
-- Name: extraer_lugares_que_no_permanecen(character varying[], character varying, integer, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION extraer_lugares_que_no_permanecen(lugares character varying[], periodo character varying, dia integer, turno integer, duracion integer) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
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
            EXIT WHEN i >= cant;
            i := i + 1;
        END LOOP;
        RETURN arr;
    END;
$$;


ALTER FUNCTION public.extraer_lugares_que_no_permanecen(lugares character varying[], periodo character varying, dia integer, turno integer, duracion integer) OWNER TO rafa;

--
-- TOC entry 144 (class 1255 OID 26359)
-- Dependencies: 10
-- Name: first(anyelement, anyelement); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION first(anyelement, anyelement) RETURNS anyelement
    LANGUAGE sql
    AS $_$SELECT COALESCE($1, $2);$_$;


ALTER FUNCTION public.first(anyelement, anyelement) OWNER TO rafa;

--
-- TOC entry 226 (class 1255 OID 34381)
-- Dependencies: 735 10
-- Name: generar(character varying, character varying, character varying, bigint, boolean, bigint, boolean, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION generar(idesp character varying, idnivel character varying, idperiodo character varying, forma bigint, parche boolean, compactar bigint, parche1 boolean, buscalugar boolean) RETURNS void
    LANGUAGE plpgsql
    AS $$
    DECLARE
	v integer;
	ddia integer;
	tturno integer;
	dduracion integer;

	sePudoColocar boolean;
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
        idxOrdenAct BIGINT[];

        -- contadores
        i BIGINT;
        j BIGINT;
        k INTEGER;
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

        idlugar varchar;
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
    es_higienico_profe BOOLEAN;
    es_higienico_brg BOOLEAN;
	
    BEGIN

        cantCriterios := 9;
        cantActEnCurso := 0;

	RAISE NOTICE '[INFO] Comenzando...';
	
        -- brgsEnCurso := dame_lista_id_brigadas_de_esp(idesp, idnivel);
        -- cantBrgsEnCurso := matrix.get_length_of(brgsEnCurso);
                
        -- aqui se obtienen los ids de las asignaturas implicadas en la generacion
        -- ordenadas segun la cantidad de actividades que tienen en el periodo
        -- especificado "idperiodo"

        RAISE NOTICE '[INFO] Obteniendo listado de asignaturas implicadas...';
        
        asigEnCurso := dame_asig_segun_esp_anno_per(idesp, idnivel, idperiodo);

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------

        cantAsigEnCurso := matrix.get_length_of(asigEnCurso,1 );
        
        CD := dame_cant_dias(idperiodo);
        CT := dame_cant_turnos(idperiodo);
        
        SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;
        CD := rperiodo.cant_dias;
        CT := rperiodo.cant_turnos;

        cantMaxAct := max_cant_act(idesp, idnivel, idperiodo);
        matrixAct := matrix.create_string_matrix(cantAsigEnCurso, cantMaxAct);


        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------

        RAISE NOTICE '[INFO] Preparando matrix de actividades ...';
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
        
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
        
	RAISE NOTICE '[INFO] Cantidad de actividades en curso % ...', cantActEnCurso;
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


        RAISE NOTICE '[INFO] Generando ...';
        -- !!! GENERANDO !!!


        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------    

        LOOP
            varCiclo1 := 0;
            LOOP
		varCiclo1 := varCiclo1 + 1;
		EXIT WHEN varCiclo1 > cantCiclo1;
		
                varCiclo2 :=0;
                LOOP
		    varCiclo2 := varCiclo2 + 1;
		    EXIT WHEN varCiclo2 > cantCiclo2;
		    
                    IF forma = 1 THEN
                         nivel_actual := varCiclo2;
                        clasif_actual := varCiclo1;
                    ELSE
                         nivel_actual := varCiclo1;
                        clasif_actual := varCiclo2;    
                    END IF;


		    RAISE NOTICE '[INFO] Calculando cantidad de actividades a priorizar ...';
                    -- calcular la cantidad de actividades a priorizar

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
        
                    cantPriorizando :=0;
                    i := 1;
                    plantillasEnCurso := '{}'::VARCHAR[];
                    idxAsigEnPriori := '{}'::BIGINT[];
                    LOOP
                        IF matrixAct[i][nivel_actual] = listaid_clasif[clasif_actual] THEN
                            cantPriorizando := cantPriorizando + 1;
                            plantillasEnCurso := array_append(plantillasEnCurso,dame_id_plantilla(asigEnCurso[i], idperiodo, nivel_actual));
                            idxAsigEnPriori := array_append(idxAsigEnPriori,i);
                        END IF;
                        EXIT WHEN i = cantAsigEnCurso;
                        i := i + 1;
                    END LOOP;

		    RAISE NOTICE '[INFO] Llenando la tabla prioriza con % actividades...', cantPriorizando;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
		    CONTINUE WHEN cantPriorizando = 0;
		    
                    -- Llenando la tabla prioriza
                    TablaPrioriza := matrix.create_numeric_matrix(cantPriorizando, 9);

                    i := 1;
                    LOOP
                        -- Cantidad de profesores asignados
                        param_cantProfesAsignados := dame_cant_profes_asignados(plantillasEnCurso[i]);
                        
                        -- Cantidad de restricciones de los profesores asignados
                        profesAsignados := dame_lista_id_profes_asignados(plantillasEnCurso[i]);
                        param_cantRestProfe := 0;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
                        j := 0;
                        LOOP
                            EXIT WHEN j = param_cantProfesAsignados;
                            param_cantRestProfe := param_cantRestProfe + dame_cant_rest_profe(profesAsignados[j], idperiodo);
                            j := j + 1;
                        END LOOP;

                        -- Cantiad de lugares asignados
                        param_cantLugaresAsignados := dame_cant_lugares_asignados(plantillasEnCurso[i]);

                        -- Cantidad de restricciones de los profesores asignados
                        lugaresAsignados := dame_lista_id_lugares_asignados(plantillasEnCurso[i]);
                        param_cantRestLugar := 0;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
                        j := 0;
                        LOOP
                            EXIT WHEN j = param_cantLugaresAsignados;
                            param_cantRestLugar := param_cantRestLugar + dame_cant_rest_profe(lugaresAsignados[j], idperiodo);
                            j := j + 1;
                        END LOOP;

                        -- Cantidad de actividades de la asignatura
                        param_cantActAsig := cantActs[idxAsigEnPriori[i]];
                        
                        -- Cantidad de otros recursos imprescindibles
                        param_cantRecursosImprescindibles := dame_cant_otros_recursos_imprescindibles(plantillasEnCurso[i]);
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------                        
                        -- Cantidad de otros recursos asignados
                        param_cantRecursosAsignados := dame_total_otros_recursos_asignados(plantillasEnCurso[i]);
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------                        
                        -- Cantidad de restricciones de la asignatura
                        param_cantRestAsig := dame_cant_rest_asignatura(asigEnCurso[idxAsigEnPriori[i]], idperiodo);

                        -- Rellenando la tabla
                        
                        TablaPrioriza[i][1] := param_cantProfesAsignados;          
                        TablaPrioriza[i][2] := param_cantRestProfe;
                        TablaPrioriza[i][3] := param_cantLugaresAsignados;
                        TablaPrioriza[i][4] := param_cantRestLugar;
                        TablaPrioriza[i][5] := param_cantActAsig;
                        TablaPrioriza[i][6] := param_cantRecursosImprescindibles;
                        TablaPrioriza[i][7] := param_cantRecursosAsignados;
                        TablaPrioriza[i][8] := param_cantRestAsig;
                        TablaPrioriza[i][9] := 0;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
			RAISE NOTICE '% - % % % % % % % % - %',i,TablaPrioriza[i][1],TablaPrioriza[i][2],TablaPrioriza[i][3],TablaPrioriza[i][4],TablaPrioriza[i][5],TablaPrioriza[i][6],TablaPrioriza[i][7],TablaPrioriza[i][8],TablaPrioriza[i][9];
                        EXIT WHEN i = cantPriorizando;
                        i := i + 1;
                    END LOOP;

		    RAISE NOTICE '[INFO] Tabla obtenida:';
		    EXECUTE matrix.echo(TablaPrioriza);
		    RAISE NOTICE '[INFO] Priorizando actividad a colocar ...';	
		    	

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------		    
                    -- Priorizando la actividad a colocar
                    criterios := '{t,f,t,f,f,f,t,f}'::BOOLEAN[];
                    niveles:= prioriza.nivelar_matrix(TablaPrioriza, criterios);

		    RAISE NOTICE '[INFO] Calculando totales horizontales ...';	
		    
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
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------                    
                    -- Determino el orden de prioridad para colocar las actividades que entraron en juego
                    -- El orden se determina seg??os totales horizontales calculados, de menor a mayor

                    -- Inicializo el arreglo que va a tener el orden
                    
                    idxOrdenAct := '{}'::INTEGER[];

		    RAISE NOTICE '[INFO] Ordenando totales horizontales ...';
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
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
		    RAISE NOTICE '[INFO] Determinando clasificacion siguiente ...';

                    -- Determinando la clasificacion de actividad siguiente
                    
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
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
                    RAISE NOTICE '[INFO] Colocando actividad ...';
                    -- -------------------------------------------
                    -- Colocando la actividad 

                    SELECT * INTO rclasificacion FROM clasificacion_de_actividad WHERE id = listaid_clasif[clasif_actual];
                    SELECT valor INTO r FROM zpriori WHERE clasificacion = listaid_clasif[clasif_actual] AND periodo = idperiodo ORDER By valor DESC LIMIT 1 OFFSET 0;

		    ztodo := r.valor;
            
                    posibXDia := matrix.create_numeric_list(CD);

		    RAISE NOTICE '      [INFO] obteniendo zpriori ...';
		    
                    zp := dame_zpriori(rclasificacion.id, idperiodo);

                    IF ztodo IS NULL THEN
                        ztodo := 1;
                    END IF;

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------		    
                    i := 1;
                                        
                    LOOP   

			RAISE NOTICE '      [INFO] colocando actividad i = % ',i;

			
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------                        
                        SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = plantillasEnCurso[idxOrdenAct[i]];
                        SELECT * INTO rdesglose FROM desglose WHERE id = rplantilla.desglose;
                        SELECT * INTO rasignatura FROM asignatura WHERE id = rdesglose.asignatura;


			maxDia := CD -((cantActs[i] - nivel_actual) - compactar) * IIf(rdesglose.respetar_orden, 1, 0);

                        sentido := 2;
                        dia_anterior := 0;

                        grupos := dame_grupos(idesp, idnivel, rplantilla.clasificacion);
                        
                        cantGrupos := matrix.get_length_of(grupos,1);

                        idMaxPIGrupo := matrix.create_numeric_matrix(cantGrupos,CD);
                        
                        faltan := cantGrupos;

                        gruposEnCurso := matrix.create_boolean_cube(cantGrupos, CD, CT);

                        j := 1;
                        LOOP
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------                        
			    RAISE NOTICE '      [INFO] colocando por grupo j = % ',j;

			    RAISE NOTICE '      [INFO] obteniendo listaidbrg';
                            listaidbrg := dame_lista_id_brigadas_de_grupo(grupos[j]);

			    RAISE NOTICE '      [INFO] obteniendo grupompi';
                            grupompi := and_mpi(listaidbrg,CD,CT,rplantilla.id,0,TRUE);

			    RAISE NOTICE '      [INFO] obteniendo gruposEnCurso';
                            gruposEnCurso := matrix.set_matrix_in_cube(grupompi, j);

                            RAISE NOTICE '      [INFO] and mpi a grupompi';
                            grupompi := and_mpi(listaidbrg,CD,CT,rplantilla.id,0,FALSE);

			    RAISE NOTICE '      [INFO] calculando idmaxpigrupo';

                            k := 1;

                            LOOP
				--RAISE NOTICE '      [INFO] calculando idmaxpigrupo CD = % k = %', CD, k;
				--RAISE NOTICE '            obteniendo dia booleano';
                                diaBooleano := matrix.extract_boolean_column(grupompi, k);
                                --RAISE NOTICE '            obteniendo maxColoca';
                                --RAISE NOTICE '            	diaBooleano = ';

                                --execute matrix.echo(diaBooleano);
                                
                                maxColoca := get_max_colocation(diaBooleano, rclasificacion.duracion);

                                --RAISE NOTICE '            maxColoca = %', maxColoca;

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
                        
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
			RAISE NOTICE '       [INFO] calculo de los posibles turnos optimos en zonapriori ...';
                        -- calculo de los posibles turnos optimos en zonapriori

                        IF parche1 = TRUE THEN
				RAISE NOTICE '       [INFO] busco los turnos optimos de los grupos por dias ...';
                            -- busco los turnos optimos de los grupos por dias

                            j := 1;
                            LOOP
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------                            
				RAISE NOTICE '       [INFO] calculo las posibilidades para todos los grupos ...';
                                -- calculo las posibilidades para todos los grupos

                                -- busco el m?mo
                                ya := matrix.create_boolean_list(cantGrupos);

                                k := 1;
                                LOOP
                                    
                                    l := 1;
                                    LOOP
                                        min := CT + 1;
                                        gmin := 0;

					RAISE NOTICE '       [INFO] busco el grupo con el minimo de posibilidades optimas (en zpriori = 1) ...';
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
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
					RAISE NOTICE '       [INFO] busco la primera posibilidad para el grupo encontrado ...';
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
                                        
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
                                        EXIT WHEN l = cantGrupos;
                                        l := l + 1;
                                    END LOOP;


                                    EXIT WHEN k = cantGrupos;
                                    k := k + 1;
                                END LOOP;
                                
                                EXIT WHEN j = CD;
                                j := j + 1;
                            END LOOP;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
			    RAISE NOTICE '       [INFO] actualizo posibXDias ...';
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

			RAISE NOTICE '       [INFO] empieza la colocacion ...';
                        -- empieza la colocacion                      
                        grupo_anterior := 0; -- verificar que el grupo_anterior puede ser el mismo que j

                        
                        
                        j := 1;
                        LOOP
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
			    sePudoColocar := false;

			    RAISE NOTICE '[INFO] Asig: %,%', rasignatura.descripcion, rasignatura.id;
			    RAISE NOTICE '[INFO] Clasif: %,%', rclasificacion.descripcion, rclasificacion.id;
			    RAISE NOTICE '[INFO] Colocando plantilla %', rplantilla.id;
			    
			    RAISE NOTICE '[INFO] Colocando grupo %: %',j,grupos[j];
			    
                            listaidbrg := dame_lista_id_brigadas_de_grupo(grupos[j]);

                            cantListaIdBrg := matrix.get_length_of(listaidbrg, 1);
                            
                            grupompi := and_mpi(listaidbrg,CD,CT,rplantilla.id,0,FALSE);

			    RAISE NOTICE ' grupompi = ';
                            execute matrix.echo(grupompi);

			    RAISE NOTICE '       [INFO] calculo el rango de busqueda ...';
                            -- calculo el rango de busqueda
                            
                            rangobusqueda := rango(rplantilla.id, grupos[j], rdesglose.min_dias, rdesglose.max_dias);

			    IF rplantilla.exige_profesor THEN
			    
			    RAISE NOTICE '       [INFO] OPTIMIZACION DEL PROFESOR ...';
                            -- ----------------------------------------------------------------------------
                            -- OPTIMIZACION DEL PROFESOR
                            -- ----------------------------------------------------------------------------
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
				RAISE NOTICE '       [INFO] Obtengo los profesores asignados al grupo actual para la actividad actual ...';
                                -- Obtengo los profesores asignados al grupo actual para la actividad actual

                                actual_profe := dame_lista_id_profes_asignados_al_grupo(rplantilla.id, grupos[j]);
                                
                                cantActualProfe := matrix.get_length_of(actual_profe, 1);
                                
				RAISE NOTICE '       [INFO] cantActualProfe = % ', cantActualProfe;
				
				IF cantActualProfe > 0 THEN
					actual_profe_cant := matrix.create_numeric_matrix(cantActualProfe,CD);
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
					-- cantidad de turnos ocupados en actividades por dia

					RAISE NOTICE '       [INFO] se calcula el gasto total en el periodo para cada profesor ...';
					  
					-- se calcula el gasto total en el periodo para cada profesor

					l := 1;
					LOOP
					    actual_profe_gastos[l] := 0;
					    FOR r IN SELECT * FROM actividad WHERE dame_periodo_plantilla_de_actividad(plantilla) = rperiodo.id AND profesor = actual_profe[l] LOOP
						k := dame_duracion_clasif_de_actividad(dame_clasif_plantilla_de_actividad(rplantilla.id));
						actual_profe_cant[l][r.dia] := actual_profe_cant[l][r.dia] + k;
						actual_profe_gastos[l] := actual_profe_gastos[l] + k;
					    END LOOP;

					    EXIT WHEN l >= cantActualProfe;
					    l := l + 1;
					END LOOP;
					
					-- si hay mas de un profesor se selecciona el mejor con el metodo prioriza 
					-- dado los criterios:
					--      gasto (min)
					--      prioridad segun el seguridad.usuario (min)
					-- pero como la lista de profesores ya viene ordenada por prioridad
					-- entonces basta con buscar el primer profesor con el minimo gasto
					
					RAISE NOTICE '       [INFO] buscar el primer profesor con el minimo gasto ...';
					
					mejor_profe := 1;
					minim := -1;
					IF cantActualProfe > 1 THEN
					    l := 1;
					    LOOP
						IF minim = -1 OR minim > actual_profe_gastos[l] THEN
						    minim := actual_profe_gastos[l];
						    mejor_profe := l;
						END IF;
						
						EXIT WHEN l >= cantActualProfe;
						l := l + 1;
					    END LOOP;
					    
					END IF;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------					
					-- una vez obtenido el mejor profesor, entonces se crea una lista
					-- con los mejores dias para el profesor en el rango posible de colocacion
					-- el dia optimo esta dado por el dia de mas gasto
					-- pues la idea es minimizar la cantidad de dias que debe venir
					-- el profesor a dar clases (este parametro puede ser configurable)

					RAISE NOTICE '       [INFO] determinando los mejores dias para el profesor en el rango posible de colocacion ...';
								 
					dias_buenos_profe := matrix.create_numeric_list(CD);
					
					-- la principio es consecutivo
					l := 1;
					LOOP
					    dias_buenos_profe[l] := l;
					    EXIT WHEN l = CD;
					    l := l + 1;
					END LOOP;

					RAISE NOTICE '       [INFO] luego ordeno en el rango, de mayor a menor ...';
					
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
											
						EXIT WHEN k >= rangobusqueda.fin;
						k := k + 1;
					    END LOOP;
					    EXIT WHEN l >= rangobusqueda.fin - 1;
					    l := l +1;
					END LOOP;
                                END IF;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
				RAISE NOTICE 'dias_buenos_profe =';
				execute matrix.echo(dias_buenos_profe);
                                
                                RAISE NOTICE '       [INFO] terminada la OPTIMIZACION DEL PROFESOR ...';
				-- ----------------------------------------------------------------------------

				-- deshabilitar los turnos donde el profesor no puede dar clases

				ddia :=1;
				LOOP
					tturno :=1;
					LOOP
						dduracion := 1;
						LOOP
							EXIT WHEN grupompi[ddia][tturno] = false;
							
							v := valor_restriccion_profesor(actual_profe[mejor_profe], rperiodo.id, ddia, tturno + dduracion - 1);
							IF v > 0 THEN
								grupompi[ddia][tturno] := false;
								EXIT;
							END IF;
							EXIT WHEN dduracion >= rclasificacion.duracion;
							dduracion := dduracion + 1;
						END LOOP;
						EXIT WHEN tturno >= CT;
						tturno := tturno + 1;
					END LOOP;
					EXIT WHEN ddia >= CD;
					ddia := ddia + 1;
				END LOOP;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
                            
                            END IF;
                            
                            IF j = 1 AND idClasifSgte <> '' AND idClasifSgte <> rclasificacion.id THEN
                                IF parche = FALSE OR dia_anterior <= (CD / 2) -1 THEN
                                    dia_anterior := dia_minimo_impacto (idClasifSgte, idesp, idnivel, rperiodo.id, rangobusqueda.ini, rangobusqueda.fin);
                                    -- dia_anterior := dia_minimo_impacto (idClasifSgte, idesp, idnivel, rperiodo.id, grupos_rangos[j][1], grupos_rangos[j][2]);
                                END IF;
                            END IF;

                            RAISE NOTICE '       [INFO] verificando que la actividad no este asignada ...';
                            -- verificando que la actividad no este asignada
                            
                            SELECT count(id) as cant INTO r FROM actividad WHERE plantilla = rplantilla.id AND grupo = grupos[j];

                            esta_colocada := FALSE;
                            IF r.cant > 0 THEN
                                esta_colocada := TRUE;
                                sePudoColocar := TRUE;
                            END IF;

                            IF esta_colocada = FALSE THEN
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
				RAISE NOTICE '       [INFO] actualiza sentido para mayor combinacion ...';
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

				RAISE NOTICE '       [INFO] busqueda por zonas ...';
                                zona := 1;
                                LOOP
                                
                                    etapa := 1;
                                    LOOP
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------                                    
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
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
						idprofe = NULL;

						es_higienico_profe := TRUE;

						RAISE NOTICE '       [INFO] optimizando al profesor e higiene para profesor ...';
						IF rplantilla.exige_profesor = TRUE THEN
						    
						    -- optimizando al profesor
						    k := dias_buenos_profe[kk];
						    idprofe := actual_profe[mejor_profe];
							
						    -- higiene para profesor
						    es_higienico_profe := es_higienico_profesor(idprofe, rplantilla.id, k);
							
						END IF;

						IF es_higienico_profe = TRUE THEN
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
							IF rplantilla.exige_profesor = FALSE OR NOT (rplantilla.exige_profesor = TRUE AND idprofe IS NULL) THEN 
											
								RAISE NOTICE '       [INFO] higiene para brigada ...';
								-- ----------------------
								-- higiene para brigadas

								es_higienico_brg := TRUE;
								
								ih := 1;
								LOOP
								    es_higienico_brg := es_higienico_brigada(listaidbrg[ih], rplantilla.id, k);
									
								    EXIT WHEN ih >= cantListaIdBrg OR es_higienico_brg = FALSE;
								    ih := ih + 1;
								END LOOP;

								IF es_higienico_brg = TRUE THEN
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
									-- ----------------------
									RAISE NOTICE '       [INFO] colocando ...';
											    
									puedo := TRUE;

									IF rdesglose.respetar_orden = FALSE AND rdesglose.mismo_dia = FALSE THEN

										SELECT * INTO r FROM actividad WHERE grupo = grupos[j] AND dame_desglose_de_plantilla(plantilla) = rdesglose.id;

										IF r.grupo = grupos[j] THEN
										    puedo := FALSE;
										END IF;

									END IF;

									RAISE NOTICE ' [------] k = % puedo = %', k, puedo;
												
									IF k <> 0 AND puedo = TRUE THEN

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------

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
							    
											    
											    IF NOT (grupompi[k][t] = TRUE AND etapa = 1 AND xasig[t] = TRUE) THEN 
											      -- CONTINUE WHEN grupompi[k][t] = TRUE AND etapa = 1 AND xasig[t] = TRUE;
											    
												    IF grupompi[k][t] = TRUE THEN
												    
													IF etapa = 2 AND dia_anterior = 0 THEN
													    xasig[t] = TRUE;
													END IF;
												  

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------

													    IF rplantilla.exige_lugar = TRUE THEN

														RAISE NOTICE '       [INFO] asignando lugar ...';

														-- asignando lugar
													    
														idlugar := '';
														
														-- preparando...
														
														lugares := dame_lista_id_lugares_asignados(rplantilla.id);				

														RAISE NOTICE '            --------- % lugares posibles para la plantilla %',matrix.get_length_of(lugares, 1), rplantilla.id;
														RAISE NOTICE '       [INFO] lugares filtrados, extraer los que no permanecen en el turno ';

														-- extraer los que no permanecen en el turno

														lugares := extraer_lugares_que_no_permanecen(lugares, rperiodo.id, k, t, rclasificacion.duracion);

														RAISE NOTICE '       [INFO] lugares filtrados, extraer los lugares que no tienen capacidad ';
														
														-- extraer los lugares que no tienen capacidad

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------														lugares := quita_segun_capacidad(listaidbrg, lugares);

														RAISE NOTICE '       [INFO] lugares filtrados, extraer los lugares prohibidos ';
														
														-- extraer los lugares prohibidos

														lugares := quita_segun_prohibidos(('{' || quote_literal(grupos[j]) || '}')::VARCHAR[], lugares);

														cantLugares := matrix.get_length_of(lugares, 1);

														RAISE NOTICE '       [INFO] lugares filtrados!!!, quedaron % ', cantLugares;

														IF cantLugares > 0 THEN
															
															-- colocando ...   
															-- tratar de colocar en el aula fija
															pos := -1;
															
															IF EXISTS(SELECT * FROM lugar_fijo WHERE grupo = grupos[j]) THEN
																SELECT * INTO r FROM lugar_fijo WHERE grupo = grupos[j];
																pos := matrix.get_pos_of(lugares, r.lugar);
																IF pos > -1 THEN
																    idlugar := lugares[pos];
																END IF;
															END IF;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------															    
															-- buscar el mejor lugar

															-- buscar segun prioridades
																									
															IF pos = -1 THEN

															    -- obtengo la maxima prioridad dada a los lugares para la plantilla de actividad actual
															    
															    SELECT * INTO r FROM lugar_para_actividad WHERE plantilla = rplantilla.id ORDER BY prioridad DESC LIMIT 1 OFFSET 0;
															   
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
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
															    -- comienza la colocacion
															    l := 1;
															    LOOP
																RAISE NOTICE '    seleccionando lugar prioridad % ', l;
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

        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------																
																cantLugs := matrix.get_length_of(lugs, 1);

																RAISE NOTICE ' ------ % lugares con prioridad % ',cantLugs, l;
																EXECUTE matrix.echo(lugs);
																
																idlugar := '';
																
																
																IF cantLugs > 0 THEN
																	
																    -- asignando...
																    -- TODO: pensar en una variante conjunta entre el mas usado y mas cercano

																    IF buscalugar = FALSE THEN

																	RAISE NOTICE '- buscando lugar por prioridades ';
																    
																	-- el mas utilizado por el grupo actual
																	idlugar := busca_lugar_mas_utilizado_por(lugs, grupos[j], FALSE);

																	RAISE NOTICE '- lugar mas utilizado % ', idlugar;
																	
																	IF idlugar = '' THEN
																	    
																	    -- el menos utilizado por los demas
																	    idlugar := busca_lugar_menos_utilizado_por(lugs, grupos[j], TRUE);

																	    RAISE NOTICE '- lugar menos utilizado % ', idlugar;
																	    
																	    IF idlugar = '' THEN
																		-- se coge el primero
																		idlugar := lugs[1];
																		RAISE NOTICE '- .....EL PRIMERO !!!! % ', idlugar;
																	    END IF;
																	END IF;

																	RAISE NOTICE '- mejor lugar = %', idlugar;
																	
																    ELSE
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------																    
																	RAISE NOTICE '- buscando lugar mas cercano ';
																	-- ... el mas cercano
																	RAISE NOTICE '- dame_lugares_usados_alrededor_de ';
																	lugsarround := dame_lugares_usados_alrededor_de(listaidbrg, rperiodo.id, k, t);
																	RAISE NOTICE '- busca_lugar_menos_costoso ';
																	idlugar := busca_lugar_menos_costoso(lugs, lugsarround);
																	RAISE NOTICE '- lugar mas cercano = %', idlugar;
																    END IF;
																END IF;

																EXIT WHEN l = maxpriori OR idlugar <> '';
																l := l + 1;
															    END LOOP;
															END IF;
														END IF;
													END IF;   

													IF rplantilla.exige_lugar = FALSE OR NOT (rplantilla.exige_lugar = TRUE AND idlugar = '') THEN
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------														

														RAISE NOTICE '       [INFO] asignando otros recursos ...';
														-- asignando otros recursos
														
														idrecursos := '{}'::VARCHAR[];
														
														-- comprobar la disponibilidad de otros recursos

														gruporecursos := dame_lista_id_grupos_de_recursos_asignados(rplantilla.id);
														cantGrupoRecursos := matrix.get_length_of(gruporecursos,1);

														gr := 0;
														LOOP
														    EXIT WHEN gr = cantGrupoRecursos;
														    
														    SELECT * INTO rgruporecurso FROM otro_grupo_de_recursos_para_actividad WHERE id = gruporecursos[i];
														    
														    l := 0;
														    FOR r IN SELECT *, dame_uso_de_otro_recurso(otro_recurso) as uso, 
																	dame_costo_trasladar_recurso(otro_recurso, idlugar, rperiodo.id, k, t) as costo 
															     FROM otro_recurso_para_actividad WHERE grupo_de_recursos = gruporecursos[gr] 
															     ORDER BY prioridad, uso, costo 
															LOOP
															
															idrecursos := array_append(idrecursos, r.otro_recurso);
															l := l + 1;
															EXIT WHEN l = rgruporecurso.necesarios;
														    END LOOP;
														    
														    EXIT WHEN gr = cantGrupoRecursos;
														    gr := gr + 1;
														END LOOP;
													
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------													
														RAISE NOTICE '       [INFO] agregando la actividad ...';

														-- agregando la actividad
														IF rplantilla.exige_lugar = FALSE THEN
														    idlugar := NULL;
														END IF;  
														
														-- agregando la actividad
														IF rplantilla.exige_profesor = FALSE THEN
														    idprofe := NULL;
														END IF;                        

														execute matrix.echo(lugs);
														RAISE NOTICE '       plantilla = %',rplantilla.id;
														RAISE NOTICE '       profe = %',idprofe;
														RAISE NOTICE '       lugar = %',idlugar;
														RAISE NOTICE '       k = %',k;
														RAISE NOTICE '       t = %',t;
														RAISE NOTICE '       grupo = %',grupos[j];
														
														execute matrix.echo(idrecursos);

														RAISE NOTICE '	     momento = %', tiempo.moment_now();
														RAISE NOTICE '       creador = %', rasignatura.creador;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------																		
														EXECUTE agrega_actividad(rplantilla.id, idprofe, idlugar, k, t, grupos[j], idrecursos, tiempo.moment_now(), rasignatura.creador);

														sePudoColocar := true;
														
														-- --------------------------------------------
													ELSE
														RAISE NOTICE ' ---------------- no se pudo colocar por falta de lugar';
													END IF;
												END IF;
											END IF;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------											
										    EXIT WHEN t = fin_sentido or sePudoColocar = true;
										    t := t + step_sentido;
										END LOOP;
									    END IF;

									END IF;
								END IF;
							    RAISE NOTICE ' ****** [INFO CICLO] kk = % de CicloFin = % ', kk , CicloFin;
							
							ELSE
								RAISE NOTICE ' ---------------- no se pudo colocar por falta de profesor';
							END IF;
						END IF;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------						    
					    EXIT WHEN kk = CicloFin or sePudoColocar = true;
					    kk := kk + 1;
					    
					END LOOP;

					RAISE NOTICE ' ****** [INFO CICLO] etapa = % de 3  ', etapa;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------					
					EXIT WHEN etapa = 3 or sePudoColocar = true;
					etapa := etapa + 1;
				    END LOOP;

				    RAISE NOTICE ' ****** [INFO CICLO] zona = % de ztodo = %  ', zona, ztodo;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------				    
				    EXIT WHEN zona = ztodo or sePudoColocar = true;
				    zona := zona + 1;
				END LOOP;
			    END IF;
		    
			    IF sePudoColocar = false THEN
				RAISE NOTICE ' no se pudo colocar!!!!! ';
				-- TODO: registrar
			    END IF;
			    
			    RAISE NOTICE ' ****** [INFO CICLO] j = % de cantGrupos = %  ', j, cantGrupos;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------			    
			    EXIT WHEN j >= cantGrupos;
			    
			    grupo_anterior := j;
			    
			    j := j + 1;
			END LOOP;
			
			-- --------------------------------------------
			RAISE NOTICE ' ****** [INFO CICLO] i = % de cantPriorizando = %  ', i, cantPriorizando;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------			
			EXIT WHEN i = cantPriorizando;
			i := i + 1;
		    END LOOP;

		    RAISE NOTICE ' ****** [INFO CICLO] varCiclo2 = % de cantCiclo2 = %  ', varCiclo2, cantCiclo2;
		    
		    --EXIT WHEN varCiclo2 >= cantCiclo2;
		    
		    --varCiclo2 := varCiclo2 + 1;
		END LOOP;

                RAISE NOTICE ' ****** [INFO CICLO] varCiclo1 = % de cantCiclo1 = %  ', varCiclo1, cantCiclo1;
                
                --EXIT WHEN varCiclo1 >= cantCiclo1;
                --varCiclo1 := varCiclo1 + 1;
            END LOOP;

            RAISE NOTICE ' ****** [INFO CICLO] voyact = % de cantActEnCurso = %  ', voyAct, cantActEnCurso;
        -------------------------------------------------------------------
        IF generador.fue_cancelado(idesp, idnivel, idperiodo) = TRUE THEN
		RETURN;
        END IF;
        -------------------------------------------------------------------
	    voyAct := voyAct + 1;
	    
            EXIT WHEN voyact >= cantActEnCurso;
            
        END LOOP;
        
    END;
$$;


ALTER FUNCTION public.generar(idesp character varying, idnivel character varying, idperiodo character varying, forma bigint, parche boolean, compactar bigint, parche1 boolean, buscalugar boolean) OWNER TO postgres;

--
-- TOC entry 239 (class 1255 OID 26362)
-- Dependencies: 10 735
-- Name: get_max_colocation(boolean[], integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION get_max_colocation(arr boolean[], duracion integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
    DECLARE crows BIGINT;
        i INTEGER;
        inipi INTEGER;
        ini INTEGER;
        c INTEGER;
    BEGIN
        crows := matrix.get_length_of(arr, 1);

        IF crows = 0 THEN
            RETURN 0;
        END IF;

        inipi := 0;
        c := 0;
        
        i := 1;
        LOOP 
            IF arr[i] = TRUE THEN
                inipi = i;
                c := 1;
            END IF;
            EXIT WHEN i >= crows OR c = 1;
            i := i + 1;
        END LOOP;

        IF inipi > 0 THEN
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
        END IF;
        
        RETURN c;

    END;
$$;


ALTER FUNCTION public.get_max_colocation(arr boolean[], duracion integer) OWNER TO rafa;

--
-- TOC entry 2776 (class 0 OID 0)
-- Dependencies: 239
-- Name: FUNCTION get_max_colocation(arr boolean[], duracion integer); Type: COMMENT; Schema: public; Owner: rafa
--

COMMENT ON FUNCTION get_max_colocation(arr boolean[], duracion integer) IS 'Dado un arreglo de posibles inicios de una actividad devuelve la maxima cantidad de colocaciones posibles';


--
-- TOC entry 145 (class 1255 OID 26363)
-- Dependencies: 10
-- Name: greatest(numeric, numeric); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION "greatest"(numeric, numeric) RETURNS numeric
    LANGUAGE sql
    AS $_$SELECT CASE WHEN (($1 > $2) OR ($2 IS NULL)) THEN $1 ELSE $2 END;$_$;


ALTER FUNCTION public."greatest"(numeric, numeric) OWNER TO rafa;

--
-- TOC entry 146 (class 1255 OID 26364)
-- Dependencies: 10
-- Name: greatest(numeric, numeric, numeric); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION "greatest"(numeric, numeric, numeric) RETURNS numeric
    LANGUAGE sql
    AS $_$SELECT greatest($1, greatest($2, $3));$_$;


ALTER FUNCTION public."greatest"(numeric, numeric, numeric) OWNER TO rafa;

--
-- TOC entry 147 (class 1255 OID 26365)
-- Dependencies: 10
-- Name: if(boolean, text, text); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION if(boolean, text, text) RETURNS text
    LANGUAGE sql
    AS $_$SELECT CASE WHEN $1 THEN $2 ELSE $3 END;$_$;


ALTER FUNCTION public.if(boolean, text, text) OWNER TO rafa;

--
-- TOC entry 123 (class 1255 OID 26366)
-- Dependencies: 10
-- Name: if(boolean, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION if(boolean, integer, integer) RETURNS integer
    LANGUAGE sql
    AS $_$SELECT CASE WHEN $1 THEN $2 ELSE $3 END;$_$;


ALTER FUNCTION public.if(boolean, integer, integer) OWNER TO rafa;

--
-- TOC entry 151 (class 1255 OID 26367)
-- Dependencies: 10
-- Name: iif(boolean, anyelement, anyelement); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION iif(expresion boolean, dato1 anyelement, dato2 anyelement) RETURNS anyelement
    LANGUAGE sql
    AS $_$ 
    SELECT CASE $1 WHEN TRUE THEN $2 ELSE $3 END;
$_$;


ALTER FUNCTION public.iif(expresion boolean, dato1 anyelement, dato2 anyelement) OWNER TO rafa;

--
-- TOC entry 2777 (class 0 OID 0)
-- Dependencies: 151
-- Name: FUNCTION iif(expresion boolean, dato1 anyelement, dato2 anyelement); Type: COMMENT; Schema: public; Owner: rafa
--

COMMENT ON FUNCTION iif(expresion boolean, dato1 anyelement, dato2 anyelement) IS 'Devuelve el primer elemento si la condicion es true y el segundo si es falsa';


--
-- TOC entry 162 (class 1255 OID 26368)
-- Dependencies: 10 735
-- Name: lugar_permanece_en(character varying, character varying, integer, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION lugar_permanece_en(plugar character varying, pperiodo character varying, pdia integer, pturno integer, duracion integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
    DECLARE i INTEGER;
	v INTEGER;
    BEGIN
	i := 1;
	LOOP
		v := valor_restriccion_lugar(plugar, pperiodo, pdia, pturno);
		IF v > 0 THEN
			RETURN FALSE;
		END IF;
		EXIT WHEN i >= pturno + duracion;
		i := i + 1;
	END LOOP;
	
        RETURN TRUE;
    END;
$$;


ALTER FUNCTION public.lugar_permanece_en(plugar character varying, pperiodo character varying, pdia integer, pturno integer, duracion integer) OWNER TO rafa;

--
-- TOC entry 152 (class 1255 OID 26369)
-- Dependencies: 10
-- Name: max_cant_act(character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION max_cant_act(especialidad character varying, nivel character varying, periodo character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$
    SELECT max(cantidad)
    FROM asignaturas_cant_act 
    WHERE especialidad = $1 AND nivel = $2 AND periodo = $3;
$_$;


ALTER FUNCTION public.max_cant_act(especialidad character varying, nivel character varying, periodo character varying) OWNER TO rafa;

--
-- TOC entry 153 (class 1255 OID 26370)
-- Dependencies: 10 735
-- Name: mueve_o_crea_brigada_de_grupo(character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION mueve_o_crea_brigada_de_grupo(idbrg character varying, idgrupo character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.mueve_o_crea_brigada_de_grupo(idbrg character varying, idgrupo character varying) OWNER TO rafa;

--
-- TOC entry 154 (class 1255 OID 26371)
-- Dependencies: 10 735
-- Name: nivela_prioridad_lxact_after_delete(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION nivela_prioridad_lxact_after_delete() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	declare r record;
		p integer;
	begin
		p := OLD.prioridad;
		
		select * into r from lugar_para_actividad where prioridad = p AND plantilla = OLD.plantilla;
		if not found then
			update lugar_para_actividad set prioridad = prioridad - 1 where prioridad > p AND plantilla = OLD.plantilla;
		end if;

		return OLD;
	end;
$$;


ALTER FUNCTION public.nivela_prioridad_lxact_after_delete() OWNER TO rafa;

--
-- TOC entry 155 (class 1255 OID 26372)
-- Dependencies: 735 10
-- Name: nivela_prioridad_lxact_after_update(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION nivela_prioridad_lxact_after_update() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	declare r record;
		p integer;
	begin
		if new.actualizacion_interna = FALSE AND new.prioridad <> old.prioridad then
			p := OLD.prioridad;
			
			select * into r from lugar_para_actividad where prioridad = p;
			if not found then
				update lugar_para_actividad set prioridad = prioridad -1, actualizacion_interna = TRUE where prioridad > p AND plantilla = OLD.plantilla;
				update lugar_para_actividad set actualizacion_interna = FALSE WHERE plantilla = OLD.plantilla AND lugar = NEW.lugar;				
				NEW.prioridad = NEW.prioridad - 1;
			end if;
		end if;
		return NEW;
	end;
$$;


ALTER FUNCTION public.nivela_prioridad_lxact_after_update() OWNER TO rafa;

--
-- TOC entry 156 (class 1255 OID 26373)
-- Dependencies: 735 10
-- Name: nivela_prioridad_rxact_after_delete(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION nivela_prioridad_rxact_after_delete() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	declare r record;
		p integer;
	begin
		p := OLD.prioridad;
		
		select * into r from otro_grupo_de_recursos_para_actividad where prioridad = p;
		if not found then
			update otro_grupo_de_recursos_para_actividad set prioridad = prioridad - 1 where prioridad > p AND plantilla = OLD.plantilla;
		end if;

		return OLD;
	end;
$$;


ALTER FUNCTION public.nivela_prioridad_rxact_after_delete() OWNER TO rafa;

--
-- TOC entry 157 (class 1255 OID 26374)
-- Dependencies: 735 10
-- Name: nuevo_desglose_cuando_nueva_asignatura(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION nuevo_desglose_cuando_nueva_asignatura() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE r record;
	    pcreador varchar;
	begin
		
		FOR r IN SELECT * FROM periodo WHERE dame_creador_de_planificacion(dame_planificacion_de_periodo(id)) = NEW.creador LOOP
			INSERT INTO desglose (asignatura, periodo) VALUES (NEW.id, r.id);
		END LOOP;
		
		return NEW;
	end;
$$;


ALTER FUNCTION public.nuevo_desglose_cuando_nueva_asignatura() OWNER TO rafa;

--
-- TOC entry 158 (class 1255 OID 26375)
-- Dependencies: 735 10
-- Name: nuevo_desglose_cuando_nuevo_periodo(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION nuevo_desglose_cuando_nuevo_periodo() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
	DECLARE r record;
	    pcreador varchar;
	begin
		pcreador := dame_creador_de_planificacion(dame_planificacion_de_periodo(NEW.id));

		FOR r IN SELECT * FROM asignatura WHERE creador = pcreador LOOP
			INSERT INTO desglose (asignatura, periodo) VALUES (r.id, NEW.id);
		END LOOP;
		
		return NEW;
	end;
$$;


ALTER FUNCTION public.nuevo_desglose_cuando_nuevo_periodo() OWNER TO rafa;

--
-- TOC entry 159 (class 1255 OID 26376)
-- Dependencies: 10
-- Name: plantilla_exige_lugar(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION plantilla_exige_lugar(id character varying) RETURNS boolean
    LANGUAGE sql
    AS $_$
    SELECT exige_lugar FROM plantilla_de_actividad WHERE id = $1;
$_$;


ALTER FUNCTION public.plantilla_exige_lugar(id character varying) OWNER TO rafa;

--
-- TOC entry 160 (class 1255 OID 26377)
-- Dependencies: 10
-- Name: plantilla_exige_profesor(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION plantilla_exige_profesor(id character varying) RETURNS boolean
    LANGUAGE sql
    AS $_$
    SELECT exige_profesor FROM plantilla_de_actividad WHERE id = $1;
$_$;


ALTER FUNCTION public.plantilla_exige_profesor(id character varying) OWNER TO rafa;

--
-- TOC entry 1934 (class 1259 OID 26378)
-- Dependencies: 2303 2304 2305 10
-- Name: actividad; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE actividad (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'actividad'::character varying, 'id'::character varying, 'actividad'::character varying) NOT NULL,
    plantilla character varying NOT NULL,
    grupo character varying NOT NULL,
    profesor character varying,
    lugar character varying,
    dia integer,
    turno integer,
    momento_de_colocacion double precision,
    creador character varying NOT NULL,
    CONSTRAINT actividad_check CHECK ((((plantilla_exige_profesor(plantilla) = true) AND (profesor IS NOT NULL)) OR (plantilla_exige_profesor(plantilla) = false))),
    CONSTRAINT actividad_check1 CHECK ((((plantilla_exige_lugar(plantilla) = true) AND (lugar IS NOT NULL)) OR (plantilla_exige_lugar(plantilla) = false)))
);


ALTER TABLE public.actividad OWNER TO rafa;

--
-- TOC entry 1935 (class 1259 OID 26387)
-- Dependencies: 2306 2307 2308 2309 2310 2311 2312 2313 2314 2315 2316 2317 2318 2319 2320 2321 2322 2323 2324 2325 2326 2327 10
-- Name: restriccion_condicional; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_condicional (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'restriccion_condicional'::character varying, 'id'::character varying, 'restriccion_condicional'::character varying) NOT NULL,
    planificacion character varying,
    activa boolean DEFAULT true,
    clasif_act character varying,
    asig character varying,
    brigada character varying,
    profesor character varying,
    lugar character varying,
    otro_recurso character varying,
    especialidad character varying,
    nivel character varying,
    fecha_ini integer,
    fecha_fin integer,
    hora_ini double precision,
    hora_fin double precision,
    discreto_exacto boolean,
    dia_ini_planif integer,
    dia_fin_planif integer,
    hora_ini_planif double precision,
    hora_fin_planif double precision,
    discreto_exacto_planif boolean,
    periodo character varying,
    dia_ini integer,
    dia_fin integer,
    turno_ini integer,
    turno_fin integer,
    discreto boolean,
    hoy boolean,
    ahora boolean,
    dia_relativo_anterior integer DEFAULT 0,
    cant_dias_relativo_anterior integer DEFAULT 0,
    dia_relativo_posterior integer DEFAULT 0,
    cant_dias_relativo_posterior integer DEFAULT 0,
    rango_tiempo_relativo_anterior_ini double precision,
    rango_tiempo_relativo_anterior_tamano double precision,
    turno_relativo_anterior integer,
    cant_turnos_relativo_anterior integer,
    turno_relativo_posterior integer DEFAULT 0,
    cant_turnos_relativo_posterior integer DEFAULT 0,
    rango_tiempo_relativo_posterior_ini double precision,
    rango_tiempo_relativo_posterior_tamano double precision,
    entre_dias_antes integer,
    entre_dias_despues integer,
    entre_turnos_antes integer,
    entre_turnos_despues integer,
    entre_tiempo_antes double precision,
    entre_tiempo_despues double precision,
    cant_exa integer DEFAULT 1,
    cant_min integer DEFAULT 1,
    cant_max integer DEFAULT 1,
    consec_brigada character varying,
    consec_profesor character varying,
    consec_lugar character varying,
    consec_otro_recurso character varying,
    consec_periodo character varying NOT NULL,
    consec_dia_ini integer,
    consec_dia_fin integer,
    consec_turno_ini integer,
    consec_turno_fin integer,
    consec_discreto boolean DEFAULT true,
    siempre boolean,
    valor integer DEFAULT 1,
    CONSTRAINT restriccion_condicional_cant_dias_relativo_anterior_check CHECK ((cant_dias_relativo_anterior > (-1))),
    CONSTRAINT restriccion_condicional_cant_dias_relativo_posterior_check CHECK ((cant_dias_relativo_posterior > (-1))),
    CONSTRAINT restriccion_condicional_cant_turnos_relativo_anterior_check CHECK ((cant_turnos_relativo_anterior > (-1))),
    CONSTRAINT restriccion_condicional_cant_turnos_relativo_posterior_check CHECK ((cant_turnos_relativo_posterior > (-1))),
    CONSTRAINT restriccion_condicional_dia_relativo_anterior_check CHECK ((dia_relativo_anterior > (-1))),
    CONSTRAINT restriccion_condicional_dia_relativo_posterior_check CHECK ((dia_relativo_posterior > (-1))),
    CONSTRAINT restriccion_condicional_turno_relativo_anterior_check CHECK ((turno_relativo_anterior > (-1))),
    CONSTRAINT restriccion_condicional_turno_relativo_posterior_check CHECK ((turno_relativo_posterior > (-1))),
    CONSTRAINT restriccion_condicional_valor_check CHECK ((valor > 0))
);


ALTER TABLE public.restriccion_condicional OWNER TO rafa;

--
-- TOC entry 161 (class 1255 OID 26415)
-- Dependencies: 735 10 557 560
-- Name: ocurre_causa_rest_cond(restriccion_condicional, actividad, character varying, integer, integer, boolean); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION ocurre_causa_rest_cond(rc restriccion_condicional, act actividad, idperiodo character varying, pdia integer, pturno integer, analiza_completo boolean) RETURNS boolean
    LANGUAGE plpgsql
    AS $$    
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
$$;


ALTER FUNCTION public.ocurre_causa_rest_cond(rc restriccion_condicional, act actividad, idperiodo character varying, pdia integer, pturno integer, analiza_completo boolean) OWNER TO rafa;

--
-- TOC entry 205 (class 1255 OID 34398)
-- Dependencies: 10
-- Name: pldbg_abort_target(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_abort_target(session integer) RETURNS SETOF boolean
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_abort_target';


ALTER FUNCTION public.pldbg_abort_target(session integer) OWNER TO postgres;

--
-- TOC entry 206 (class 1255 OID 34399)
-- Dependencies: 10
-- Name: pldbg_attach_to_port(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_attach_to_port(portnumber integer) RETURNS integer
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_attach_to_port';


ALTER FUNCTION public.pldbg_attach_to_port(portnumber integer) OWNER TO postgres;

--
-- TOC entry 207 (class 1255 OID 34400)
-- Dependencies: 528 10
-- Name: pldbg_continue(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_continue(session integer) RETURNS breakpoint
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_continue';


ALTER FUNCTION public.pldbg_continue(session integer) OWNER TO postgres;

--
-- TOC entry 208 (class 1255 OID 34401)
-- Dependencies: 10
-- Name: pldbg_create_listener(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_create_listener() RETURNS integer
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_create_listener';


ALTER FUNCTION public.pldbg_create_listener() OWNER TO postgres;

--
-- TOC entry 209 (class 1255 OID 34402)
-- Dependencies: 10
-- Name: pldbg_deposit_value(integer, text, integer, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_deposit_value(session integer, varname text, linenumber integer, value text) RETURNS boolean
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_deposit_value';


ALTER FUNCTION public.pldbg_deposit_value(session integer, varname text, linenumber integer, value text) OWNER TO postgres;

--
-- TOC entry 210 (class 1255 OID 34403)
-- Dependencies: 10
-- Name: pldbg_drop_breakpoint(integer, oid, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_drop_breakpoint(session integer, func oid, linenumber integer) RETURNS boolean
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_drop_breakpoint';


ALTER FUNCTION public.pldbg_drop_breakpoint(session integer, func oid, linenumber integer) OWNER TO postgres;

--
-- TOC entry 211 (class 1255 OID 34404)
-- Dependencies: 10 528
-- Name: pldbg_get_breakpoints(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_get_breakpoints(session integer) RETURNS SETOF breakpoint
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_get_breakpoints';


ALTER FUNCTION public.pldbg_get_breakpoints(session integer) OWNER TO postgres;

--
-- TOC entry 214 (class 1255 OID 34407)
-- Dependencies: 534 10
-- Name: pldbg_get_proxy_info(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_get_proxy_info() RETURNS proxyinfo
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_get_proxy_info';


ALTER FUNCTION public.pldbg_get_proxy_info() OWNER TO postgres;

--
-- TOC entry 212 (class 1255 OID 34405)
-- Dependencies: 10
-- Name: pldbg_get_source(integer, oid); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_get_source(session integer, func oid) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_get_source';


ALTER FUNCTION public.pldbg_get_source(session integer, func oid) OWNER TO postgres;

--
-- TOC entry 213 (class 1255 OID 34406)
-- Dependencies: 530 10
-- Name: pldbg_get_stack(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_get_stack(session integer) RETURNS SETOF frame
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_get_stack';


ALTER FUNCTION public.pldbg_get_stack(session integer) OWNER TO postgres;

--
-- TOC entry 227 (class 1255 OID 34416)
-- Dependencies: 538 10
-- Name: pldbg_get_target_info(text, "char"); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_get_target_info(signature text, targettype "char") RETURNS targetinfo
    LANGUAGE c STRICT
    AS '$libdir/targetinfo', 'pldbg_get_target_info';


ALTER FUNCTION public.pldbg_get_target_info(signature text, targettype "char") OWNER TO postgres;

--
-- TOC entry 215 (class 1255 OID 34408)
-- Dependencies: 542 10
-- Name: pldbg_get_variables(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_get_variables(session integer) RETURNS SETOF var
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_get_variables';


ALTER FUNCTION public.pldbg_get_variables(session integer) OWNER TO postgres;

--
-- TOC entry 216 (class 1255 OID 34409)
-- Dependencies: 528 10
-- Name: pldbg_select_frame(integer, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_select_frame(session integer, frame integer) RETURNS breakpoint
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_select_frame';


ALTER FUNCTION public.pldbg_select_frame(session integer, frame integer) OWNER TO postgres;

--
-- TOC entry 217 (class 1255 OID 34410)
-- Dependencies: 10
-- Name: pldbg_set_breakpoint(integer, oid, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_set_breakpoint(session integer, func oid, linenumber integer) RETURNS boolean
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_set_breakpoint';


ALTER FUNCTION public.pldbg_set_breakpoint(session integer, func oid, linenumber integer) OWNER TO postgres;

--
-- TOC entry 218 (class 1255 OID 34411)
-- Dependencies: 10
-- Name: pldbg_set_global_breakpoint(integer, oid, integer, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_set_global_breakpoint(session integer, func oid, linenumber integer, targetpid integer) RETURNS boolean
    LANGUAGE c
    AS '$libdir/pldbgapi', 'pldbg_set_global_breakpoint';


ALTER FUNCTION public.pldbg_set_global_breakpoint(session integer, func oid, linenumber integer, targetpid integer) OWNER TO postgres;

--
-- TOC entry 219 (class 1255 OID 34412)
-- Dependencies: 10 528
-- Name: pldbg_step_into(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_step_into(session integer) RETURNS breakpoint
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_step_into';


ALTER FUNCTION public.pldbg_step_into(session integer) OWNER TO postgres;

--
-- TOC entry 220 (class 1255 OID 34413)
-- Dependencies: 528 10
-- Name: pldbg_step_over(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_step_over(session integer) RETURNS breakpoint
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_step_over';


ALTER FUNCTION public.pldbg_step_over(session integer) OWNER TO postgres;

--
-- TOC entry 221 (class 1255 OID 34414)
-- Dependencies: 10 528
-- Name: pldbg_wait_for_breakpoint(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_wait_for_breakpoint(session integer) RETURNS breakpoint
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_wait_for_breakpoint';


ALTER FUNCTION public.pldbg_wait_for_breakpoint(session integer) OWNER TO postgres;

--
-- TOC entry 29 (class 1255 OID 34415)
-- Dependencies: 10
-- Name: pldbg_wait_for_target(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pldbg_wait_for_target(session integer) RETURNS integer
    LANGUAGE c STRICT
    AS '$libdir/pldbgapi', 'pldbg_wait_for_target';


ALTER FUNCTION public.pldbg_wait_for_target(session integer) OWNER TO postgres;

--
-- TOC entry 182 (class 1255 OID 34397)
-- Dependencies: 10
-- Name: plpgsql_oid_debug(oid); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION plpgsql_oid_debug(functionoid oid) RETURNS integer
    LANGUAGE c STRICT
    AS '$libdir/plugins/plugin_debugger', 'plpgsql_oid_debug';


ALTER FUNCTION public.plpgsql_oid_debug(functionoid oid) OWNER TO postgres;

--
-- TOC entry 240 (class 1255 OID 36070)
-- Dependencies: 10 735
-- Name: posible_inicio(character varying, integer, integer, character varying, character varying[], character varying[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION posible_inicio(idplantilla character varying, dia integer, turno integer, idbrigada character varying, plugares character varying[], pprofesores character varying[]) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
    DECLARE c BIGINT;
        i INTEGER;
        j INTEGER;
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

	--RAISE NOTICE '[INFO] POSIBLE INICIO:  dia % y turno % ', dia, turno;
	
        duracion := dame_duracion_plantilla_de_actividad(idplantilla);

        --RAISE NOTICE '[INFO] Duracion obtenida % ', duracion;
        
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
	    --RAISE NOTICE '[INFO] POSIBLE INICIO: comprobando restricciones para turno % ', turno + i;
	    
            EXIT WHEN i = duracion;
            
            -- comprobando que el periodo no est?estringido
            
            v:= valor_restriccion_periodo(periodo, dia, turno + i);

            IF v > 0 THEN
                RETURN FALSE;
            END IF;

            -- comprobando la disponibilidad de la clasificacion

            v:= valor_restriccion_clasif_act(clasif, periodo, dia, turno + i);

            IF v > 0 THEN
                RETURN FALSE;
            END IF;

            -- comprobando la disponibilidad de la asignatura

            v:= valor_restriccion_asignatura(asignatura, periodo, dia, turno + i);

            IF v > 0 THEN
                RETURN FALSE;
            END IF;
            
            -- compoobando la disponibilidad de la brigada

            v:= valor_restriccion_brigada(idbrigada, periodo, dia, turno + i);

            IF v > 0 THEN
                RETURN FALSE;
            END IF;
            
            i := i + 1;
        END LOOP;


        IF exige_profesor = TRUE THEN

	    --RAISE NOTICE '[INFO] POSIBLE INICIO: comprobar la disponibilidad de profesores ';
            -- comprobar la disponibilidad de profesores
            
            j := 0;

            k := 0;
            
            LOOP
                EXIT WHEN j = cantProfes;

                puede := TRUE;
                
                i := 0;
                LOOP
                    EXIT WHEN i = duracion;
                                
                    v:= valor_restriccion_profesor(profesores[j], periodo, dia, turno + i);

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
		--RAISE NOTICE '[INFO] POSIBLE INICIO: comprobar la disponibilidad de lugares ';
            -- comprobar la disponibilidad de lugares
            j := 0;

            k := 0;
            
            LOOP
                EXIT WHEN j = cantLugares;

                puede := TRUE;
                
                i := 0;
                LOOP
                    EXIT WHEN i = duracion;
                                
                    v := valor_restriccion_lugar(lugares[j], periodo, dia, turno + i);

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

        --RAISE NOTICE '[INFO] POSIBLE INICIO: comprobar la disponibilidad de otros recursos ';
        -- comprobar la disponibilidad de otros recursos

        gruporecursos := dame_lista_id_grupos_de_recursos_asignados(idplantilla);
        cantGrupoRecursos := matrix.get_length_of(gruporecursos,1);

	-- execute matrix.echo(gruporecursos);
	
	--RAISE NOTICE '[INFO] POSIBLE INICIO: cantGrupoRecursos = % ', cantGrupoRecursos;

        i := 0;
        LOOP
	    
            EXIT WHEN i = cantGrupoRecursos;

	    --RAISE NOTICE '[INFO] POSIBLE INICIO: ... grupo recursos %', i;

            recursos := dame_lista_id_otros_recursos_asignados(gruporecursos[i]);
            cantRecursos := matrix.get_length_of(recursos, 1);

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
                        v := valor_restriccion_otro_recurso(recursos[j], periodo, dia, turno + k);
                    
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

        RETURN 1;
    END;
$$;


ALTER FUNCTION public.posible_inicio(idplantilla character varying, dia integer, turno integer, idbrigada character varying, plugares character varying[], pprofesores character varying[]) OWNER TO postgres;

--
-- TOC entry 236 (class 1255 OID 26418)
-- Dependencies: 10 735
-- Name: quita_segun_capacidad(character varying[], character varying[]); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION quita_segun_capacidad(brigadas character varying[], lugares character varying[]) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        r RECORD;
        cantLug BIGINT;
        i BIGINT;
        matricula BIGINT;
        capacidad INTEGER;
    BEGIN
        matricula := dame_matricula_total(brigadas);
        cantLug := matrix.get_length_of(lugares, 1);

	IF cantLug > 0 THEN
		i := 1;
		LOOP
		    capacidad := dame_capacidad(lugares[i]); 

		    -- si la capacidad es 0 significa que no importa o que es infinita

		    IF capacidad = 0 OR capacidad >= matricula THEN
			arr := array_append(arr, lugares[i]);
		    END IF;
		    
		    EXIT WHEN i >= cantLug;
		    i := i + 1;
		END LOOP;
        END IF;
        
        RETURN arr;
    END;
    
$$;


ALTER FUNCTION public.quita_segun_capacidad(brigadas character varying[], lugares character varying[]) OWNER TO rafa;

--
-- TOC entry 167 (class 1255 OID 26419)
-- Dependencies: 10 735
-- Name: quita_segun_prohibidos(character varying[], character varying[]); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION quita_segun_prohibidos(grupos character varying[], lugares character varying[]) RETURNS character varying[]
    LANGUAGE plpgsql
    AS $$
    DECLARE arr VARCHAR[];
        r RECORD;
        cantLug BIGINT; cantGrupos BIGINT;
        i BIGINT; j BIGINT;
        puede BOOLEAN;
    BEGIN
        
        cantLug := matrix.get_length_of(lugares, 1);
        cantGrupos := matrix.get_length_of(grupos, 1);

        IF cantLug > 0 THEN
		i := 1;
		LOOP
		    puede := TRUE;

		    IF cantGrupos >0 THEN
		    
			    j := 1;

			    LOOP
				IF es_lugar_prohibido (grupos[j], lugares[i]) THEN
				    puede := FALSE;
				    EXIT;
				END IF;
				
				EXIT WHEN j >= cantGrupos;
				j := j + 1;
			    END LOOP;

		    END IF;
		    IF puede = TRUE THEN
			arr := array_append(arr, lugares[i]);
		    END IF;
		    
		    EXIT WHEN i = cantLug;
		    i := i + 1;
		END LOOP;
        END IF;
        
        RETURN arr;
    END;
    
$$;


ALTER FUNCTION public.quita_segun_prohibidos(grupos character varying[], lugares character varying[]) OWNER TO rafa;

--
-- TOC entry 163 (class 1255 OID 26420)
-- Dependencies: 10
-- Name: rand(); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION rand() RETURNS double precision
    LANGUAGE sql
    AS $$SELECT random();$$;


ALTER FUNCTION public.rand() OWNER TO rafa;

--
-- TOC entry 169 (class 1255 OID 26421)
-- Dependencies: 10 735 540
-- Name: rango(character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION rango(idplantilla character varying, idgrupo character varying, minimo integer, maximo integer) RETURNS trango
    LANGUAGE plpgsql
    AS $$    
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
                        ran.ini = i + minimo;
                        ran.fin = CD;
                    ELSE
                        ran.ini = 1;
                        ran.fin = i - minimo;
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

            ran.ini := da + minimo;

            IF dif <= maximo * 2 THEN
                ran.fin = db - minimo;
            ELSE
                ran.fin = da + maximo;
            END IF;
        END IF;

        RETURN ran;
    
    END;
$$;


ALTER FUNCTION public.rango(idplantilla character varying, idgrupo character varying, minimo integer, maximo integer) OWNER TO rafa;

--
-- TOC entry 164 (class 1255 OID 26422)
-- Dependencies: 10 735
-- Name: restringe_por_uso_de_otro_recurso(character varying, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION restringe_por_uso_de_otro_recurso(idactividad character varying, idrecurso character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.restringe_por_uso_de_otro_recurso(idactividad character varying, idrecurso character varying) OWNER TO rafa;

--
-- TOC entry 168 (class 1255 OID 26423)
-- Dependencies: 735 10
-- Name: restringe_segun_actividad(character varying, character varying, character varying, character varying, integer, integer, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION restringe_segun_actividad(idactividad character varying, idplantilla character varying, idprofesor character varying, idlugar character varying, pdia integer, pturno integer, idgrupo character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE 
    pid VARCHAR;
    listaidbrg VARCHAR[];
    cant BIGINT;
    i BIGINT; j INTEGER;
    rplantilla record;
    idperiodo varchar;
    rclasificacion record;
    r record;
BEGIN

    SELECT * INTO rplantilla FROM plantilla_de_actividad WHERE id = idplantilla;
    SELECT * INTO rclasificacion FROM clasificacion_de_actividad WHERE id = rplantilla.clasificacion;
    
    idperiodo := dame_periodo_plantilla_de_actividad(idplantilla);
    -- restringir a los implicados

    -- restringiendo a las brigadas
    listaidbrg := dame_lista_id_brigadas_de_grupo(idgrupo);
    cant := matrix.get_length_of(listaidbrg, 1);

    i := 1;
    LOOP
        j := pturno;

        LOOP
            pid := crear_id_de_replica('ancoragoh','restriccion_de_brigada','id','restriccion_de_brigada');
            INSERT INTO restriccion_de_brigada (id, brigada, periodo, dia, turno, valor, por_actividad, actividad) VALUES (pid, listaidbrg[i], idperiodo, pdia, j, 1, TRUE, idactividad);
            INSERT INTO restriccion_por_actividad_de_brigada (restriccion_nominal, actividad) VALUES (pid, idactividad);
            
            EXIT WHEN j = pturno + rclasificacion.duracion - 1;
            j := j + 1;
        END LOOP;
        
        EXIT WHEN i = cant;

        i := i + 1;
    END LOOP;
    
    -- restringiendo al profesor y lugar
    j := pturno;

    LOOP
        pid := crear_id_de_replica('ancoragoh','restriccion_de_profesor','id','restriccion_de_profesor');
        INSERT INTO restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad, actividad) VALUES (pid, idprofesor, idperiodo, pdia, j, 1, TRUE, idactividad);
        INSERT INTO restriccion_por_actividad_de_profesor (restriccion_nominal, actividad) VALUES (pid, idactividad);
        
        pid := crear_id_de_replica('ancoragoh','restriccion_de_lugar','id','restriccion_de_lugar');
        INSERT INTO restriccion_de_lugar (id, lugar, periodo, dia, turno, valor, por_actividad, actividad) VALUES (pid, idlugar, idperiodo, pdia, j, 1, TRUE, idactividad);
        INSERT INTO restriccion_por_actividad_de_lugar (restriccion_nominal, actividad) VALUES (pid, idactividad);
        
        EXIT WHEN j = pturno + rclasificacion.duracion - 1;
        j := j + 1;
    END LOOP;
   
END;
$$;


ALTER FUNCTION public.restringe_segun_actividad(idactividad character varying, idplantilla character varying, idprofesor character varying, idlugar character varying, pdia integer, pturno integer, idgrupo character varying) OWNER TO rafa;

--
-- TOC entry 165 (class 1255 OID 26424)
-- Dependencies: 10 735
-- Name: sube_orden_de_plantilla(character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION sube_orden_de_plantilla(idplantilla character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.sube_orden_de_plantilla(idplantilla character varying) OWNER TO rafa;

--
-- TOC entry 201 (class 1255 OID 34370)
-- Dependencies: 10 735
-- Name: sube_prioridad_clasif(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION sube_prioridad_clasif(idclasif character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
	declare 
		r1 record;
		r2 record;
	begin
		SELECT * INTO r1 FROM clasificacion_de_actividad WHERE id = idclasif;

		IF r1.prioridad > 1 THEN
			SELECT * INTO r2 FROM clasificacion_de_actividad WHERE creador = r1.creador AND prioridad = r1.prioridad - 1;
			UPDATE clasificacion_de_actividad SET prioridad = r1.prioridad WHERE id = r2.id;
			UPDATE clasificacion_de_actividad SET prioridad = r2.prioridad WHERE id = r1.id;
		END IF;
		
		return true;
	end;
$$;


ALTER FUNCTION public.sube_prioridad_clasif(idclasif character varying) OWNER TO postgres;

--
-- TOC entry 166 (class 1255 OID 26425)
-- Dependencies: 10 735
-- Name: turnos_se_solapan(character varying, integer, integer, integer, character varying, integer, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION turnos_se_solapan(idperiodo1 character varying, dia1 integer, turno1 integer, duracion1 integer, idperiodo2 character varying, dia2 integer, turno2 integer, duracion2 integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
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
    $$;


ALTER FUNCTION public.turnos_se_solapan(idperiodo1 character varying, dia1 integer, turno1 integer, duracion1 integer, idperiodo2 character varying, dia2 integer, turno2 integer, duracion2 integer) OWNER TO rafa;

--
-- TOC entry 1936 (class 1259 OID 26426)
-- Dependencies: 2328 2329 2330 2331 2332 10
-- Name: periodo; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE periodo (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'periodo'::character varying, 'id'::character varying, 'periodo'::character varying) NOT NULL,
    descripcion character varying NOT NULL,
    cant_dias integer DEFAULT 5,
    cant_turnos integer DEFAULT 6,
    hora_inicial double precision,
    duracion_del_turno double precision,
    planificacion character varying NOT NULL,
    CONSTRAINT periodo_cant_dias_check CHECK ((cant_dias > 0)),
    CONSTRAINT periodo_cant_turnos_check CHECK ((cant_turnos > 0))
);


ALTER TABLE public.periodo OWNER TO rafa;

--
-- TOC entry 170 (class 1255 OID 26437)
-- Dependencies: 563 735 563 10
-- Name: valor_rest_entre_config_distintas(integer[], periodo, periodo, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION valor_rest_entre_config_distintas(rests integer[], per1 periodo, per2 periodo, dia integer, turno integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.valor_rest_entre_config_distintas(rests integer[], per1 periodo, per2 periodo, dia integer, turno integer) OWNER TO rafa;

--
-- TOC entry 171 (class 1255 OID 26438)
-- Dependencies: 735 10
-- Name: valor_restriccion_asignatura(character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION valor_restriccion_asignatura(id character varying, idperiodo character varying, pdia integer, pturno integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
        BEGIN
            RETURN valor_restriccion_generica('ASIGNATURA',id, idperiodo, pdia, pturno, '*');
        END;
        
    $$;


ALTER FUNCTION public.valor_restriccion_asignatura(id character varying, idperiodo character varying, pdia integer, pturno integer) OWNER TO rafa;

--
-- TOC entry 172 (class 1255 OID 26439)
-- Dependencies: 10 735
-- Name: valor_restriccion_brigada(character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION valor_restriccion_brigada(id character varying, idperiodo character varying, pdia integer, pturno integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
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
        
    $$;


ALTER FUNCTION public.valor_restriccion_brigada(id character varying, idperiodo character varying, pdia integer, pturno integer) OWNER TO rafa;

--
-- TOC entry 173 (class 1255 OID 26440)
-- Dependencies: 735 10
-- Name: valor_restriccion_clasif_act(character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION valor_restriccion_clasif_act(id character varying, idperiodo character varying, pdia integer, pturno integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
        BEGIN
            RETURN valor_restriccion_generica('CLASIFACT',id, idperiodo, pdia, pturno, '*');
        END;
        
    $$;


ALTER FUNCTION public.valor_restriccion_clasif_act(id character varying, idperiodo character varying, pdia integer, pturno integer) OWNER TO rafa;

--
-- TOC entry 174 (class 1255 OID 26441)
-- Dependencies: 10 735
-- Name: valor_restriccion_condicional(character varying, character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION valor_restriccion_condicional(tipo_consec character varying, id_consec character varying, idperiodo character varying, pdia integer, pturno integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
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
    $$;


ALTER FUNCTION public.valor_restriccion_condicional(tipo_consec character varying, id_consec character varying, idperiodo character varying, pdia integer, pturno integer) OWNER TO rafa;

--
-- TOC entry 175 (class 1255 OID 26442)
-- Dependencies: 10 735
-- Name: valor_restriccion_especialidad(character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION valor_restriccion_especialidad(id character varying, idperiodo character varying, pdia integer, pturno integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
        BEGIN
            RETURN valor_restriccion_generica('ESPECIALIDAD',id, idperiodo, pdia, pturno, '*');
        END;
        
    $$;


ALTER FUNCTION public.valor_restriccion_especialidad(id character varying, idperiodo character varying, pdia integer, pturno integer) OWNER TO rafa;

--
-- TOC entry 234 (class 1255 OID 26443)
-- Dependencies: 10 735
-- Name: valor_restriccion_generica(character varying, character varying, character varying, integer, integer, character varying); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION valor_restriccion_generica(ptipo character varying, id character varying, idperiodo character varying, pdia integer, pturno integer, tipo_restricc character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
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
                SELECT valor INTO r from restriccion_de_clasif_act where clasificacion = id AND periodo = idperiodo and dia = pdia and turno = pturno;
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
                    SELECT * INTO r 
                        FROM restriccion_real_de_periodo 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi);
                        IF FOUND THEN
				RETURN 1;
			END IF;
                END IF;
                
                IF tipo = 'ASIGNATURA' THEN
                    SELECT * INTO r 
                        FROM restriccion_real_de_asignatura
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi);             
                        IF FOUND THEN
				RETURN 1;
			END IF; 
                END IF;

                IF tipo = 'BRIGADA' THEN
                    SELECT * INTO r 
                        FROM restriccion_real_de_brigada 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi)
                        AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        IF FOUND THEN
				RETURN 1;
			END IF;
                END IF;

                IF tipo = 'CLASIFACT' THEN
                    SELECT * INTO r 
                        FROM restriccion_real_de_clasif_act
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi); 
                        IF FOUND THEN
				RETURN 1;
			END IF;                
                END IF;

                IF tipo = 'ESPECIALIDAD' THEN
                    SELECT * INTO r 
                        FROM restriccion_real_de_especialidad
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi);  
                        IF FOUND THEN
				RETURN 1;
			END IF;               
                END IF;

                IF tipo = 'LUGAR' THEN
                    SELECT * INTO r 
                        FROM restriccion_real_de_lugar 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi)
                        AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        IF FOUND THEN
				RETURN 1;
			END IF;
                END IF;

                IF tipo = 'OTRORECURSO' THEN
                    SELECT * INTO r 
                        from restriccion_real_de_otro_recurso 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi)
                        AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        IF FOUND THEN
				RETURN 1;
			END IF;
                END IF;

                IF tipo = 'PROFESOR' THEN
                    SELECT * INTO r 
                        from restriccion_real_de_profesor 
                        WHERE fecha = dia_analizando AND NOT (hora_inicial > hf OR hora_final < hi)
                        AND (tipo_restricc = '*' OR (tipo_restricc = 'ACTIVIDAD' and por_actividad = TRUE) or (tipo_restricc = 'RESTRICCION' and por_actividad = FALSE));
                        IF FOUND THEN
				RETURN 1;
			END IF;
                END IF;

                /*IF FOUND THEN
                    RETURN r2.valor;
                END IF;*/
            END LOOP;    

            RETURN 0;
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
    $$;


ALTER FUNCTION public.valor_restriccion_generica(ptipo character varying, id character varying, idperiodo character varying, pdia integer, pturno integer, tipo_restricc character varying) OWNER TO rafa;

--
-- TOC entry 223 (class 1255 OID 34387)
-- Dependencies: 735 10
-- Name: valor_restriccion_heredada(character varying, character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION valor_restriccion_heredada(tipo_hijo character varying, id_hijo character varying, idperiodo character varying, pdia integer, pturno integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
        DECLARE th VARCHAR; 
            r record;
            v INTEGER;
            r1 record;
            otroid VARCHAR;
        BEGIN
            th := upper(tipo_hijo);

            -- ANALIZANDO LOS PADRES
            -- si algun padre est?estringido de cualquiera de las formas
            -- entonces el hijo tambien lo esta
            
            FOR r IN SELECT * FROM herencia_de_restricciones 
                WHERE activa = TRUE AND (dame_tipo_hijo_hrt(id) = th) 
                    AND ((hijo_brigada = id_hijo AND th = 'BRIGADA') 
                        OR (hijo_profesor = id_hijo AND th = 'PROFESOR')
                        OR (hijo_lugar = id_hijo AND th = 'LUGAR')
                        OR (hijo_otro_recurso = id_hijo AND th = 'OTRORECURSO')
                        )
                        LOOP

                otroid := iif(th = 'BRIGADA', r.padre_brigada, '');
                otroid := iif(th = 'PROFESOR', r.padre_profesor, '');
                otroid := iif(th = 'LUGAR', r.padre_lugar, '');
                otroid := iif(th = 'OTRORECURSO', r.padre_otro_recurso, '');
                
                v := valor_restriccion_generica(dame_tipo_padre_hrt(r.id), otroid, idperiodo, pdia, pturno, '*');

                IF v > 0 THEN

                    SELECT * INTO r1 FROM excepcion_de_hrt WHERE herencia = r.id AND periodo = idperiodo AND dia = pdia AND turno = pturno;

                    IF NOT FOUND THEN
                        RETURN v;
                    END IF;
                    
                END IF;

            END LOOP;

            -- ANALIZANDO LOS HIJOS
            -- si algn hijo esta restringido por actividad entonces el padre esta
            -- restringido
            
            FOR r IN SELECT * FROM herencia_de_restricciones 
                WHERE (dame_tipo_padre_hrt(id) = th) 
                    AND ((padre_brigada = id_hijo AND th = 'BRIGADA') 
                        OR (padre_profesor = id_hijo AND th = 'PROFESOR')
                        OR (padre_lugar = id_hijo AND th = 'LUGAR')
                        OR (padre_otro_recurso = id_hijo AND th = 'OTRORECURSO')
                        )
                        LOOP

                otroid := iif(th = 'BRIGADA', r.hijo_brigada, '');
                otroid := iif(th = 'PROFESOR', r.hijo_profesor, '');
                otroid := iif(th = 'LUGAR', r.hijo_lugar, '');
                otroid := iif(th = 'OTRORECURSO', r.hijo_otro_recurso, '');
                
                v := valor_restriccion_generica(dame_tipo_hijo_hrt(r.id), otroid, idperiodo, pdia, pturno, 'ACTIVIDAD');

                IF v > 0 THEN

                    SELECT * INTO r1 FROM excepcion_de_hrt WHERE herencia = r.id AND periodo = idperiodo AND dia = pdia AND turno = pturno;

                    IF NOT FOUND THEN
                        RETURN v;
                    END IF;
                    
                END IF;

            END LOOP;

            RETURN 0;
        END;
    $$;


ALTER FUNCTION public.valor_restriccion_heredada(tipo_hijo character varying, id_hijo character varying, idperiodo character varying, pdia integer, pturno integer) OWNER TO postgres;

--
-- TOC entry 176 (class 1255 OID 26445)
-- Dependencies: 10 735
-- Name: valor_restriccion_lugar(character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION valor_restriccion_lugar(id character varying, idperiodo character varying, pdia integer, pturno integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
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
        
    $$;


ALTER FUNCTION public.valor_restriccion_lugar(id character varying, idperiodo character varying, pdia integer, pturno integer) OWNER TO rafa;

--
-- TOC entry 177 (class 1255 OID 26446)
-- Dependencies: 10 735
-- Name: valor_restriccion_periodo(character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION valor_restriccion_periodo(idperiodo character varying, pdia integer, pturno integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
        BEGIN
            RETURN valor_restriccion_generica('PERIODO', idperiodo, '', pdia, pturno, '*');            
        END;
    $$;


ALTER FUNCTION public.valor_restriccion_periodo(idperiodo character varying, pdia integer, pturno integer) OWNER TO rafa;

--
-- TOC entry 178 (class 1255 OID 26447)
-- Dependencies: 10 735
-- Name: valor_restriccion_profesor(character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION valor_restriccion_profesor(id character varying, idperiodo character varying, pdia integer, pturno integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
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
        
    $$;


ALTER FUNCTION public.valor_restriccion_profesor(id character varying, idperiodo character varying, pdia integer, pturno integer) OWNER TO rafa;

--
-- TOC entry 179 (class 1255 OID 26448)
-- Dependencies: 10
-- Name: valor_zpriori(character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: rafa
--

CREATE FUNCTION valor_zpriori(clasif character varying, periodo character varying, dia integer, turno integer) RETURNS integer
    LANGUAGE sql
    AS $_$
        SELECT valor from zpriori where clasificacion = $1 and periodo = $2 and dia = $3 and turno = $4
    $_$;


ALTER FUNCTION public.valor_zpriori(clasif character varying, periodo character varying, dia integer, turno integer) OWNER TO rafa;

SET search_path = seguridad, pg_catalog;

--
-- TOC entry 180 (class 1255 OID 26449)
-- Dependencies: 735 8
-- Name: check_host(character varying, character varying); Type: FUNCTION; Schema: seguridad; Owner: rafa
--

CREATE FUNCTION check_host(pusuario character varying, pip character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
	declare r record;
	begin
		select * into r from seguridad.host where usuario = pusuario and ip = ip;

		if found then
			return true;
		end if;
		
		return false;
	end;
$$;


ALTER FUNCTION seguridad.check_host(pusuario character varying, pip character varying) OWNER TO rafa;

--
-- TOC entry 181 (class 1255 OID 26450)
-- Dependencies: 735 8
-- Name: tiene_permiso(character varying, character varying); Type: FUNCTION; Schema: seguridad; Owner: rafa
--

CREATE FUNCTION tiene_permiso(pusuario character varying, ppermiso character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION seguridad.tiene_permiso(pusuario character varying, ppermiso character varying) OWNER TO rafa;

SET search_path = tiempo, pg_catalog;

--
-- TOC entry 183 (class 1255 OID 26451)
-- Dependencies: 9 735
-- Name: countdays_of_month(integer); Type: FUNCTION; Schema: tiempo; Owner: rafa
--

CREATE FUNCTION countdays_of_month(m integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION tiempo.countdays_of_month(m integer) OWNER TO rafa;

--
-- TOC entry 2778 (class 0 OID 0)
-- Dependencies: 183
-- Name: FUNCTION countdays_of_month(m integer); Type: COMMENT; Schema: tiempo; Owner: rafa
--

COMMENT ON FUNCTION countdays_of_month(m integer) IS 'RETURNS count of days of specificic month';


--
-- TOC entry 184 (class 1255 OID 26452)
-- Dependencies: 735 9
-- Name: current_hour_number(); Type: FUNCTION; Schema: tiempo; Owner: rafa
--

CREATE FUNCTION current_hour_number() RETURNS double precision
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION tiempo.current_hour_number() OWNER TO rafa;

--
-- TOC entry 185 (class 1255 OID 26453)
-- Dependencies: 9 735
-- Name: date_number_from_moment_number(double precision); Type: FUNCTION; Schema: tiempo; Owner: rafa
--

CREATE FUNCTION date_number_from_moment_number(m double precision) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
    BEGIN
        RETURN trunc(m / 864) + 1;
    END;
$$;


ALTER FUNCTION tiempo.date_number_from_moment_number(m double precision) OWNER TO rafa;

--
-- TOC entry 186 (class 1255 OID 26454)
-- Dependencies: 735 9
-- Name: date_to_number(integer, integer, integer); Type: FUNCTION; Schema: tiempo; Owner: rafa
--

CREATE FUNCTION date_to_number(d integer, m integer, a integer) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
    DECLARE cd INTEGER;
    
    BEGIN
        -- cantidad de dias antes del mes a partir del 1ero de enero
        cd := tiempo.day_number(d,m,a);

        -- cantidad de dias a partir del 1ero enero del 2000
        RETURN (a - 2000) * 365 + cd + (a - 2000) / 4 + bool_to_int(tiempo.is_leap_year(a) = FALSE);
    END;
$$;


ALTER FUNCTION tiempo.date_to_number(d integer, m integer, a integer) OWNER TO rafa;

--
-- TOC entry 2779 (class 0 OID 0)
-- Dependencies: 186
-- Name: FUNCTION date_to_number(d integer, m integer, a integer); Type: COMMENT; Schema: tiempo; Owner: rafa
--

COMMENT ON FUNCTION date_to_number(d integer, m integer, a integer) IS 'RETURNS consecutive number of date';


--
-- TOC entry 187 (class 1255 OID 26455)
-- Dependencies: 735 9
-- Name: day_number(integer, integer, integer); Type: FUNCTION; Schema: tiempo; Owner: rafa
--

CREATE FUNCTION day_number(d integer, m integer, a integer) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION tiempo.day_number(d integer, m integer, a integer) OWNER TO rafa;

--
-- TOC entry 2780 (class 0 OID 0)
-- Dependencies: 187
-- Name: FUNCTION day_number(d integer, m integer, a integer); Type: COMMENT; Schema: tiempo; Owner: rafa
--

COMMENT ON FUNCTION day_number(d integer, m integer, a integer) IS 'RETURNS the consecutive number of day';


--
-- TOC entry 188 (class 1255 OID 26456)
-- Dependencies: 735 9
-- Name: hour_number_from_date_number(double precision); Type: FUNCTION; Schema: tiempo; Owner: rafa
--

CREATE FUNCTION hour_number_from_date_number(m double precision) RETURNS double precision
    LANGUAGE plpgsql
    AS $$
    BEGIN
        RETURN m - (trunc(m / 864)) * 864;
    END;
$$;


ALTER FUNCTION tiempo.hour_number_from_date_number(m double precision) OWNER TO rafa;

--
-- TOC entry 189 (class 1255 OID 26457)
-- Dependencies: 9 735
-- Name: hour_to_number(integer, integer, integer); Type: FUNCTION; Schema: tiempo; Owner: rafa
--

CREATE FUNCTION hour_to_number(h integer, m integer, s integer) RETURNS double precision
    LANGUAGE plpgsql
    AS $$
    BEGIN
        RETURN h * 36 + m * 0.6 + s * 0.01;
    END;
$$;


ALTER FUNCTION tiempo.hour_to_number(h integer, m integer, s integer) OWNER TO rafa;

--
-- TOC entry 2781 (class 0 OID 0)
-- Dependencies: 189
-- Name: FUNCTION hour_to_number(h integer, m integer, s integer); Type: COMMENT; Schema: tiempo; Owner: rafa
--

COMMENT ON FUNCTION hour_to_number(h integer, m integer, s integer) IS 'Convert hour to number';


--
-- TOC entry 190 (class 1255 OID 26458)
-- Dependencies: 9 735
-- Name: is_leap_year(integer); Type: FUNCTION; Schema: tiempo; Owner: rafa
--

CREATE FUNCTION is_leap_year(pyear integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
    BEGIN
        IF mod(pyear, 4) = 0 and mod(pyear, 100) <> 0 or mod(pyear, 400) = 0 THEN 
            RETURN TRUE;
        END IF;
        RETURN FALSE;
    END;
$$;


ALTER FUNCTION tiempo.is_leap_year(pyear integer) OWNER TO rafa;

--
-- TOC entry 2782 (class 0 OID 0)
-- Dependencies: 190
-- Name: FUNCTION is_leap_year(pyear integer); Type: COMMENT; Schema: tiempo; Owner: rafa
--

COMMENT ON FUNCTION is_leap_year(pyear integer) IS 'RETURN TRUE IF is a leap year';


--
-- TOC entry 191 (class 1255 OID 26459)
-- Dependencies: 9 735
-- Name: moment_now(); Type: FUNCTION; Schema: tiempo; Owner: rafa
--

CREATE FUNCTION moment_now() RETURNS double precision
    LANGUAGE plpgsql
    AS $$
    DECLARE ahora timestamp;
        f BIGINT;
        h DOUBLE PRECISION;
        
    BEGIN

        f := tiempo.today_number();
        h := tiempo.current_hour_number();
        
        RETURN tiempo.moment_number(f, h);

    END;
$$;


ALTER FUNCTION tiempo.moment_now() OWNER TO rafa;

--
-- TOC entry 192 (class 1255 OID 26460)
-- Dependencies: 9
-- Name: moment_number(bigint, double precision); Type: FUNCTION; Schema: tiempo; Owner: rafa
--

CREATE FUNCTION moment_number(fecha bigint, hora double precision) RETURNS double precision
    LANGUAGE sql
    AS $_$SELECT ($1 - 1) * 864 + $2;$_$;


ALTER FUNCTION tiempo.moment_number(fecha bigint, hora double precision) OWNER TO rafa;

--
-- TOC entry 193 (class 1255 OID 26461)
-- Dependencies: 9 735
-- Name: month_from_day_number(integer, integer); Type: FUNCTION; Schema: tiempo; Owner: rafa
--

CREATE FUNCTION month_from_day_number(n integer, a integer) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
    DECLARE cd INTEGER;
        x INTEGER;
    BEGIN
        x := bool_to_int(tiempo.is_leap_year(a));
        
        cd := 1 + bool_to_int(n > 31) + bool_to_int(n > 59 + x) + bool_to_int(n > 90 + x) + bool_to_int(n > 120 + x);
        cd := cd + bool_to_int(n > 151 + x) + bool_to_int(n > 181 + x) + bool_to_int(n > 212 + x) + bool_to_int(n > 243 + x);
        cd := cd + bool_to_int(n > 273 + x) + bool_to_int (n > 304 + x) + bool_to_int(n > 334 + x);

        RETURN cd;
    END;
$$;


ALTER FUNCTION tiempo.month_from_day_number(n integer, a integer) OWNER TO rafa;

--
-- TOC entry 2783 (class 0 OID 0)
-- Dependencies: 193
-- Name: FUNCTION month_from_day_number(n integer, a integer); Type: COMMENT; Schema: tiempo; Owner: rafa
--

COMMENT ON FUNCTION month_from_day_number(n integer, a integer) IS 'RETURNS month from consecutive number of date';


--
-- TOC entry 194 (class 1255 OID 26462)
-- Dependencies: 544 9 735
-- Name: number_to_date(bigint); Type: FUNCTION; Schema: tiempo; Owner: rafa
--

CREATE FUNCTION number_to_date(n bigint) RETURNS ancora_date
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION tiempo.number_to_date(n bigint) OWNER TO rafa;

--
-- TOC entry 2784 (class 0 OID 0)
-- Dependencies: 194
-- Name: FUNCTION number_to_date(n bigint); Type: COMMENT; Schema: tiempo; Owner: rafa
--

COMMENT ON FUNCTION number_to_date(n bigint) IS 'RETURNS a date from consecutive number';


--
-- TOC entry 195 (class 1255 OID 26463)
-- Dependencies: 546 9 735
-- Name: number_to_hour(double precision); Type: FUNCTION; Schema: tiempo; Owner: rafa
--

CREATE FUNCTION number_to_hour(n double precision) RETURNS ancora_hour
    LANGUAGE plpgsql
    AS $$
    DECLARE t tiempo.ancora_hour;
    BEGIN
        t.h := trunc(n / 36);
        t.m := trunc((n - t.h * 36) /  0.6);
        t.s := trunc((n - (t.h * 36 + t.m * 0.6)) / 0.01);
        
        RETURN t;
    END;
$$;


ALTER FUNCTION tiempo.number_to_hour(n double precision) OWNER TO rafa;

--
-- TOC entry 2785 (class 0 OID 0)
-- Dependencies: 195
-- Name: FUNCTION number_to_hour(n double precision); Type: COMMENT; Schema: tiempo; Owner: rafa
--

COMMENT ON FUNCTION number_to_hour(n double precision) IS 'Conert number to hour';


--
-- TOC entry 196 (class 1255 OID 26464)
-- Dependencies: 735 9
-- Name: today_number(); Type: FUNCTION; Schema: tiempo; Owner: rafa
--

CREATE FUNCTION today_number() RETURNS bigint
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION tiempo.today_number() OWNER TO rafa;

SET search_path = public, pg_catalog;

--
-- TOC entry 736 (class 1255 OID 26465)
-- Dependencies: 10 144
-- Name: first(anyelement); Type: AGGREGATE; Schema: public; Owner: rafa
--

CREATE AGGREGATE first(anyelement) (
    SFUNC = public.first,
    STYPE = anyelement
);


ALTER AGGREGATE public.first(anyelement) OWNER TO rafa;

SET search_path = generador, pg_catalog;

--
-- TOC entry 1993 (class 1259 OID 36211)
-- Dependencies: 11
-- Name: cancelar; Type: TABLE; Schema: generador; Owner: postgres; Tablespace: 
--

CREATE TABLE cancelar (
    especialidad character varying NOT NULL,
    nivel character varying NOT NULL,
    periodo character varying NOT NULL
);


ALTER TABLE generador.cancelar OWNER TO postgres;

SET search_path = public, pg_catalog;

--
-- TOC entry 1937 (class 1259 OID 26466)
-- Dependencies: 10
-- Name: acceso_prohibido; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE acceso_prohibido (
    grupo character varying NOT NULL,
    lugar character varying NOT NULL
);


ALTER TABLE public.acceso_prohibido OWNER TO rafa;

--
-- TOC entry 1938 (class 1259 OID 26472)
-- Dependencies: 2333 10
-- Name: asignatura; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE asignatura (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'asignatura'::character varying, 'id'::character varying, 'asignatura'::character varying) NOT NULL,
    descripcion character varying NOT NULL,
    especialidad character varying,
    nivel character varying NOT NULL,
    creador character varying NOT NULL
);


ALTER TABLE public.asignatura OWNER TO rafa;

--
-- TOC entry 1939 (class 1259 OID 26479)
-- Dependencies: 2334 2335 2336 2337 2338 10
-- Name: desglose; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE desglose (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'desglose'::character varying, 'id'::character varying, 'desglose'::character varying) NOT NULL,
    asignatura character varying NOT NULL,
    periodo character varying NOT NULL,
    min_dias integer DEFAULT 1,
    max_dias integer DEFAULT 5,
    respetar_orden boolean DEFAULT true,
    mismo_dia boolean DEFAULT false
);


ALTER TABLE public.desglose OWNER TO rafa;

--
-- TOC entry 1940 (class 1259 OID 26490)
-- Dependencies: 2080 10
-- Name: asignaturas_cant_act; Type: VIEW; Schema: public; Owner: rafa
--

CREATE VIEW asignaturas_cant_act AS
    SELECT asignatura.id AS asignatura, asignatura.especialidad, asignatura.nivel, desglose.periodo, dame_cant_act(asignatura.id, desglose.periodo) AS cantidad FROM (asignatura JOIN desglose ON (((desglose.asignatura)::text = (asignatura.id)::text)));


ALTER TABLE public.asignaturas_cant_act OWNER TO rafa;

--
-- TOC entry 1941 (class 1259 OID 26494)
-- Dependencies: 10
-- Name: brigada_de_grupo; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE brigada_de_grupo (
    brigada character varying NOT NULL,
    grupo character varying NOT NULL
);


ALTER TABLE public.brigada_de_grupo OWNER TO rafa;

--
-- TOC entry 1942 (class 1259 OID 26500)
-- Dependencies: 2339 2340 2341 2342 10
-- Name: clasificacion_de_actividad; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE clasificacion_de_actividad (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'clasificacion_de_actividad'::character varying, 'id'::character varying, 'clasificacion_de_actividad'::character varying) NOT NULL,
    descripcion character varying NOT NULL,
    duracion integer DEFAULT 1,
    creador character varying NOT NULL,
    prioridad integer DEFAULT 1 NOT NULL,
    gasto integer DEFAULT 1
);


ALTER TABLE public.clasificacion_de_actividad OWNER TO rafa;

--
-- TOC entry 1943 (class 1259 OID 26510)
-- Dependencies: 10
-- Name: costo_de_traslado; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE costo_de_traslado (
    origen character varying NOT NULL,
    destino character varying NOT NULL,
    costo bigint
);


ALTER TABLE public.costo_de_traslado OWNER TO rafa;

--
-- TOC entry 1944 (class 1259 OID 26516)
-- Dependencies: 2343 10
-- Name: especialidad; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE especialidad (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'especialidad'::character varying, 'id'::character varying, 'especialidad'::character varying) NOT NULL,
    descripcion character varying NOT NULL,
    creador character varying NOT NULL
);


ALTER TABLE public.especialidad OWNER TO rafa;

--
-- TOC entry 1945 (class 1259 OID 26523)
-- Dependencies: 2344 2345 10
-- Name: excepcion_de_hrt; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE excepcion_de_hrt (
    herencia character varying NOT NULL,
    periodo character varying NOT NULL,
    dia integer NOT NULL,
    turno integer NOT NULL,
    CONSTRAINT excepcion_de_hrt_dia_check CHECK ((dia > 0)),
    CONSTRAINT excepcion_de_hrt_turno_check CHECK ((turno > 0))
);


ALTER TABLE public.excepcion_de_hrt OWNER TO rafa;

--
-- TOC entry 1946 (class 1259 OID 26531)
-- Dependencies: 2346 2347 2348 10
-- Name: herencia_de_restricciones; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE herencia_de_restricciones (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'herencia_de_restricciones'::character varying, 'id'::character varying, 'herencia_de_restricciones'::character varying) NOT NULL,
    planificacion character varying,
    activa boolean DEFAULT true,
    hijo_brigada character varying,
    hijo_profesor character varying,
    hijo_lugar character varying,
    hijo_otro_recurso character varying,
    padre_brigada character varying,
    padre_profesor character varying,
    padre_lugar character varying,
    padre_otro_recurso character varying,
    CONSTRAINT herencia_de_restricciones_check CHECK ((check_hrt(hijo_brigada, hijo_profesor, hijo_lugar, hijo_otro_recurso, padre_brigada, padre_profesor, padre_lugar, padre_otro_recurso) = true))
);


ALTER TABLE public.herencia_de_restricciones OWNER TO rafa;

--
-- TOC entry 1947 (class 1259 OID 26540)
-- Dependencies: 10
-- Name: higiene_para_brigada; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE higiene_para_brigada (
    periodo character varying NOT NULL,
    brigada character varying NOT NULL,
    gasto_maximo_diario integer NOT NULL
);


ALTER TABLE public.higiene_para_brigada OWNER TO rafa;

--
-- TOC entry 1948 (class 1259 OID 26546)
-- Dependencies: 10
-- Name: higiene_para_profesor; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE higiene_para_profesor (
    periodo character varying NOT NULL,
    profesor character varying NOT NULL,
    gasto_maximo_diario integer NOT NULL
);


ALTER TABLE public.higiene_para_profesor OWNER TO rafa;

--
-- TOC entry 1949 (class 1259 OID 26552)
-- Dependencies: 10
-- Name: lugar_fijo; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE lugar_fijo (
    grupo character varying NOT NULL,
    lugar character varying NOT NULL
);


ALTER TABLE public.lugar_fijo OWNER TO rafa;

--
-- TOC entry 1950 (class 1259 OID 26558)
-- Dependencies: 2349 2350 10
-- Name: lugar_para_actividad; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE lugar_para_actividad (
    plantilla character varying NOT NULL,
    lugar character varying NOT NULL,
    prioridad integer DEFAULT 1 NOT NULL,
    actualizacion_interna boolean DEFAULT false
);


ALTER TABLE public.lugar_para_actividad OWNER TO rafa;

--
-- TOC entry 1951 (class 1259 OID 26566)
-- Dependencies: 2351 10
-- Name: nivel; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE nivel (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'nivel'::character varying, 'id'::character varying, 'nivel'::character varying) NOT NULL,
    descripcion character varying NOT NULL,
    creador character varying NOT NULL
);


ALTER TABLE public.nivel OWNER TO rafa;

--
-- TOC entry 1952 (class 1259 OID 26573)
-- Dependencies: 2352 2353 2354 2355 10
-- Name: otro_grupo_de_recursos_para_actividad; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE otro_grupo_de_recursos_para_actividad (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'otro_grupo_de_recursos_para_actividad'::character varying, 'id'::character varying, 'otro_grupo_de_recursos_para_actividad'::character varying) NOT NULL,
    plantilla character varying NOT NULL,
    prioridad integer DEFAULT 1 NOT NULL,
    necesarios integer DEFAULT 1 NOT NULL,
    imprescindibles boolean DEFAULT true NOT NULL,
    creador character varying NOT NULL
);


ALTER TABLE public.otro_grupo_de_recursos_para_actividad OWNER TO rafa;

--
-- TOC entry 1954 (class 1259 OID 26590)
-- Dependencies: 2357 10
-- Name: otro_recurso_para_actividad; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE otro_recurso_para_actividad (
    grupo_de_recursos character varying NOT NULL,
    prioridad integer DEFAULT 1 NOT NULL,
    otro_recurso character varying NOT NULL
);


ALTER TABLE public.otro_recurso_para_actividad OWNER TO rafa;

--
-- TOC entry 1955 (class 1259 OID 26597)
-- Dependencies: 10
-- Name: otros_recursos_usados; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE otros_recursos_usados (
    otro_recurso character varying NOT NULL,
    actividad character varying NOT NULL,
    creador character varying NOT NULL
);


ALTER TABLE public.otros_recursos_usados OWNER TO rafa;

--
-- TOC entry 1956 (class 1259 OID 26603)
-- Dependencies: 2358 2359 10
-- Name: periodicidad; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE periodicidad (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'periodicidad'::character varying, 'id'::character varying, 'periodicidad'::character varying) NOT NULL,
    periodo character varying NOT NULL,
    dia integer,
    CONSTRAINT periodicidad_dia_check CHECK ((dia > 0))
);


ALTER TABLE public.periodicidad OWNER TO rafa;

--
-- TOC entry 1957 (class 1259 OID 26611)
-- Dependencies: 2360 2361 10
-- Name: planificacion; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE planificacion (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'planificacion'::character varying, 'id'::character varying, 'planificacion'::character varying) NOT NULL,
    descripcion character varying NOT NULL,
    fecha_inicial bigint,
    cant_dias bigint,
    activa boolean DEFAULT true,
    creador character varying NOT NULL
);


ALTER TABLE public.planificacion OWNER TO rafa;

--
-- TOC entry 1958 (class 1259 OID 26619)
-- Dependencies: 2362 2363 2364 2365 10
-- Name: plantilla_de_actividad; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE plantilla_de_actividad (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'plantilla_de_actividad'::character varying, 'id'::character varying, 'plantilla_de_actividad'::character varying) NOT NULL,
    desglose character varying NOT NULL,
    orden integer,
    clasificacion character varying NOT NULL,
    exige_profesor boolean DEFAULT true,
    exige_lugar boolean DEFAULT true,
    actualizacion_interna boolean DEFAULT false
);


ALTER TABLE public.plantilla_de_actividad OWNER TO rafa;

--
-- TOC entry 1959 (class 1259 OID 26629)
-- Dependencies: 2366 10
-- Name: profesor; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE profesor (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'profesor'::character varying, 'id'::character varying, 'profesor'::character varying) NOT NULL,
    nombre character varying,
    creador character varying NOT NULL
);


ALTER TABLE public.profesor OWNER TO rafa;

--
-- TOC entry 1960 (class 1259 OID 26636)
-- Dependencies: 2367 2368 10
-- Name: profesor_para_actividad; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE profesor_para_actividad (
    id character varying DEFAULT crear_id_de_replica('ancoragoh'::character varying, 'profesor_para_actividad'::character varying, 'id'::character varying, 'profesor_para_actividad'::character varying) NOT NULL,
    plantilla character varying NOT NULL,
    profesor character varying NOT NULL,
    prioridad integer DEFAULT 1 NOT NULL
);


ALTER TABLE public.profesor_para_actividad OWNER TO rafa;

--
-- TOC entry 1961 (class 1259 OID 26644)
-- Dependencies: 10
-- Name: profesor_para_grupo; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE profesor_para_grupo (
    profesor_para_actividad character varying NOT NULL,
    grupo character varying NOT NULL
);


ALTER TABLE public.profesor_para_grupo OWNER TO rafa;

--
-- TOC entry 1962 (class 1259 OID 26650)
-- Dependencies: 10
-- Name: receso; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE receso (
    periodo character varying NOT NULL,
    despues_del_turno integer NOT NULL,
    duracion double precision
);


ALTER TABLE public.receso OWNER TO rafa;

--
-- TOC entry 1963 (class 1259 OID 26656)
-- Dependencies: 2369 2370 10
-- Name: restriccion_de_asignatura; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_de_asignatura (
    id character varying NOT NULL,
    asignatura character varying NOT NULL,
    periodo character varying,
    dia integer,
    turno integer,
    valor integer DEFAULT 1,
    CONSTRAINT restriccion_de_asignatura_valor_check CHECK ((valor > 0))
);


ALTER TABLE public.restriccion_de_asignatura OWNER TO rafa;

--
-- TOC entry 1964 (class 1259 OID 26664)
-- Dependencies: 2371 2372 2373 10
-- Name: restriccion_de_brigada; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_de_brigada (
    id character varying NOT NULL,
    brigada character varying NOT NULL,
    periodo character varying NOT NULL,
    dia integer,
    turno integer,
    valor integer DEFAULT 1,
    por_actividad boolean DEFAULT false,
    actividad character varying,
    CONSTRAINT restriccion_de_brigada_valor_check CHECK ((valor > 0))
);


ALTER TABLE public.restriccion_de_brigada OWNER TO rafa;

--
-- TOC entry 1965 (class 1259 OID 26673)
-- Dependencies: 2374 2375 10
-- Name: restriccion_de_clasif_act; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_de_clasif_act (
    id character varying NOT NULL,
    clasificacion character varying NOT NULL,
    periodo character varying,
    dia integer,
    turno integer,
    valor integer DEFAULT 1,
    CONSTRAINT restriccion_de_clasif_act_valor_check CHECK ((valor > 0))
);


ALTER TABLE public.restriccion_de_clasif_act OWNER TO rafa;

--
-- TOC entry 1966 (class 1259 OID 26681)
-- Dependencies: 2376 2377 10
-- Name: restriccion_de_especialidad; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_de_especialidad (
    id character varying NOT NULL,
    especialidad character varying NOT NULL,
    periodo character varying NOT NULL,
    dia integer,
    turno integer,
    valor integer DEFAULT 1,
    CONSTRAINT restriccion_de_especialidad_valor_check CHECK ((valor > 0))
);


ALTER TABLE public.restriccion_de_especialidad OWNER TO rafa;

--
-- TOC entry 1967 (class 1259 OID 26689)
-- Dependencies: 2378 2379 2380 10
-- Name: restriccion_de_lugar; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_de_lugar (
    id character varying NOT NULL,
    lugar character varying NOT NULL,
    periodo character varying,
    dia integer,
    turno integer,
    valor integer DEFAULT 1,
    por_actividad boolean DEFAULT false,
    actividad character varying,
    CONSTRAINT restriccion_de_lugar_valor_check CHECK ((valor > 0))
);


ALTER TABLE public.restriccion_de_lugar OWNER TO rafa;

--
-- TOC entry 1968 (class 1259 OID 26698)
-- Dependencies: 2381 2382 2383 10
-- Name: restriccion_de_otro_recurso; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_de_otro_recurso (
    id character varying NOT NULL,
    otro_recurso character varying NOT NULL,
    periodo character varying,
    dia integer,
    turno integer,
    valor integer DEFAULT 1,
    por_actividad boolean DEFAULT false,
    actividad character varying,
    CONSTRAINT restriccion_de_otro_recurso_valor_check CHECK ((valor > 0))
);


ALTER TABLE public.restriccion_de_otro_recurso OWNER TO rafa;

--
-- TOC entry 1969 (class 1259 OID 26707)
-- Dependencies: 2384 2385 10
-- Name: restriccion_de_periodo; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_de_periodo (
    id character varying NOT NULL,
    periodo character varying NOT NULL,
    dia integer,
    turno integer,
    valor integer DEFAULT 1,
    CONSTRAINT restriccion_de_periodo_valor_check CHECK ((valor > 0))
);


ALTER TABLE public.restriccion_de_periodo OWNER TO rafa;

--
-- TOC entry 1970 (class 1259 OID 26715)
-- Dependencies: 2386 2387 2388 10
-- Name: restriccion_de_profesor; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_de_profesor (
    id character varying NOT NULL,
    profesor character varying NOT NULL,
    periodo character varying,
    dia integer,
    turno integer,
    valor integer DEFAULT 1,
    por_actividad boolean DEFAULT false,
    actividad character varying,
    CONSTRAINT restriccion_de_profesor_valor_check CHECK ((valor > 0))
);


ALTER TABLE public.restriccion_de_profesor OWNER TO rafa;

--
-- TOC entry 1971 (class 1259 OID 26724)
-- Dependencies: 10
-- Name: restriccion_por_actividad; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_por_actividad (
    restriccion_nominal character varying NOT NULL,
    actividad character varying NOT NULL
);


ALTER TABLE public.restriccion_por_actividad OWNER TO rafa;

--
-- TOC entry 1972 (class 1259 OID 26730)
-- Dependencies: 1971 10
-- Name: restriccion_por_actividad_de_brigada; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_por_actividad_de_brigada (
)
INHERITS (restriccion_por_actividad);


ALTER TABLE public.restriccion_por_actividad_de_brigada OWNER TO rafa;

--
-- TOC entry 1973 (class 1259 OID 26736)
-- Dependencies: 10 1971
-- Name: restriccion_por_actividad_de_lugar; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_por_actividad_de_lugar (
)
INHERITS (restriccion_por_actividad);


ALTER TABLE public.restriccion_por_actividad_de_lugar OWNER TO rafa;

--
-- TOC entry 1974 (class 1259 OID 26742)
-- Dependencies: 10 1971
-- Name: restriccion_por_actividad_de_otro_recurso; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_por_actividad_de_otro_recurso (
)
INHERITS (restriccion_por_actividad);


ALTER TABLE public.restriccion_por_actividad_de_otro_recurso OWNER TO rafa;

--
-- TOC entry 1975 (class 1259 OID 26748)
-- Dependencies: 10 1971
-- Name: restriccion_por_actividad_de_profesor; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_por_actividad_de_profesor (
)
INHERITS (restriccion_por_actividad);


ALTER TABLE public.restriccion_por_actividad_de_profesor OWNER TO rafa;

--
-- TOC entry 1976 (class 1259 OID 26754)
-- Dependencies: 2389 10
-- Name: restriccion_real; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_real (
    restriccion_nominal character varying NOT NULL,
    periodicidad character varying NOT NULL,
    fecha bigint,
    hora_inicial double precision,
    hora_final double precision,
    por_actividad boolean NOT NULL,
    actividad character varying,
    objeto character varying NOT NULL,
    CONSTRAINT restriccion_real_check CHECK ((hora_final > hora_inicial))
);


ALTER TABLE public.restriccion_real OWNER TO rafa;

--
-- TOC entry 1977 (class 1259 OID 26761)
-- Dependencies: 2390 10 1976
-- Name: restriccion_real_de_asignatura; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_real_de_asignatura (
)
INHERITS (restriccion_real);


ALTER TABLE public.restriccion_real_de_asignatura OWNER TO rafa;

--
-- TOC entry 1978 (class 1259 OID 26768)
-- Dependencies: 2391 10 1976
-- Name: restriccion_real_de_brigada; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_real_de_brigada (
)
INHERITS (restriccion_real);


ALTER TABLE public.restriccion_real_de_brigada OWNER TO rafa;

--
-- TOC entry 1979 (class 1259 OID 26775)
-- Dependencies: 2392 10 1976
-- Name: restriccion_real_de_clasif_act; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_real_de_clasif_act (
)
INHERITS (restriccion_real);


ALTER TABLE public.restriccion_real_de_clasif_act OWNER TO rafa;

--
-- TOC entry 1980 (class 1259 OID 26782)
-- Dependencies: 2393 1976 10
-- Name: restriccion_real_de_especialidad; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_real_de_especialidad (
)
INHERITS (restriccion_real);


ALTER TABLE public.restriccion_real_de_especialidad OWNER TO rafa;

--
-- TOC entry 1981 (class 1259 OID 26789)
-- Dependencies: 2394 10 1976
-- Name: restriccion_real_de_lugar; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_real_de_lugar (
)
INHERITS (restriccion_real);


ALTER TABLE public.restriccion_real_de_lugar OWNER TO rafa;

--
-- TOC entry 1982 (class 1259 OID 26796)
-- Dependencies: 2395 1976 10
-- Name: restriccion_real_de_otro_recurso; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_real_de_otro_recurso (
)
INHERITS (restriccion_real);


ALTER TABLE public.restriccion_real_de_otro_recurso OWNER TO rafa;

--
-- TOC entry 1983 (class 1259 OID 26803)
-- Dependencies: 2396 1976 10
-- Name: restriccion_real_de_periodo; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_real_de_periodo (
)
INHERITS (restriccion_real);


ALTER TABLE public.restriccion_real_de_periodo OWNER TO rafa;

--
-- TOC entry 1984 (class 1259 OID 26810)
-- Dependencies: 2397 1976 10
-- Name: restriccion_real_de_profesor; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE restriccion_real_de_profesor (
)
INHERITS (restriccion_real);


ALTER TABLE public.restriccion_real_de_profesor OWNER TO rafa;

--
-- TOC entry 1985 (class 1259 OID 26817)
-- Dependencies: 2081 10
-- Name: uso_de_otros_recursos; Type: VIEW; Schema: public; Owner: rafa
--

CREATE VIEW uso_de_otros_recursos AS
    SELECT otros_recursos_usados.otro_recurso, count(otros_recursos_usados.actividad) AS count FROM otros_recursos_usados GROUP BY otros_recursos_usados.otro_recurso;


ALTER TABLE public.uso_de_otros_recursos OWNER TO rafa;

--
-- TOC entry 1986 (class 1259 OID 26821)
-- Dependencies: 2398 2399 10
-- Name: zpriori; Type: TABLE; Schema: public; Owner: rafa; Tablespace: 
--

CREATE TABLE zpriori (
    clasificacion character varying NOT NULL,
    periodo character varying NOT NULL,
    dia integer NOT NULL,
    turno integer NOT NULL,
    valor integer DEFAULT 1,
    CONSTRAINT zpriori_valor_check CHECK ((valor > 0))
);


ALTER TABLE public.zpriori OWNER TO rafa;

SET search_path = seguridad, pg_catalog;

--
-- TOC entry 1987 (class 1259 OID 26829)
-- Dependencies: 2400 8
-- Name: host; Type: TABLE; Schema: seguridad; Owner: rafa; Tablespace: 
--

CREATE TABLE host (
    ip character varying DEFAULT '*'::character varying NOT NULL,
    usuario character varying NOT NULL
);


ALTER TABLE seguridad.host OWNER TO rafa;

--
-- TOC entry 1988 (class 1259 OID 26836)
-- Dependencies: 8
-- Name: permiso; Type: TABLE; Schema: seguridad; Owner: rafa; Tablespace: 
--

CREATE TABLE permiso (
    permiso character varying NOT NULL,
    rol character varying NOT NULL
);


ALTER TABLE seguridad.permiso OWNER TO rafa;

--
-- TOC entry 1989 (class 1259 OID 26842)
-- Dependencies: 8
-- Name: rol; Type: TABLE; Schema: seguridad; Owner: rafa; Tablespace: 
--

CREATE TABLE rol (
    nombre character varying NOT NULL,
    descripcion character varying
);


ALTER TABLE seguridad.rol OWNER TO rafa;

--
-- TOC entry 1990 (class 1259 OID 26848)
-- Dependencies: 8
-- Name: rol_de_usuario; Type: TABLE; Schema: seguridad; Owner: rafa; Tablespace: 
--

CREATE TABLE rol_de_usuario (
    usuario character varying NOT NULL,
    rol character varying NOT NULL
);


ALTER TABLE seguridad.rol_de_usuario OWNER TO rafa;

--
-- TOC entry 1991 (class 1259 OID 26854)
-- Dependencies: 2401 8
-- Name: traza; Type: TABLE; Schema: seguridad; Owner: rafa; Tablespace: 
--

CREATE TABLE traza (
    fecha timestamp without time zone DEFAULT now(),
    usuario character varying NOT NULL,
    accion character varying,
    ip character varying
);


ALTER TABLE seguridad.traza OWNER TO rafa;

--
-- TOC entry 1992 (class 1259 OID 26861)
-- Dependencies: 2402 2403 8
-- Name: usuario; Type: TABLE; Schema: seguridad; Owner: rafa; Tablespace: 
--

CREATE TABLE usuario (
    id character varying DEFAULT public.crear_id_de_replica('ancoragoh'::character varying, 'seguridad.usuario'::character varying, 'id'::character varying, 'seguridad.usuario'::character varying) NOT NULL,
    nombre character varying NOT NULL,
    info character varying NOT NULL,
    correo character varying NOT NULL,
    momento_ingreso double precision DEFAULT tiempo.moment_now() NOT NULL,
    nick character varying NOT NULL,
    contrasena character varying NOT NULL
);


ALTER TABLE seguridad.usuario OWNER TO rafa;

SET search_path = generador, pg_catalog;

--
-- TOC entry 2765 (class 0 OID 36211)
-- Dependencies: 1993
-- Data for Name: cancelar; Type: TABLE DATA; Schema: generador; Owner: postgres
--

COPY cancelar (especialidad, nivel, periodo) FROM stdin;
\.


SET search_path = public, pg_catalog;

--
-- TOC entry 2711 (class 0 OID 26466)
-- Dependencies: 1937
-- Data for Name: acceso_prohibido; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY acceso_prohibido (grupo, lugar) FROM stdin;
\.


--
-- TOC entry 2708 (class 0 OID 26378)
-- Dependencies: 1934
-- Data for Name: actividad; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY actividad (id, plantilla, grupo, profesor, lugar, dia, turno, momento_de_colocacion, creador) FROM stdin;
ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	2	1	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	2	6	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	plantilla_de_actividad200610_0200121276992012k03rn80azivwryv7pxp82aclhc0e9yf02gb05pvy72lhgagfmtwwxgwf5kx7dzype1g5gkd10y8g1zw3f3yyp1bom6gcddcj	grupo190610_0525371276961137rzo3dgswxcnsgw6rlsl0f0u0k1993am5jtcf4r94awnm0ffyl2xjc1ze51xz30l9pidn7y373b2bgt51	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	1	1	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	plantilla_de_actividad200610_0200121276992012k03rn80azivwryv7pxp82aclhc0e9yf02gb05pvy72lhgagfmtwwxgwf5kx7dzype1g5gkd10y8g1zw3f3yyp1bom6gcddcj	grupo190610_05254312769611437npmmif4uzmf4y904srn1068msv147xo8rr05ebelf57tp47jkqhyuzpsxbx0lqr49oain4489iaftugvqk	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	1	2	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	plantilla_de_actividad200610_0200121276992012k03rn80azivwryv7pxp82aclhc0e9yf02gb05pvy72lhgagfmtwwxgwf5kx7dzype1g5gkd10y8g1zw3f3yyp1bom6gcddcj	grupo190610_0525481276961148rtolufn9aee0u3gpqbq7ofrsvbg530aag60uraux8ky4ha5hvb0m21wn97y767w01uqhjjw	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	2	1	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	plantilla_de_actividad200610_0200121276992012k03rn80azivwryv7pxp82aclhc0e9yf02gb05pvy72lhgagfmtwwxgwf5kx7dzype1g5gkd10y8g1zw3f3yyp1bom6gcddcj	grupo190610_0525301276961130nz0n34be8bnw1ayhz3gz40vrsygo2rv1brdoljozc8zric2el21pkwo2u298gzemuwd3zdwxcg81	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	1	6	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	plantilla_de_actividad200610_0200121276992012k03rn80azivwryv7pxp82aclhc0e9yf02gb05pvy72lhgagfmtwwxgwf5kx7dzype1g5gkd10y8g1zw3f3yyp1bom6gcddcj	grupo180610_0355511276869351d20t2ryrtwlz8jcb60y548n83w5xiim7dlu9692zs2vqr4md3zy86u64y9ud10cw9ia153ykmj2v8	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	1	5	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	grupo190610_0525371276961137rzo3dgswxcnsgw6rlsl0f0u0k1993am5jtcf4r94awnm0ffyl2xjc1ze51xz30l9pidn7y373b2bgt51	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	2	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	grupo190610_05254312769611437npmmif4uzmf4y904srn1068msv147xo8rr05ebelf57tp47jkqhyuzpsxbx0lqr49oain4489iaftugvqk	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	1	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	grupo190610_0525481276961148rtolufn9aee0u3gpqbq7ofrsvbg530aag60uraux8ky4ha5hvb0m21wn97y767w01uqhjjw	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	1	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	grupo190610_0525301276961130nz0n34be8bnw1ayhz3gz40vrsygo2rv1brdoljozc8zric2el21pkwo2u298gzemuwd3zdwxcg81	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	5	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	grupo180610_0355511276869351d20t2ryrtwlz8jcb60y548n83w5xiim7dlu9692zs2vqr4md3zy86u64y9ud10cw9ia153ykmj2v8	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	6	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad	plantilla_de_actividad070610_0335031275939303nh2edym9zzglqii3xikl5q64daqms6k7dcag1neqck2stalgiwsddn7goms7h44k65rwhwdk764q6fwd2eg5sca7pt4xmx9ir	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	1	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad	plantilla_de_actividad070610_0335031275939303nh2edym9zzglqii3xikl5q64daqms6k7dcag1neqck2stalgiwsddn7goms7h44k65rwhwdk764q6fwd2eg5sca7pt4xmx9ir	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	6	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad	plantilla_de_actividad200610_0202161276992136wvjvanuozqag2br736ja7ehryuz4hot9dmdoowhc28hpjs9sqc31s4td9sua7lzs	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	2	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad	plantilla_de_actividad200610_0202161276992136wvjvanuozqag2br736ja7ehryuz4hot9dmdoowhc28hpjs9sqc31s4td9sua7lzs	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	5	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-4j6h9xj781z17uq4wdh-actividad	plantilla_de_actividad200610_0200181276992018ygdotqf89osf3044pwddxm015vv95mqraczsfz82v2irjghmpq7w9t0ah6rzarbqz4zq	grupo190610_0525371276961137rzo3dgswxcnsgw6rlsl0f0u0k1993am5jtcf4r94awnm0ffyl2xjc1ze51xz30l9pidn7y373b2bgt51	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	1	1	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-2qlvbuuf1gstmwi621fq-actividad	plantilla_de_actividad200610_0200181276992018ygdotqf89osf3044pwddxm015vv95mqraczsfz82v2irjghmpq7w9t0ah6rzarbqz4zq	grupo190610_05254312769611437npmmif4uzmf4y904srn1068msv147xo8rr05ebelf57tp47jkqhyuzpsxbx0lqr49oain4489iaftugvqk	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	1	4	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-uu4d0ms9imgd17js9kvh-actividad	plantilla_de_actividad200610_0200181276992018ygdotqf89osf3044pwddxm015vv95mqraczsfz82v2irjghmpq7w9t0ah6rzarbqz4zq	grupo190610_0525481276961148rtolufn9aee0u3gpqbq7ofrsvbg530aag60uraux8ky4ha5hvb0m21wn97y767w01uqhjjw	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	1	2	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-jjfo5peus2gi1oqflxnb-actividad	plantilla_de_actividad200610_0200181276992018ygdotqf89osf3044pwddxm015vv95mqraczsfz82v2irjghmpq7w9t0ah6rzarbqz4zq	grupo190610_0525301276961130nz0n34be8bnw1ayhz3gz40vrsygo2rv1brdoljozc8zric2el21pkwo2u298gzemuwd3zdwxcg81	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	1	5	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-gz5metcrgni2sz9is4k-actividad	plantilla_de_actividad200610_0200181276992018ygdotqf89osf3044pwddxm015vv95mqraczsfz82v2irjghmpq7w9t0ah6rzarbqz4zq	grupo180610_0355511276869351d20t2ryrtwlz8jcb60y548n83w5xiim7dlu9692zs2vqr4md3zy86u64y9ud10cw9ia153ykmj2v8	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	1	3	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-rnjxg0d6i6mgnvl6cekc-actividad	plantilla_de_actividad200610_0203401276992220qca8jcpu2gf89a4olyz231sp5lqhq0jpe5e92s3e7br3qb7k7wjrinky7ni1	grupo190610_0525371276961137rzo3dgswxcnsgw6rlsl0f0u0k1993am5jtcf4r94awnm0ffyl2xjc1ze51xz30l9pidn7y373b2bgt51	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	2	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-993bfq5nzu2cg8n0op0-actividad	plantilla_de_actividad200610_0203401276992220qca8jcpu2gf89a4olyz231sp5lqhq0jpe5e92s3e7br3qb7k7wjrinky7ni1	grupo190610_05254312769611437npmmif4uzmf4y904srn1068msv147xo8rr05ebelf57tp47jkqhyuzpsxbx0lqr49oain4489iaftugvqk	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	3	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-xxcg1opaocqvg1pzn90j-actividad	plantilla_de_actividad200610_0203401276992220qca8jcpu2gf89a4olyz231sp5lqhq0jpe5e92s3e7br3qb7k7wjrinky7ni1	grupo190610_0525481276961148rtolufn9aee0u3gpqbq7ofrsvbg530aag60uraux8ky4ha5hvb0m21wn97y767w01uqhjjw	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	1	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-291k2752pq8hkl0if3hu-actividad	plantilla_de_actividad200610_0203401276992220qca8jcpu2gf89a4olyz231sp5lqhq0jpe5e92s3e7br3qb7k7wjrinky7ni1	grupo190610_0525301276961130nz0n34be8bnw1ayhz3gz40vrsygo2rv1brdoljozc8zric2el21pkwo2u298gzemuwd3zdwxcg81	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	6	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-hufh2wrihcfmjyktjbnr-actividad	plantilla_de_actividad200610_0203401276992220qca8jcpu2gf89a4olyz231sp5lqhq0jpe5e92s3e7br3qb7k7wjrinky7ni1	grupo180610_0355511276869351d20t2ryrtwlz8jcb60y548n83w5xiim7dlu9692zs2vqr4md3zy86u64y9ud10cw9ia153ykmj2v8	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	5	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-z0p3fdhkzlurpgietr8s-actividad	plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	grupo190610_0525371276961137rzo3dgswxcnsgw6rlsl0f0u0k1993am5jtcf4r94awnm0ffyl2xjc1ze51xz30l9pidn7y373b2bgt51	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	3	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-dheqwmhkype1m2nmjghf-actividad	plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	grupo190610_05254312769611437npmmif4uzmf4y904srn1068msv147xo8rr05ebelf57tp47jkqhyuzpsxbx0lqr49oain4489iaftugvqk	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	2	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-pi7oqevd0muvj3oqnyyj-actividad	plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	grupo190610_0525481276961148rtolufn9aee0u3gpqbq7ofrsvbg530aag60uraux8ky4ha5hvb0m21wn97y767w01uqhjjw	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	2	5	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-8s77vuzj442o0pmbxsa-actividad	plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	grupo190610_0525301276961130nz0n34be8bnw1ayhz3gz40vrsygo2rv1brdoljozc8zric2el21pkwo2u298gzemuwd3zdwxcg81	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	4	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:34:25.368+02-0toqess6ildwgfm5g0x4-actividad	plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	grupo180610_0355511276869351d20t2ryrtwlz8jcb60y548n83w5xiim7dlu9692zs2vqr4md3zy86u64y9ud10cw9ia153ykmj2v8	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	4	3304316.6499999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	plantilla_de_actividad170410_0446071271537168lmle7f8bt1csvt516s4q1ef6aonzc1vg2fpanc9o4s2eo79l0i	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	1	1	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	plantilla_de_actividad170410_0446071271537168lmle7f8bt1csvt516s4q1ef6aonzc1vg2fpanc9o4s2eo79l0i	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	1	6	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	plantilla_de_actividad180610_0411261276870286ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwma	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	1	2	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	plantilla_de_actividad180610_0411261276870286ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwma	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	1	5	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	plantilla_de_actividad200610_0202111276992131t6jkbs7dolr0fclro9ju2yyxlro1yd2k71wkvclg6ny9kncc0ilrsz3y00kw05h3xqmfstdfzlj33ths7n1	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	3	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	plantilla_de_actividad200610_0202111276992131t6jkbs7dolr0fclro9ju2yyxlro1yd2k71wkvclg6ny9kncc0ilrsz3y00kw05h3xqmfstdfzlj33ths7n1	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	4	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	plantilla_de_actividad200610_0203311276992211m3pt962xna4kmueucmvbkz16hrrvtbllshix5myy2kv09nsyyqmmw5ucaphrvtxwta7ew	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	4	3304313.6699999999	admin-superuser
ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	plantilla_de_actividad200610_0203311276992211m3pt962xna4kmueucmvbkz16hrrvtbllshix5myy2kv09nsyyqmmw5ucaphrvtxwta7ew	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	3	3304313.6699999999	admin-superuser
\.


--
-- TOC entry 2712 (class 0 OID 26472)
-- Dependencies: 1938
-- Data for Name: asignatura; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY asignatura (id, descripcion, especialidad, nivel, creador) FROM stdin;
asignatura140410_04335112712772314ldxk0857hpjazc3mpvvp14djl1d1mqtxuh8te2qlhzl73eji15xrx119q4z4kjq6qoou	Matemática I	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	admin-superuser
asignatura150610_11063312766359937e82ayvpuya8bjodn0pkb0mna57x3wj0k9fza74x8d0hsv4gzad80paklpkwhfe1ufja7t8jg6365lf	Calculo 2	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	admin-superuser
asignatura200610_0159331276991973cmz7iu7mshbcomk64uogkav22iuazlu1c7kkmb0trml497u82lojmxdxxtkqhckr841dl	Filosofia	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	admin-superuser
asignatura200610_01594412769919847el5r8o5znuok1jyq8pj7jvg6vydcifd1ca1tp1f1fi7cvbdzucloq5ymck2j36yoovaql84xm32z4z64vv22a0uy824my	Programacion	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	admin-superuser
asignatura200610_0159561276991996x9ecwju06p6i0hoia40av09037gfik090ohox8h8ld4tvvnxtz2j3dk6p03naewlzzxwbrh2ew	Educacion Fisica	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	admin-superuser
asignatura250710_02273412800608544ws7cg1a02ko3hfwh532pufrhx2420un38aisjcbxoi4pephv4gkklpyl0xp5v3fvum941jphobw0pwh5grhbqvjkxop4v7llw	mat	especialidad210610_12505612771174563mszdxinopprpcshpnqcdy2gx6nf3k2c317slujvwy37om0t6p3c2lzwk3y2higvxrg1p7eizkbzhamjg7aqlltp4d51qvqso	nivel110410_0757581271030278ydxq95p4km2zad1n0a7tsuoor24xibiuvoj58j9ybw94l9mrvizng8ez99hhi6bd7uidjt6ph3t4a1mqum6chfh73c9wwuis	admin-superuser
\.


--
-- TOC entry 2705 (class 0 OID 26251)
-- Dependencies: 1931
-- Data for Name: brigada; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY brigada (id, descripcion, especialidad, nivel, matricula, creador) FROM stdin;
brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	if-13	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	0	admin-superuser
brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	if-15	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	0	admin-superuser
brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s	if-14	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	0	admin-superuser
brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	if-12	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	0	admin-superuser
brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	If-11	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	0	admin-superuser
brigada250710_0226231280060783yejwaxdrftbbu5lfihwfwzg7z72188srjngshmaxznyjiwpf2x0z2ut2g	e1	especialidad210610_12505612771174563mszdxinopprpcshpnqcdy2gx6nf3k2c317slujvwy37om0t6p3c2lzwk3y2higvxrg1p7eizkbzhamjg7aqlltp4d51qvqso	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	0	admin-superuser
\.


--
-- TOC entry 2714 (class 0 OID 26494)
-- Dependencies: 1941
-- Data for Name: brigada_de_grupo; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY brigada_de_grupo (brigada, grupo) FROM stdin;
brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j
brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6
brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6
brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j
brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	grupo180610_0355511276869351d20t2ryrtwlz8jcb60y548n83w5xiim7dlu9692zs2vqr4md3zy86u64y9ud10cw9ia153ykmj2v8
brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	grupo190610_0525301276961130nz0n34be8bnw1ayhz3gz40vrsygo2rv1brdoljozc8zric2el21pkwo2u298gzemuwd3zdwxcg81
brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	grupo190610_0525371276961137rzo3dgswxcnsgw6rlsl0f0u0k1993am5jtcf4r94awnm0ffyl2xjc1ze51xz30l9pidn7y373b2bgt51
brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s	grupo190610_05254312769611437npmmif4uzmf4y904srn1068msv147xo8rr05ebelf57tp47jkqhyuzpsxbx0lqr49oain4489iaftugvqk
brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	grupo190610_0525481276961148rtolufn9aee0u3gpqbq7ofrsvbg530aag60uraux8ky4ha5hvb0m21wn97y767w01uqhjjw
\.


--
-- TOC entry 2715 (class 0 OID 26500)
-- Dependencies: 1942
-- Data for Name: clasificacion_de_actividad; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY clasificacion_de_actividad (id, descripcion, duracion, creador, prioridad, gasto) FROM stdin;
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	Conferencia	1	admin-superuser	1	1
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	Clase practica	1	admin-superuser	2	1
\.


--
-- TOC entry 2716 (class 0 OID 26510)
-- Dependencies: 1943
-- Data for Name: costo_de_traslado; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY costo_de_traslado (origen, destino, costo) FROM stdin;
lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	lugar130410_0355341271188534bpyvy9rafxm5wkmagug32ogyrbazd87fmu1bshby5osqz6r7pxzhc76u86jb	19
lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	lugar210410_09243712718994788crry75ybs1gykuc4bm8hfs5bpcpbhq2qnxq9ifv3x62x0lc3bqd7g18ijfgrvf95bz4d9l6og7zsb9pwoqwrv5okqdl2mzsed	15
\.


--
-- TOC entry 2713 (class 0 OID 26479)
-- Dependencies: 1939
-- Data for Name: desglose; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY desglose (id, asignatura, periodo, min_dias, max_dias, respetar_orden, mismo_dia) FROM stdin;
ancoragoh-2010-04-14 16:33:51.871089-04-lw38rj5yihkvl0tyg9jf-desglose	asignatura140410_04335112712772314ldxk0857hpjazc3mpvvp14djl1d1mqtxuh8te2qlhzl73eji15xrx119q4z4kjq6qoou	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	t	t
ancoragoh-2010-04-14 16:33:51.871089-04-ljx1pd6mpgw13nwjxr86-desglose	asignatura140410_04335112712772314ldxk0857hpjazc3mpvvp14djl1d1mqtxuh8te2qlhzl73eji15xrx119q4z4kjq6qoou	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	3	t	t
ancoragoh-2010-06-20 01:59:33.319+02-urmium8lbhscb6u5l34g-desglose	asignatura200610_0159331276991973cmz7iu7mshbcomk64uogkav22iuazlu1c7kkmb0trml497u82lojmxdxxtkqhckr841dl	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	t	f
ancoragoh-2010-06-15 23:06:33.666+02-25agmd3g6j5lpczws7c9-desglose	asignatura150610_11063312766359937e82ayvpuya8bjodn0pkb0mna57x3wj0k9fza74x8d0hsv4gzad80paklpkwhfe1ufja7t8jg6365lf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	t	f
ancoragoh-2010-06-15 23:06:33.666+02-giwh3fsw5psnbn2dmy1x-desglose	asignatura150610_11063312766359937e82ayvpuya8bjodn0pkb0mna57x3wj0k9fza74x8d0hsv4gzad80paklpkwhfe1ufja7t8jg6365lf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	t	f
ancoragoh-2010-06-20 01:59:33.319+02-ma06g9wju9w9ynjg1ko-desglose	asignatura200610_0159331276991973cmz7iu7mshbcomk64uogkav22iuazlu1c7kkmb0trml497u82lojmxdxxtkqhckr841dl	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	t	f
ancoragoh-2010-06-20 01:59:44.486+02-nfyl3vcrggh3xxocexuo-desglose	asignatura200610_01594412769919847el5r8o5znuok1jyq8pj7jvg6vydcifd1ca1tp1f1fi7cvbdzucloq5ymck2j36yoovaql84xm32z4z64vv22a0uy824my	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	t	f
ancoragoh-2010-06-20 01:59:44.486+02-r3o29hnwzzo650p46f-desglose	asignatura200610_01594412769919847el5r8o5znuok1jyq8pj7jvg6vydcifd1ca1tp1f1fi7cvbdzucloq5ymck2j36yoovaql84xm32z4z64vv22a0uy824my	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	t	f
ancoragoh-2010-06-20 01:59:56.868+02-zqq6izmu24fgf1d6nkn-desglose	asignatura200610_0159561276991996x9ecwju06p6i0hoia40av09037gfik090ohox8h8ld4tvvnxtz2j3dk6p03naewlzzxwbrh2ew	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	t	f
ancoragoh-2010-06-20 01:59:56.868+02-9ddt98n9zwwytet1e6o3-desglose	asignatura200610_0159561276991996x9ecwju06p6i0hoia40av09037gfik090ohox8h8ld4tvvnxtz2j3dk6p03naewlzzxwbrh2ew	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	t	f
ancoragoh-2010-06-21 12:45:40.549+02-4np8py88ylqktd595lyz-desglose	asignatura140410_04335112712772314ldxk0857hpjazc3mpvvp14djl1d1mqtxuh8te2qlhzl73eji15xrx119q4z4kjq6qoou	periodo210610_1245401277117140i650vcl3fwgxglpgnusq9focxqufyl0j1cerbu8qnf7i6xapkpze6l98kyi	1	5	t	f
ancoragoh-2010-06-21 12:45:40.549+02-xtnutyac6ysb1td8mee4-desglose	asignatura150610_11063312766359937e82ayvpuya8bjodn0pkb0mna57x3wj0k9fza74x8d0hsv4gzad80paklpkwhfe1ufja7t8jg6365lf	periodo210610_1245401277117140i650vcl3fwgxglpgnusq9focxqufyl0j1cerbu8qnf7i6xapkpze6l98kyi	1	5	t	f
ancoragoh-2010-06-21 12:45:40.549+02-2mwn1pty1fp4xs5fkkcl-desglose	asignatura200610_0159331276991973cmz7iu7mshbcomk64uogkav22iuazlu1c7kkmb0trml497u82lojmxdxxtkqhckr841dl	periodo210610_1245401277117140i650vcl3fwgxglpgnusq9focxqufyl0j1cerbu8qnf7i6xapkpze6l98kyi	1	5	t	f
ancoragoh-2010-06-21 12:45:40.549+02-z9korqcd0n8f7ylnd7ko-desglose	asignatura200610_01594412769919847el5r8o5znuok1jyq8pj7jvg6vydcifd1ca1tp1f1fi7cvbdzucloq5ymck2j36yoovaql84xm32z4z64vv22a0uy824my	periodo210610_1245401277117140i650vcl3fwgxglpgnusq9focxqufyl0j1cerbu8qnf7i6xapkpze6l98kyi	1	5	t	f
ancoragoh-2010-06-21 12:45:40.549+02-mqm0yp4i8lxyne6q4tbl-desglose	asignatura200610_0159561276991996x9ecwju06p6i0hoia40av09037gfik090ohox8h8ld4tvvnxtz2j3dk6p03naewlzzxwbrh2ew	periodo210610_1245401277117140i650vcl3fwgxglpgnusq9focxqufyl0j1cerbu8qnf7i6xapkpze6l98kyi	1	5	t	f
ancoragoh-2010-06-21 15:35:06.74+02-ps1cvguw8kxniypuzzi1-desglose	asignatura140410_04335112712772314ldxk0857hpjazc3mpvvp14djl1d1mqtxuh8te2qlhzl73eji15xrx119q4z4kjq6qoou	periodo210610_03350612771273062icuozbloj1n5f9xmddy9k9nkr8hq63gaf92dvi6j79bwk0g3qx34w45mu44opsxx9h4fn2krptu3knu	1	5	t	f
ancoragoh-2010-06-21 15:35:06.74+02-2izqomx1or5v94mfx9m3-desglose	asignatura150610_11063312766359937e82ayvpuya8bjodn0pkb0mna57x3wj0k9fza74x8d0hsv4gzad80paklpkwhfe1ufja7t8jg6365lf	periodo210610_03350612771273062icuozbloj1n5f9xmddy9k9nkr8hq63gaf92dvi6j79bwk0g3qx34w45mu44opsxx9h4fn2krptu3knu	1	5	t	f
ancoragoh-2010-06-21 15:35:06.74+02-b68pwy0atbw9w9wpg6q5-desglose	asignatura200610_0159331276991973cmz7iu7mshbcomk64uogkav22iuazlu1c7kkmb0trml497u82lojmxdxxtkqhckr841dl	periodo210610_03350612771273062icuozbloj1n5f9xmddy9k9nkr8hq63gaf92dvi6j79bwk0g3qx34w45mu44opsxx9h4fn2krptu3knu	1	5	t	f
ancoragoh-2010-06-21 15:35:06.74+02-ckbx4t9cggge7ievsefw-desglose	asignatura200610_01594412769919847el5r8o5znuok1jyq8pj7jvg6vydcifd1ca1tp1f1fi7cvbdzucloq5ymck2j36yoovaql84xm32z4z64vv22a0uy824my	periodo210610_03350612771273062icuozbloj1n5f9xmddy9k9nkr8hq63gaf92dvi6j79bwk0g3qx34w45mu44opsxx9h4fn2krptu3knu	1	5	t	f
ancoragoh-2010-06-21 15:35:06.74+02-37aylmofoh5ut6qj4wi-desglose	asignatura200610_0159561276991996x9ecwju06p6i0hoia40av09037gfik090ohox8h8ld4tvvnxtz2j3dk6p03naewlzzxwbrh2ew	periodo210610_03350612771273062icuozbloj1n5f9xmddy9k9nkr8hq63gaf92dvi6j79bwk0g3qx34w45mu44opsxx9h4fn2krptu3knu	1	5	t	f
ancoragoh-2010-07-25 14:25:11.285+02-qy1rw0sjcgn06sgcwbo-desglose	asignatura140410_04335112712772314ldxk0857hpjazc3mpvvp14djl1d1mqtxuh8te2qlhzl73eji15xrx119q4z4kjq6qoou	periodo250710_02251112800607111dp15arl4x9kuxs0wdoh7vmm8hthwt1gqzebpjoham5resfhk6tipbz7bc6cdv4ccxy277hqcowat1y34b	1	5	t	f
ancoragoh-2010-07-25 14:25:11.285+02-4bmihu4p4axsylrdsds-desglose	asignatura150610_11063312766359937e82ayvpuya8bjodn0pkb0mna57x3wj0k9fza74x8d0hsv4gzad80paklpkwhfe1ufja7t8jg6365lf	periodo250710_02251112800607111dp15arl4x9kuxs0wdoh7vmm8hthwt1gqzebpjoham5resfhk6tipbz7bc6cdv4ccxy277hqcowat1y34b	1	5	t	f
ancoragoh-2010-07-25 14:25:11.285+02-q0hcdjbvrk6g08iyqmk4-desglose	asignatura200610_0159331276991973cmz7iu7mshbcomk64uogkav22iuazlu1c7kkmb0trml497u82lojmxdxxtkqhckr841dl	periodo250710_02251112800607111dp15arl4x9kuxs0wdoh7vmm8hthwt1gqzebpjoham5resfhk6tipbz7bc6cdv4ccxy277hqcowat1y34b	1	5	t	f
ancoragoh-2010-07-25 14:25:11.285+02-xvhmvga765r8lftcdidn-desglose	asignatura200610_01594412769919847el5r8o5znuok1jyq8pj7jvg6vydcifd1ca1tp1f1fi7cvbdzucloq5ymck2j36yoovaql84xm32z4z64vv22a0uy824my	periodo250710_02251112800607111dp15arl4x9kuxs0wdoh7vmm8hthwt1gqzebpjoham5resfhk6tipbz7bc6cdv4ccxy277hqcowat1y34b	1	5	t	f
ancoragoh-2010-07-25 14:25:11.285+02-i0thoqe3ern80exmrchv-desglose	asignatura200610_0159561276991996x9ecwju06p6i0hoia40av09037gfik090ohox8h8ld4tvvnxtz2j3dk6p03naewlzzxwbrh2ew	periodo250710_02251112800607111dp15arl4x9kuxs0wdoh7vmm8hthwt1gqzebpjoham5resfhk6tipbz7bc6cdv4ccxy277hqcowat1y34b	1	5	t	f
ancoragoh-2010-07-25 14:27:34.02+02-kv3ty7teizs6dk5givqv-desglose	asignatura250710_02273412800608544ws7cg1a02ko3hfwh532pufrhx2420un38aisjcbxoi4pephv4gkklpyl0xp5v3fvum941jphobw0pwh5grhbqvjkxop4v7llw	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	t	f
ancoragoh-2010-07-25 14:27:34.02+02-qtqjrptcvtmfjshyb34g-desglose	asignatura250710_02273412800608544ws7cg1a02ko3hfwh532pufrhx2420un38aisjcbxoi4pephv4gkklpyl0xp5v3fvum941jphobw0pwh5grhbqvjkxop4v7llw	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	t	f
ancoragoh-2010-07-25 14:27:34.02+02-28qhrecmsyc5tpmvrtx1-desglose	asignatura250710_02273412800608544ws7cg1a02ko3hfwh532pufrhx2420un38aisjcbxoi4pephv4gkklpyl0xp5v3fvum941jphobw0pwh5grhbqvjkxop4v7llw	periodo210610_1245401277117140i650vcl3fwgxglpgnusq9focxqufyl0j1cerbu8qnf7i6xapkpze6l98kyi	1	5	t	f
ancoragoh-2010-07-25 14:27:34.02+02-zyjlafq9n2rbe00nooag-desglose	asignatura250710_02273412800608544ws7cg1a02ko3hfwh532pufrhx2420un38aisjcbxoi4pephv4gkklpyl0xp5v3fvum941jphobw0pwh5grhbqvjkxop4v7llw	periodo210610_03350612771273062icuozbloj1n5f9xmddy9k9nkr8hq63gaf92dvi6j79bwk0g3qx34w45mu44opsxx9h4fn2krptu3knu	1	5	t	f
ancoragoh-2010-07-25 14:27:34.02+02-mq1zya6k0x9dj52270ps-desglose	asignatura250710_02273412800608544ws7cg1a02ko3hfwh532pufrhx2420un38aisjcbxoi4pephv4gkklpyl0xp5v3fvum941jphobw0pwh5grhbqvjkxop4v7llw	periodo250710_02251112800607111dp15arl4x9kuxs0wdoh7vmm8hthwt1gqzebpjoham5resfhk6tipbz7bc6cdv4ccxy277hqcowat1y34b	1	5	t	f
\.


--
-- TOC entry 2717 (class 0 OID 26516)
-- Dependencies: 1944
-- Data for Name: especialidad; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY especialidad (id, descripcion, creador) FROM stdin;
especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	Ingenieria Informatica	admin-superuser
especialidad210610_12505612771174563mszdxinopprpcshpnqcdy2gx6nf3k2c317slujvwy37om0t6p3c2lzwk3y2higvxrg1p7eizkbzhamjg7aqlltp4d51qvqso	Electrica	admin-superuser
\.


--
-- TOC entry 2718 (class 0 OID 26523)
-- Dependencies: 1945
-- Data for Name: excepcion_de_hrt; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY excepcion_de_hrt (herencia, periodo, dia, turno) FROM stdin;
\.


--
-- TOC entry 2706 (class 0 OID 26299)
-- Dependencies: 1932
-- Data for Name: grupo; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY grupo (id, clasificacion, descripcion, especialidad, nivel, planificacion) FROM stdin;
grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j	clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	g2	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j
grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6	clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	g1	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j
grupo070610_0416241275941784irrnpo4tvy6rl0nfx45w4e0sekhzmvkud39sg2c3q8j37x8tq3fj7f3ap013vbmy4kgaci3sgclc1iw	clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	1	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel110410_07575112710302717jr5ci9dukfxsy6pres9v46fqslrdoz9ghnb1fexp8nlk4799rccrg1wrvidc9pp40wd4	planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j
grupo070610_0416301275941790a3q88j8niyf5zsss3zt69moeozhf85m9w46uc49lrefgwyzonjkmvyr0nyfl	clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	2	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel110410_07575112710302717jr5ci9dukfxsy6pres9v46fqslrdoz9ghnb1fexp8nlk4799rccrg1wrvidc9pp40wd4	planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j
grupo180610_0355511276869351d20t2ryrtwlz8jcb60y548n83w5xiim7dlu9692zs2vqr4md3zy86u64y9ud10cw9ia153ykmj2v8	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	g1	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j
grupo190610_0525301276961130nz0n34be8bnw1ayhz3gz40vrsygo2rv1brdoljozc8zric2el21pkwo2u298gzemuwd3zdwxcg81	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	g2	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j
grupo190610_0525371276961137rzo3dgswxcnsgw6rlsl0f0u0k1993am5jtcf4r94awnm0ffyl2xjc1ze51xz30l9pidn7y373b2bgt51	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	g3	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j
grupo190610_05254312769611437npmmif4uzmf4y904srn1068msv147xo8rr05ebelf57tp47jkqhyuzpsxbx0lqr49oain4489iaftugvqk	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	g4	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j
grupo190610_0525481276961148rtolufn9aee0u3gpqbq7ofrsvbg530aag60uraux8ky4ha5hvb0m21wn97y767w01uqhjjw	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	g5	especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e	nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j
\.


--
-- TOC entry 2719 (class 0 OID 26531)
-- Dependencies: 1946
-- Data for Name: herencia_de_restricciones; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY herencia_de_restricciones (id, planificacion, activa, hijo_brigada, hijo_profesor, hijo_lugar, hijo_otro_recurso, padre_brigada, padre_profesor, padre_lugar, padre_otro_recurso) FROM stdin;
\.


--
-- TOC entry 2720 (class 0 OID 26540)
-- Dependencies: 1947
-- Data for Name: higiene_para_brigada; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY higiene_para_brigada (periodo, brigada, gasto_maximo_diario) FROM stdin;
\.


--
-- TOC entry 2721 (class 0 OID 26546)
-- Dependencies: 1948
-- Data for Name: higiene_para_profesor; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY higiene_para_profesor (periodo, profesor, gasto_maximo_diario) FROM stdin;
\.


--
-- TOC entry 2707 (class 0 OID 26319)
-- Dependencies: 1933
-- Data for Name: lugar; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY lugar (id, nombre, capacidad, creador) FROM stdin;
lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	201	23	admin-superuser
lugar130410_0355341271188534bpyvy9rafxm5wkmagug32ogyrbazd87fmu1bshby5osqz6r7pxzhc76u86jb	205 - Aula de conferencia	40	admin-superuser
lugar210410_09243712718994788crry75ybs1gykuc4bm8hfs5bpcpbhq2qnxq9ifv3x62x0lc3bqd7g18ijfgrvf95bz4d9l6og7zsb9pwoqwrv5okqdl2mzsed	203	0	admin-superuser
lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	204 - Aula de conferencia	0	admin-superuser
lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	301	30	admin-superuser
lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	302	0	admin-superuser
lugar220410_0924111271985851v3us9kjlt5aa6sbp68b9bfiol2meh7v9xfzieu74u36cs7636qvnfvkvk7w62q60	303	0	admin-superuser
lugar150610_1146151276595175hn5bspio8qjoodxmopwd7c70mcy62bydtdfr143o1j3zud8yymnibx6ydcx66hne3gml4uh3	304 - Aula de clase práctica	25	admin-superuser
lugar150610_1147331276595253a0f0fksix52nqivzavjdmrr67aq7w28s04dua3ynd273jmrg8wxnegdzjecambjy5b7szy16oc8v85jb1b7a2ju	305 - Aula de conferencia	20	admin-superuser
lugar150610_1152041276595524twts0zqhuwv5k8x2v252ry7jn3abkz8qxr6nq2tegvfu2uqay4wl78o589apytgoy1dw4grox8vwdvewwjkihs34mr7h91k	401 - Aula de clase práctica	30	admin-superuser
lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	cancha	0	admin-superuser
\.


--
-- TOC entry 2722 (class 0 OID 26552)
-- Dependencies: 1949
-- Data for Name: lugar_fijo; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY lugar_fijo (grupo, lugar) FROM stdin;
\.


--
-- TOC entry 2723 (class 0 OID 26558)
-- Dependencies: 1950
-- Data for Name: lugar_para_actividad; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY lugar_para_actividad (plantilla, lugar, prioridad, actualizacion_interna) FROM stdin;
plantilla_de_actividad070610_0335031275939303nh2edym9zzglqii3xikl5q64daqms6k7dcag1neqck2stalgiwsddn7goms7h44k65rwhwdk764q6fwd2eg5sca7pt4xmx9ir	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	f
plantilla_de_actividad170410_0446071271537168lmle7f8bt1csvt516s4q1ef6aonzc1vg2fpanc9o4s2eo79l0i	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	1	f
plantilla_de_actividad170410_0446071271537168lmle7f8bt1csvt516s4q1ef6aonzc1vg2fpanc9o4s2eo79l0i	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	f
plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	1	f
plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	1	f
plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	lugar150610_1152041276595524twts0zqhuwv5k8x2v252ry7jn3abkz8qxr6nq2tegvfu2uqay4wl78o589apytgoy1dw4grox8vwdvewwjkihs34mr7h91k	1	f
plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	f
plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	lugar130410_0355341271188534bpyvy9rafxm5wkmagug32ogyrbazd87fmu1bshby5osqz6r7pxzhc76u86jb	1	f
plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	f
plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	f
plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	lugar210410_09243712718994788crry75ybs1gykuc4bm8hfs5bpcpbhq2qnxq9ifv3x62x0lc3bqd7g18ijfgrvf95bz4d9l6og7zsb9pwoqwrv5okqdl2mzsed	1	f
plantilla_de_actividad070610_0335031275939303nh2edym9zzglqii3xikl5q64daqms6k7dcag1neqck2stalgiwsddn7goms7h44k65rwhwdk764q6fwd2eg5sca7pt4xmx9ir	lugar210410_09243712718994788crry75ybs1gykuc4bm8hfs5bpcpbhq2qnxq9ifv3x62x0lc3bqd7g18ijfgrvf95bz4d9l6og7zsb9pwoqwrv5okqdl2mzsed	1	f
plantilla_de_actividad070610_0335031275939303nh2edym9zzglqii3xikl5q64daqms6k7dcag1neqck2stalgiwsddn7goms7h44k65rwhwdk764q6fwd2eg5sca7pt4xmx9ir	lugar130410_0355341271188534bpyvy9rafxm5wkmagug32ogyrbazd87fmu1bshby5osqz6r7pxzhc76u86jb	1	f
plantilla_de_actividad070610_0335031275939303nh2edym9zzglqii3xikl5q64daqms6k7dcag1neqck2stalgiwsddn7goms7h44k65rwhwdk764q6fwd2eg5sca7pt4xmx9ir	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	f
plantilla_de_actividad070610_0335031275939303nh2edym9zzglqii3xikl5q64daqms6k7dcag1neqck2stalgiwsddn7goms7h44k65rwhwdk764q6fwd2eg5sca7pt4xmx9ir	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	f
plantilla_de_actividad180610_0411261276870286ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwma	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	f
plantilla_de_actividad170410_0446071271537168lmle7f8bt1csvt516s4q1ef6aonzc1vg2fpanc9o4s2eo79l0i	lugar210410_09243712718994788crry75ybs1gykuc4bm8hfs5bpcpbhq2qnxq9ifv3x62x0lc3bqd7g18ijfgrvf95bz4d9l6og7zsb9pwoqwrv5okqdl2mzsed	1	t
plantilla_de_actividad170410_0446071271537168lmle7f8bt1csvt516s4q1ef6aonzc1vg2fpanc9o4s2eo79l0i	lugar130410_0355341271188534bpyvy9rafxm5wkmagug32ogyrbazd87fmu1bshby5osqz6r7pxzhc76u86jb	1	f
plantilla_de_actividad180610_0411261276870286ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwma	lugar210410_09243712718994788crry75ybs1gykuc4bm8hfs5bpcpbhq2qnxq9ifv3x62x0lc3bqd7g18ijfgrvf95bz4d9l6og7zsb9pwoqwrv5okqdl2mzsed	1	f
plantilla_de_actividad180610_0411261276870286ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwma	lugar130410_0355341271188534bpyvy9rafxm5wkmagug32ogyrbazd87fmu1bshby5osqz6r7pxzhc76u86jb	1	f
plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	f
plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	f
plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	lugar150610_1147331276595253a0f0fksix52nqivzavjdmrr67aq7w28s04dua3ynd273jmrg8wxnegdzjecambjy5b7szy16oc8v85jb1b7a2ju	1	f
plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	lugar150610_1146151276595175hn5bspio8qjoodxmopwd7c70mcy62bydtdfr143o1j3zud8yymnibx6ydcx66hne3gml4uh3	1	f
plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	lugar220410_0924111271985851v3us9kjlt5aa6sbp68b9bfiol2meh7v9xfzieu74u36cs7636qvnfvkvk7w62q60	1	f
plantilla_de_actividad180610_0411261276870286ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwma	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	f
plantilla_de_actividad180610_0411261276870286ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwma	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	f
plantilla_de_actividad180610_0411261276870286ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwma	lugar150610_1146151276595175hn5bspio8qjoodxmopwd7c70mcy62bydtdfr143o1j3zud8yymnibx6ydcx66hne3gml4uh3	1	f
plantilla_de_actividad180610_0411261276870286ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwma	lugar220410_0924111271985851v3us9kjlt5aa6sbp68b9bfiol2meh7v9xfzieu74u36cs7636qvnfvkvk7w62q60	1	f
plantilla_de_actividad180610_0411261276870286ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwma	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	1	f
plantilla_de_actividad180610_0411261276870286ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwma	lugar150610_1147331276595253a0f0fksix52nqivzavjdmrr67aq7w28s04dua3ynd273jmrg8wxnegdzjecambjy5b7szy16oc8v85jb1b7a2ju	1	f
plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	f
plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	lugar150610_1146151276595175hn5bspio8qjoodxmopwd7c70mcy62bydtdfr143o1j3zud8yymnibx6ydcx66hne3gml4uh3	1	f
plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	lugar220410_0924111271985851v3us9kjlt5aa6sbp68b9bfiol2meh7v9xfzieu74u36cs7636qvnfvkvk7w62q60	1	f
plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	lugar210410_09243712718994788crry75ybs1gykuc4bm8hfs5bpcpbhq2qnxq9ifv3x62x0lc3bqd7g18ijfgrvf95bz4d9l6og7zsb9pwoqwrv5okqdl2mzsed	1	f
plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	lugar130410_0355341271188534bpyvy9rafxm5wkmagug32ogyrbazd87fmu1bshby5osqz6r7pxzhc76u86jb	1	f
plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	lugar150610_1147331276595253a0f0fksix52nqivzavjdmrr67aq7w28s04dua3ynd273jmrg8wxnegdzjecambjy5b7szy16oc8v85jb1b7a2ju	1	f
plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	lugar150610_1152041276595524twts0zqhuwv5k8x2v252ry7jn3abkz8qxr6nq2tegvfu2uqay4wl78o589apytgoy1dw4grox8vwdvewwjkihs34mr7h91k	1	f
plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	f
plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	f
plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	f
plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	lugar210410_09243712718994788crry75ybs1gykuc4bm8hfs5bpcpbhq2qnxq9ifv3x62x0lc3bqd7g18ijfgrvf95bz4d9l6og7zsb9pwoqwrv5okqdl2mzsed	1	f
plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	lugar130410_0355341271188534bpyvy9rafxm5wkmagug32ogyrbazd87fmu1bshby5osqz6r7pxzhc76u86jb	1	f
plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	1	f
plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	lugar220410_0924111271985851v3us9kjlt5aa6sbp68b9bfiol2meh7v9xfzieu74u36cs7636qvnfvkvk7w62q60	1	f
plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	lugar150610_1146151276595175hn5bspio8qjoodxmopwd7c70mcy62bydtdfr143o1j3zud8yymnibx6ydcx66hne3gml4uh3	1	f
plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	lugar150610_1147331276595253a0f0fksix52nqivzavjdmrr67aq7w28s04dua3ynd273jmrg8wxnegdzjecambjy5b7szy16oc8v85jb1b7a2ju	1	f
plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	lugar150610_1152041276595524twts0zqhuwv5k8x2v252ry7jn3abkz8qxr6nq2tegvfu2uqay4wl78o589apytgoy1dw4grox8vwdvewwjkihs34mr7h91k	1	f
plantilla_de_actividad200610_0200121276992012k03rn80azivwryv7pxp82aclhc0e9yf02gb05pvy72lhgagfmtwwxgwf5kx7dzype1g5gkd10y8g1zw3f3yyp1bom6gcddcj	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	1	f
plantilla_de_actividad200610_0200181276992018ygdotqf89osf3044pwddxm015vv95mqraczsfz82v2irjghmpq7w9t0ah6rzarbqz4zq	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	1	f
plantilla_de_actividad200610_0202161276992136wvjvanuozqag2br736ja7ehryuz4hot9dmdoowhc28hpjs9sqc31s4td9sua7lzs	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	f
plantilla_de_actividad200610_0202161276992136wvjvanuozqag2br736ja7ehryuz4hot9dmdoowhc28hpjs9sqc31s4td9sua7lzs	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	f
plantilla_de_actividad200610_0202161276992136wvjvanuozqag2br736ja7ehryuz4hot9dmdoowhc28hpjs9sqc31s4td9sua7lzs	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	f
plantilla_de_actividad200610_0202161276992136wvjvanuozqag2br736ja7ehryuz4hot9dmdoowhc28hpjs9sqc31s4td9sua7lzs	lugar210410_09243712718994788crry75ybs1gykuc4bm8hfs5bpcpbhq2qnxq9ifv3x62x0lc3bqd7g18ijfgrvf95bz4d9l6og7zsb9pwoqwrv5okqdl2mzsed	1	f
plantilla_de_actividad200610_0202161276992136wvjvanuozqag2br736ja7ehryuz4hot9dmdoowhc28hpjs9sqc31s4td9sua7lzs	lugar130410_0355341271188534bpyvy9rafxm5wkmagug32ogyrbazd87fmu1bshby5osqz6r7pxzhc76u86jb	1	f
plantilla_de_actividad200610_0202111276992131t6jkbs7dolr0fclro9ju2yyxlro1yd2k71wkvclg6ny9kncc0ilrsz3y00kw05h3xqmfstdfzlj33ths7n1	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	f
plantilla_de_actividad200610_0202111276992131t6jkbs7dolr0fclro9ju2yyxlro1yd2k71wkvclg6ny9kncc0ilrsz3y00kw05h3xqmfstdfzlj33ths7n1	lugar130410_0355341271188534bpyvy9rafxm5wkmagug32ogyrbazd87fmu1bshby5osqz6r7pxzhc76u86jb	1	f
plantilla_de_actividad200610_0202111276992131t6jkbs7dolr0fclro9ju2yyxlro1yd2k71wkvclg6ny9kncc0ilrsz3y00kw05h3xqmfstdfzlj33ths7n1	lugar210410_09243712718994788crry75ybs1gykuc4bm8hfs5bpcpbhq2qnxq9ifv3x62x0lc3bqd7g18ijfgrvf95bz4d9l6og7zsb9pwoqwrv5okqdl2mzsed	1	f
plantilla_de_actividad200610_0202111276992131t6jkbs7dolr0fclro9ju2yyxlro1yd2k71wkvclg6ny9kncc0ilrsz3y00kw05h3xqmfstdfzlj33ths7n1	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	f
plantilla_de_actividad200610_0202111276992131t6jkbs7dolr0fclro9ju2yyxlro1yd2k71wkvclg6ny9kncc0ilrsz3y00kw05h3xqmfstdfzlj33ths7n1	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	f
plantilla_de_actividad200610_0203311276992211m3pt962xna4kmueucmvbkz16hrrvtbllshix5myy2kv09nsyyqmmw5ucaphrvtxwta7ew	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	f
plantilla_de_actividad200610_0203311276992211m3pt962xna4kmueucmvbkz16hrrvtbllshix5myy2kv09nsyyqmmw5ucaphrvtxwta7ew	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	f
plantilla_de_actividad200610_0203311276992211m3pt962xna4kmueucmvbkz16hrrvtbllshix5myy2kv09nsyyqmmw5ucaphrvtxwta7ew	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	f
plantilla_de_actividad200610_0203311276992211m3pt962xna4kmueucmvbkz16hrrvtbllshix5myy2kv09nsyyqmmw5ucaphrvtxwta7ew	lugar210410_09243712718994788crry75ybs1gykuc4bm8hfs5bpcpbhq2qnxq9ifv3x62x0lc3bqd7g18ijfgrvf95bz4d9l6og7zsb9pwoqwrv5okqdl2mzsed	1	f
plantilla_de_actividad200610_0203311276992211m3pt962xna4kmueucmvbkz16hrrvtbllshix5myy2kv09nsyyqmmw5ucaphrvtxwta7ew	lugar130410_0355341271188534bpyvy9rafxm5wkmagug32ogyrbazd87fmu1bshby5osqz6r7pxzhc76u86jb	1	f
plantilla_de_actividad200610_0203401276992220qca8jcpu2gf89a4olyz231sp5lqhq0jpe5e92s3e7br3qb7k7wjrinky7ni1	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	f
plantilla_de_actividad200610_0203401276992220qca8jcpu2gf89a4olyz231sp5lqhq0jpe5e92s3e7br3qb7k7wjrinky7ni1	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	1	f
plantilla_de_actividad200610_0203401276992220qca8jcpu2gf89a4olyz231sp5lqhq0jpe5e92s3e7br3qb7k7wjrinky7ni1	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	1	f
plantilla_de_actividad200610_0203401276992220qca8jcpu2gf89a4olyz231sp5lqhq0jpe5e92s3e7br3qb7k7wjrinky7ni1	lugar210410_09243712718994788crry75ybs1gykuc4bm8hfs5bpcpbhq2qnxq9ifv3x62x0lc3bqd7g18ijfgrvf95bz4d9l6og7zsb9pwoqwrv5okqdl2mzsed	1	f
plantilla_de_actividad200610_0203401276992220qca8jcpu2gf89a4olyz231sp5lqhq0jpe5e92s3e7br3qb7k7wjrinky7ni1	lugar130410_0355341271188534bpyvy9rafxm5wkmagug32ogyrbazd87fmu1bshby5osqz6r7pxzhc76u86jb	1	f
plantilla_de_actividad200610_0203401276992220qca8jcpu2gf89a4olyz231sp5lqhq0jpe5e92s3e7br3qb7k7wjrinky7ni1	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	1	f
plantilla_de_actividad170410_0446071271537168lmle7f8bt1csvt516s4q1ef6aonzc1vg2fpanc9o4s2eo79l0i	lugar220410_0924111271985851v3us9kjlt5aa6sbp68b9bfiol2meh7v9xfzieu74u36cs7636qvnfvkvk7w62q60	1	f
plantilla_de_actividad170410_0446071271537168lmle7f8bt1csvt516s4q1ef6aonzc1vg2fpanc9o4s2eo79l0i	lugar150610_1146151276595175hn5bspio8qjoodxmopwd7c70mcy62bydtdfr143o1j3zud8yymnibx6ydcx66hne3gml4uh3	1	f
plantilla_de_actividad250710_0227501280060870baungw214ndyt07xry0vhf1b3x9766aysc2ll8slnxfvka0yeg6ss7gttnz23m4xmamf4dx2k4uon7q	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	1	f
plantilla_de_actividad250710_0227501280060870baungw214ndyt07xry0vhf1b3x9766aysc2ll8slnxfvka0yeg6ss7gttnz23m4xmamf4dx2k4uon7q	lugar130410_0355341271188534bpyvy9rafxm5wkmagug32ogyrbazd87fmu1bshby5osqz6r7pxzhc76u86jb	1	f
\.


--
-- TOC entry 2724 (class 0 OID 26566)
-- Dependencies: 1951
-- Data for Name: nivel; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY nivel (id, descripcion, creador) FROM stdin;
nivel100410_0834391270946079z0n34be8bnw1ayhz3gz40vrsygo2rv1brdoljozc8zric2el21pkwo2u298gzemuwd3zd	Segundo	admin-superuser
nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s	Primero	admin-superuser
nivel110410_07575112710302717jr5ci9dukfxsy6pres9v46fqslrdoz9ghnb1fexp8nlk4799rccrg1wrvidc9pp40wd4	Tercero	admin-superuser
nivel110410_0757581271030278ydxq95p4km2zad1n0a7tsuoor24xibiuvoj58j9ybw94l9mrvizng8ez99hhi6bd7uidjt6ph3t4a1mqum6chfh73c9wwuis	Cuarto	admin-superuser
nivel110410_0758101271030290b5jf9ytytymd7cskuun2zo3r6kd9gpgchtlnb5n2ldz2crgm8zd80bls4egutjtc2ezqs8v1qormw2ijx1g9xthpf6wdk0j8yus	Quinto	admin-superuser
\.


--
-- TOC entry 2725 (class 0 OID 26573)
-- Dependencies: 1952
-- Data for Name: otro_grupo_de_recursos_para_actividad; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY otro_grupo_de_recursos_para_actividad (id, plantilla, prioridad, necesarios, imprescindibles, creador) FROM stdin;
otro_grupo_de_recursos_para_actividad210610_1257451277117865fe195bshx98crry75ybs1gykuc4bm8hfs5bpcpbhq2qnxq9ifv3x62x0lc3bqd7g18ijfgrvf95	plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	1	1	t	admin-superuser
otro_grupo_de_recursos_para_actividad250710_02282312800609037gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fnqmicq2mwfl18tvelczyx1hgi0h48	plantilla_de_actividad250710_0227501280060870baungw214ndyt07xry0vhf1b3x9766aysc2ll8slnxfvka0yeg6ss7gttnz23m4xmamf4dx2k4uon7q	1	1	t	admin-superuser
otro_grupo_de_recursos_para_actividad270710_06520112802495216eec8vtertp85n6x7crc0emn8rckcw1ajs9k6w3mz756f5hd0or	plantilla_de_actividad180610_0411261276870286ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwma	1	1	t	admin-superuser
\.


--
-- TOC entry 2726 (class 0 OID 26583)
-- Dependencies: 1953
-- Data for Name: otro_recurso; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY otro_recurso (id, nombre, creador) FROM stdin;
otro_recurso210610_0101151277118075cnaihzspb77h52gelx3lzozmynolqo5543s07qnmsy6b3maxs3ij55squ5lm5xwtpw7dsuex451j	pc 1	admin-superuser
\.


--
-- TOC entry 2727 (class 0 OID 26590)
-- Dependencies: 1954
-- Data for Name: otro_recurso_para_actividad; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY otro_recurso_para_actividad (grupo_de_recursos, prioridad, otro_recurso) FROM stdin;
otro_grupo_de_recursos_para_actividad250710_02282312800609037gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fnqmicq2mwfl18tvelczyx1hgi0h48	1	otro_recurso210610_0101151277118075cnaihzspb77h52gelx3lzozmynolqo5543s07qnmsy6b3maxs3ij55squ5lm5xwtpw7dsuex451j
otro_grupo_de_recursos_para_actividad270710_06520112802495216eec8vtertp85n6x7crc0emn8rckcw1ajs9k6w3mz756f5hd0or	1	otro_recurso210610_0101151277118075cnaihzspb77h52gelx3lzozmynolqo5543s07qnmsy6b3maxs3ij55squ5lm5xwtpw7dsuex451j
\.


--
-- TOC entry 2728 (class 0 OID 26597)
-- Dependencies: 1955
-- Data for Name: otros_recursos_usados; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY otros_recursos_usados (otro_recurso, actividad, creador) FROM stdin;
\.


--
-- TOC entry 2729 (class 0 OID 26603)
-- Dependencies: 1956
-- Data for Name: periodicidad; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY periodicidad (id, periodo, dia) FROM stdin;
periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1
periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	7
periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1
periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	7
periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	14
periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	27
periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	47
periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	63
periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	90
periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	103
\.


--
-- TOC entry 2710 (class 0 OID 26426)
-- Dependencies: 1936
-- Data for Name: periodo; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY periodo (id, descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion) FROM stdin;
periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	Semana Par	5	6	480	90	planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j
periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	Semana Impar	5	6	480	90	planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j
periodo210610_1245401277117140i650vcl3fwgxglpgnusq9focxqufyl0j1cerbu8qnf7i6xapkpze6l98kyi	Semana x	5	6	5	90	planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j
periodo210610_03350612771273062icuozbloj1n5f9xmddy9k9nkr8hq63gaf92dvi6j79bwk0g3qx34w45mu44opsxx9h4fn2krptu3knu	ggg	5	6	5	90	planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j
periodo250710_02251112800607111dp15arl4x9kuxs0wdoh7vmm8hthwt1gqzebpjoham5resfhk6tipbz7bc6cdv4ccxy277hqcowat1y34b	hhhh	5	6	5	90	planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j
\.


--
-- TOC entry 2730 (class 0 OID 26611)
-- Dependencies: 1957
-- Data for Name: planificacion; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY planificacion (id, descripcion, fecha_inicial, cant_dias, activa, creador) FROM stdin;
planificacion100410_1214311270916071ef0qwrni8kcloce752g1w6kavncqczvk85qirj3rv8f4vlwnmn58fw6z89ln97j	Primer Semestre	3897	116	t	admin-superuser
planificacion250710_0225381280060738mourb2inkcwf1bajo7jy9nz6pyi4psf0cekm9ibmeah0iiezmcyrbjg3qcsvee9nh1oditghm4	hhh	3835	30	f	admin-superuser
\.


--
-- TOC entry 2731 (class 0 OID 26619)
-- Dependencies: 1958
-- Data for Name: plantilla_de_actividad; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY plantilla_de_actividad (id, desglose, orden, clasificacion, exige_profesor, exige_lugar, actualizacion_interna) FROM stdin;
plantilla_de_actividad260610_1108291277586509pbhjg0njybt83ek9512t16sp19x5201rdo8uwh24y5mqckkhhbxk6adppzw41d7gewa1qksibts62mwm8h8	ancoragoh-2010-06-21 15:35:06.74+02-ps1cvguw8kxniypuzzi1-desglose	2	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	t	t	f
plantilla_de_actividad260610_11081612775864968kkeqstduod2d7u5osbulfexhm7k7a9j1yozuj4pzjlubwq674nve5wocelttyb1887gb6rwj07jnhz43a	ancoragoh-2010-06-21 12:45:40.549+02-4np8py88ylqktd595lyz-desglose	1	clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	t	t	f
plantilla_de_actividad250710_0227501280060870baungw214ndyt07xry0vhf1b3x9766aysc2ll8slnxfvka0yeg6ss7gttnz23m4xmamf4dx2k4uon7q	ancoragoh-2010-07-25 14:27:34.02+02-kv3ty7teizs6dk5givqv-desglose	1	clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	t	t	f
plantilla_de_actividad070610_0335031275939303nh2edym9zzglqii3xikl5q64daqms6k7dcag1neqck2stalgiwsddn7goms7h44k65rwhwdk764q6fwd2eg5sca7pt4xmx9ir	ancoragoh-2010-04-14 16:33:51.871089-04-ljx1pd6mpgw13nwjxr86-desglose	1	clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	t	t	f
plantilla_de_actividad170410_0446071271537168lmle7f8bt1csvt516s4q1ef6aonzc1vg2fpanc9o4s2eo79l0i	ancoragoh-2010-04-14 16:33:51.871089-04-lw38rj5yihkvl0tyg9jf-desglose	3	clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	t	t	f
plantilla_de_actividad260610_1108231277586503rkoqbk3dyuhrdrc2t7ntzfu64dxmvbddk54zu6liy9vllpca1f19	ancoragoh-2010-06-21 15:35:06.74+02-ps1cvguw8kxniypuzzi1-desglose	1	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	t	t	f
plantilla_de_actividad260610_1108371277586517nbbksmaxddq2t1q4xjxt3ecf3knnaivpk8ma852n59nhlzl1idfz8uakidqjrfw3tzcw2i699yxtrusuh3b2p6lmb29am8ab	ancoragoh-2010-06-21 12:45:40.549+02-4np8py88ylqktd595lyz-desglose	2	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	t	t	f
plantilla_de_actividad250710_02275612800608762msejtjf9j58bnibig7w2t4il3q9f33p96jz965o7fbv9t376ouo6umyymv4rxjqb25ysulbyodx1dheo0dt691r	ancoragoh-2010-07-25 14:27:34.02+02-kv3ty7teizs6dk5givqv-desglose	2	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	t	t	f
plantilla_de_actividad180610_0411261276870286ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwma	ancoragoh-2010-06-15 23:06:33.666+02-25agmd3g6j5lpczws7c9-desglose	1	clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	t	t	f
plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	ancoragoh-2010-06-15 23:06:33.666+02-25agmd3g6j5lpczws7c9-desglose	2	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	t	t	f
plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	ancoragoh-2010-06-15 23:06:33.666+02-giwh3fsw5psnbn2dmy1x-desglose	1	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	t	t	f
plantilla_de_actividad200610_0200121276992012k03rn80azivwryv7pxp82aclhc0e9yf02gb05pvy72lhgagfmtwwxgwf5kx7dzype1g5gkd10y8g1zw3f3yyp1bom6gcddcj	ancoragoh-2010-06-20 01:59:56.868+02-zqq6izmu24fgf1d6nkn-desglose	1	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	t	t	f
plantilla_de_actividad200610_0200181276992018ygdotqf89osf3044pwddxm015vv95mqraczsfz82v2irjghmpq7w9t0ah6rzarbqz4zq	ancoragoh-2010-06-20 01:59:56.868+02-9ddt98n9zwwytet1e6o3-desglose	1	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	t	t	f
plantilla_de_actividad200610_0202111276992131t6jkbs7dolr0fclro9ju2yyxlro1yd2k71wkvclg6ny9kncc0ilrsz3y00kw05h3xqmfstdfzlj33ths7n1	ancoragoh-2010-06-20 01:59:33.319+02-urmium8lbhscb6u5l34g-desglose	1	clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	t	t	f
plantilla_de_actividad200610_0202161276992136wvjvanuozqag2br736ja7ehryuz4hot9dmdoowhc28hpjs9sqc31s4td9sua7lzs	ancoragoh-2010-06-20 01:59:33.319+02-ma06g9wju9w9ynjg1ko-desglose	1	clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	t	t	f
plantilla_de_actividad200610_0203311276992211m3pt962xna4kmueucmvbkz16hrrvtbllshix5myy2kv09nsyyqmmw5ucaphrvtxwta7ew	ancoragoh-2010-06-20 01:59:44.486+02-nfyl3vcrggh3xxocexuo-desglose	1	clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	t	t	f
plantilla_de_actividad200610_0203401276992220qca8jcpu2gf89a4olyz231sp5lqhq0jpe5e92s3e7br3qb7k7wjrinky7ni1	ancoragoh-2010-06-20 01:59:44.486+02-r3o29hnwzzo650p46f-desglose	1	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	t	t	f
plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	ancoragoh-2010-04-14 16:33:51.871089-04-lw38rj5yihkvl0tyg9jf-desglose	1	clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	t	t	f
plantilla_de_actividad210610_1258351277117915z4d9l6og7zsb9pwoqwrv5okqdl2mzsedxtdcms7c3birqgmflt8n	ancoragoh-2010-04-14 16:33:51.871089-04-lw38rj5yihkvl0tyg9jf-desglose	2	clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	t	t	f
\.


--
-- TOC entry 2732 (class 0 OID 26629)
-- Dependencies: 1959
-- Data for Name: profesor; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY profesor (id, nombre, creador) FROM stdin;
profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq	Carlos	admin-superuser
profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	Tito	admin-superuser
profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5	Katrina	admin-superuser
profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb	pepito	admin-superuser
profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r	Sochi	admin-superuser
profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn	Angel Rodriguez	admin-superuser
profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw	Luis Gonzalez	admin-superuser
profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	Rafael Rodriguez	admin-superuser
profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	Omar Linares	admin-superuser
\.


--
-- TOC entry 2733 (class 0 OID 26636)
-- Dependencies: 1960
-- Data for Name: profesor_para_actividad; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY profesor_para_actividad (id, plantilla, profesor, prioridad) FROM stdin;
profesor_para_actividad190610_0527441276961264r45xsdgsh8k6hcuhv15r9wpgn9hhqqrekmf4rms3eyqb3cj0vz4velqoqfblh664r	plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb	1
profesor_para_actividad190610_0526431276961203z5ec1wnmvpogaxwwgebl4gdlgx3i97ufm2pxev36qhxnd0sjj1jdx5qardawr7o96cvxo8c2hm49dyjii1ojm6	plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb	1
profesor_para_actividad180610_0413041276870384eh0mq2ma5diiwx2hxnsyj1u5l2tpe5sxtadi8ynoe685qa642d3apv3xjqprwf4ymyva3r3x2sa	plantilla_de_actividad180610_0411261276870286ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwma	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq	1
profesor_para_actividad190610_0527431276961263ey8dn7kdek1x0txib5657917wcdn48k3u1fbshbxyu3u1t2kzm21hygon34i1nc2py16anfdw1hn4pih1zbs	plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r	1
profesor_para_actividad180610_0413201276870400k4dfyz9uk5kttkm9pw3fo079p8mz0vnjbsz0c5tybewzorf8fs	plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5	1
profesor_para_actividad190610_0526431276961203xzk81dpet30w7zvhglmafx34iec5i122t7058xj22p228hbu1qhnaf57f1bo9gou30bwqdsjn082lw63yqqujsj5phn5b	plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	1
profesor_para_actividad180610_0414101276870450h0f6lmwcgtb40ok2qk784b403at9va0q8pxwbcr0wlay80ejwom60qwcy	plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5	1
profesor_para_actividad190610_05274312769612633pf5or596f15kwzrixbm4qwnw14c2zoo3d335f2jrtzj4sq2p2niq	plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw	1
profesor_para_actividad190610_0526411276961201xknkj1qh9lnmvbyosmsvsipxaet23bqyhoiwehsk68m7lslh2puncxyfvyz2gpk8fp	plantilla_de_actividad180610_04113112768702912jdbtis3hbjnsc28vfbajwszylgxvwr7gmpbh5qi69u07bgqr7khn8ol29opok5vr4xp25kboj19fn	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	1
profesor_para_actividad180610_0411491276870309vwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp1xyuo69bqgxz6et6e54oh7	plantilla_de_actividad170410_0446071271537168lmle7f8bt1csvt516s4q1ef6aonzc1vg2fpanc9o4s2eo79l0i	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn	1
profesor_para_actividad190610_0527441276961264q09k2ftib1wgij9r56185fy7ygajluqlinh8oiapkidy58fauqpjpov3667p6odt7fm98yn0pd	plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	1
profesor_para_actividad180610_0412071276870327ocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9	plantilla_de_actividad070610_0335031275939303nh2edym9zzglqii3xikl5q64daqms6k7dcag1neqck2stalgiwsddn7goms7h44k65rwhwdk764q6fwd2eg5sca7pt4xmx9ir	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn	1
profesor_para_actividad200610_0202241276992144jwpuqpra6vgx3wxycqq3cuzjw7a2b0psi1vjsxuhzy8cfl4c1s483z06e89wew4nv1e572aj7xcn86	plantilla_de_actividad200610_0202111276992131t6jkbs7dolr0fclro9ju2yyxlro1yd2k71wkvclg6ny9kncc0ilrsz3y00kw05h3xqmfstdfzlj33ths7n1	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	1
profesor_para_actividad190610_0527441276961264pio8qjoodxmopwd7c70mcy62bydtdfr143o1j3zud8yymnibx6ydcx66hne3gml4uh3pqhnwpco5	plantilla_de_actividad180610_0411351276870295micq2mwfl18tvelczyx1hgi0h483lkdww5urzhy3zvksfy1gf15ymr0t2d0zblhykmqnb9ekpl	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	1
profesor_para_actividad250710_0228081280060888kvcz6w60cla9xxbmbbdoas139sc2zayawrz3s6y4zy1je5p7b678lzzbl49s6fjakiy2r05s274q98gloucl02cgb9iengh6	plantilla_de_actividad250710_0227501280060870baungw214ndyt07xry0vhf1b3x9766aysc2ll8slnxfvka0yeg6ss7gttnz23m4xmamf4dx2k4uon7q	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn	1
profesor_para_actividad200610_0204361276992276taadz3y2o5wvbkdpwz3vwg880mj3hi9ahr38sg8pb8e3l3u8jn0em2wjuvgx	plantilla_de_actividad200610_0203401276992220qca8jcpu2gf89a4olyz231sp5lqhq0jpe5e92s3e7br3qb7k7wjrinky7ni1	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	1
profesor_para_actividad250710_0228101280060890w4685cldlqa2ntad5i76da4k95mc2iuoodcbg88h8z3va7zpkpkpd5x4jbllesqftiwb5z8g849kc	plantilla_de_actividad250710_0227501280060870baungw214ndyt07xry0vhf1b3x9766aysc2ll8slnxfvka0yeg6ss7gttnz23m4xmamf4dx2k4uon7q	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq	1
profesor_para_actividad200610_0202411276992161zu618qq7pnc9wp92sg5yt37rz37uqla328aqd1cppl8bi34er1ocwj9	plantilla_de_actividad200610_0202161276992136wvjvanuozqag2br736ja7ehryuz4hot9dmdoowhc28hpjs9sqc31s4td9sua7lzs	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	1
profesor_para_actividad200610_02003412769920349t6xot2zrtaarpugcs6jsx97rgigh91ypl5rldvhyyq9x5nj903k681vcawk3cq91rzzfcldzg2jyxb214vzeuny	plantilla_de_actividad200610_0200121276992012k03rn80azivwryv7pxp82aclhc0e9yf02gb05pvy72lhgagfmtwwxgwf5kx7dzype1g5gkd10y8g1zw3f3yyp1bom6gcddcj	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	1
profesor_para_actividad200610_09194212770615822byrj6x355plm8w1ewlka6e1nfdiap7slgt5eny61qo6bojtnzndulmi9z	plantilla_de_actividad200610_0203401276992220qca8jcpu2gf89a4olyz231sp5lqhq0jpe5e92s3e7br3qb7k7wjrinky7ni1	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb	1
profesor_para_actividad200610_0200551276992055g63jucw9apk4o7mwb98u4vvnbirju7e82ayvpuya8bjodn0pkb0mna57x3wj0k9fza74x8d0hsv4gz	plantilla_de_actividad200610_0200181276992018ygdotqf89osf3044pwddxm015vv95mqraczsfz82v2irjghmpq7w9t0ah6rzarbqz4zq	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	1
profesor_para_actividad200610_0922281277061748ux7cdnhwbj65gyo3zr1em9m6kagyk92vtxai3opzid9vvcd0ef3rrysf7thwfao9oaa3n4tgkl8174yt	plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn	1
profesor_para_actividad200610_092229127706174991006qd4pi3a8th4jm66ze9rf50k89du7p6an2o5i7k33lvc709n7nq8xqxsryyjiae8j9d2oqepa0ng7so1qslk	plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq	1
profesor_para_actividad200610_02040612769922464dqx0lxdj934fdbwulf8b2mss0l2qclr4xbbd31kidcuzxlyscfado5o	plantilla_de_actividad200610_0203311276992211m3pt962xna4kmueucmvbkz16hrrvtbllshix5myy2kv09nsyyqmmw5ucaphrvtxwta7ew	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw	1
profesor_para_actividad200610_09223012770617500az4750nr6324j80s24qxvig4qtwevv2f11b0b4qikfqaxwa67tw8h1h2lwjrq92ykffqdapnu7bu9x9v8w	plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw	1
profesor_para_actividad200610_02040712769922476pktb8hb9pizyegr31o6ps13ges2d3w14kmfp3vbrax3egoq8bfp03317ew42kswfa3yfsl7oqp30e9kqhk688	plantilla_de_actividad200610_0203311276992211m3pt962xna4kmueucmvbkz16hrrvtbllshix5myy2kv09nsyyqmmw5ucaphrvtxwta7ew	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r	1
profesor_para_actividad250710_0228111280060891yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0qi2w2wgbysqqd4iwmat2jdbtis3hbjnsc28vfbajwszylgxvw	plantilla_de_actividad250710_0227501280060870baungw214ndyt07xry0vhf1b3x9766aysc2ll8slnxfvka0yeg6ss7gttnz23m4xmamf4dx2k4uon7q	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5	1
profesor_para_actividad190610_1157111276984631tm0i8gp096bob88y0kxveplmmlsw8pmx3zoghx2obiccy6mm7vi0d55hdvz1jl92o9ga5ar1	plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	2
profesor_para_actividad200610_0922301277061750ip5z2jg3bm1d4b81ke1jhax41rw9w8e3dgd76ucof997xyjpwv9x1aqwj3387ko	plantilla_de_actividad160610_06580312767074836bzh313gsitvdz2qg95i4eu6w0m6yz9gvc3iqizc590d5psmdowhxcfzefqlw6	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5	3
profesor_para_actividad210610_1254131277117653otkih8rv530ivw6nno12quxv1eiwtsuay1jsuhom08uhkn6mms7rr313uh0b7h2on2wxkny9r9	plantilla_de_actividad170410_0446071271537168lmle7f8bt1csvt516s4q1ef6aonzc1vg2fpanc9o4s2eo79l0i	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5	1
profesor_para_actividad210610_12541412771176545wocelttyb1887gb6rwj07jnhz43abrkoqbk3dyuhrdrc2t7ntzfu64d	plantilla_de_actividad170410_0446071271537168lmle7f8bt1csvt516s4q1ef6aonzc1vg2fpanc9o4s2eo79l0i	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	1
profesor_para_actividad210610_1254141277117654lkov7iyjf68bumn1di1ziwi37dvlw3bfmijxsvswforqx4p8cmbq3gs454woxptbiuadbminal9iwxns76	plantilla_de_actividad170410_0446071271537168lmle7f8bt1csvt516s4q1ef6aonzc1vg2fpanc9o4s2eo79l0i	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw	1
\.


--
-- TOC entry 2734 (class 0 OID 26644)
-- Dependencies: 1961
-- Data for Name: profesor_para_grupo; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY profesor_para_grupo (profesor_para_actividad, grupo) FROM stdin;
profesor_para_actividad180610_0413041276870384eh0mq2ma5diiwx2hxnsyj1u5l2tpe5sxtadi8ynoe685qa642d3apv3xjqprwf4ymyva3r3x2sa	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6
profesor_para_actividad180610_0413041276870384eh0mq2ma5diiwx2hxnsyj1u5l2tpe5sxtadi8ynoe685qa642d3apv3xjqprwf4ymyva3r3x2sa	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j
profesor_para_actividad180610_0414101276870450h0f6lmwcgtb40ok2qk784b403at9va0q8pxwbcr0wlay80ejwom60qwcy	grupo190610_05254312769611437npmmif4uzmf4y904srn1068msv147xo8rr05ebelf57tp47jkqhyuzpsxbx0lqr49oain4489iaftugvqk
profesor_para_actividad180610_0414101276870450h0f6lmwcgtb40ok2qk784b403at9va0q8pxwbcr0wlay80ejwom60qwcy	grupo190610_0525371276961137rzo3dgswxcnsgw6rlsl0f0u0k1993am5jtcf4r94awnm0ffyl2xjc1ze51xz30l9pidn7y373b2bgt51
profesor_para_actividad180610_0414101276870450h0f6lmwcgtb40ok2qk784b403at9va0q8pxwbcr0wlay80ejwom60qwcy	grupo190610_0525301276961130nz0n34be8bnw1ayhz3gz40vrsygo2rv1brdoljozc8zric2el21pkwo2u298gzemuwd3zdwxcg81
profesor_para_actividad180610_0414101276870450h0f6lmwcgtb40ok2qk784b403at9va0q8pxwbcr0wlay80ejwom60qwcy	grupo180610_0355511276869351d20t2ryrtwlz8jcb60y548n83w5xiim7dlu9692zs2vqr4md3zy86u64y9ud10cw9ia153ykmj2v8
profesor_para_actividad180610_0414101276870450h0f6lmwcgtb40ok2qk784b403at9va0q8pxwbcr0wlay80ejwom60qwcy	grupo190610_0525481276961148rtolufn9aee0u3gpqbq7ofrsvbg530aag60uraux8ky4ha5hvb0m21wn97y767w01uqhjjw
profesor_para_actividad190610_0526431276961203z5ec1wnmvpogaxwwgebl4gdlgx3i97ufm2pxev36qhxnd0sjj1jdx5qardawr7o96cvxo8c2hm49dyjii1ojm6	grupo190610_0525371276961137rzo3dgswxcnsgw6rlsl0f0u0k1993am5jtcf4r94awnm0ffyl2xjc1ze51xz30l9pidn7y373b2bgt51
profesor_para_actividad190610_0526431276961203xzk81dpet30w7zvhglmafx34iec5i122t7058xj22p228hbu1qhnaf57f1bo9gou30bwqdsjn082lw63yqqujsj5phn5b	grupo190610_05254312769611437npmmif4uzmf4y904srn1068msv147xo8rr05ebelf57tp47jkqhyuzpsxbx0lqr49oain4489iaftugvqk
profesor_para_actividad190610_0526411276961201xknkj1qh9lnmvbyosmsvsipxaet23bqyhoiwehsk68m7lslh2puncxyfvyz2gpk8fp	grupo190610_0525481276961148rtolufn9aee0u3gpqbq7ofrsvbg530aag60uraux8ky4ha5hvb0m21wn97y767w01uqhjjw
profesor_para_actividad180610_0413201276870400k4dfyz9uk5kttkm9pw3fo079p8mz0vnjbsz0c5tybewzorf8fs	grupo180610_0355511276869351d20t2ryrtwlz8jcb60y548n83w5xiim7dlu9692zs2vqr4md3zy86u64y9ud10cw9ia153ykmj2v8
profesor_para_actividad190610_05274312769612633pf5or596f15kwzrixbm4qwnw14c2zoo3d335f2jrtzj4sq2p2niq	grupo190610_0525301276961130nz0n34be8bnw1ayhz3gz40vrsygo2rv1brdoljozc8zric2el21pkwo2u298gzemuwd3zdwxcg81
profesor_para_actividad190610_0527431276961263ey8dn7kdek1x0txib5657917wcdn48k3u1fbshbxyu3u1t2kzm21hygon34i1nc2py16anfdw1hn4pih1zbs	grupo190610_0525371276961137rzo3dgswxcnsgw6rlsl0f0u0k1993am5jtcf4r94awnm0ffyl2xjc1ze51xz30l9pidn7y373b2bgt51
profesor_para_actividad190610_0527441276961264q09k2ftib1wgij9r56185fy7ygajluqlinh8oiapkidy58fauqpjpov3667p6odt7fm98yn0pd	grupo190610_05254312769611437npmmif4uzmf4y904srn1068msv147xo8rr05ebelf57tp47jkqhyuzpsxbx0lqr49oain4489iaftugvqk
profesor_para_actividad190610_0527441276961264r45xsdgsh8k6hcuhv15r9wpgn9hhqqrekmf4rms3eyqb3cj0vz4velqoqfblh664r	grupo190610_05254312769611437npmmif4uzmf4y904srn1068msv147xo8rr05ebelf57tp47jkqhyuzpsxbx0lqr49oain4489iaftugvqk
profesor_para_actividad190610_0527441276961264pio8qjoodxmopwd7c70mcy62bydtdfr143o1j3zud8yymnibx6ydcx66hne3gml4uh3pqhnwpco5	grupo190610_0525481276961148rtolufn9aee0u3gpqbq7ofrsvbg530aag60uraux8ky4ha5hvb0m21wn97y767w01uqhjjw
profesor_para_actividad180610_0411491276870309vwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp1xyuo69bqgxz6et6e54oh7	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j
profesor_para_actividad180610_0411491276870309vwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp1xyuo69bqgxz6et6e54oh7	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6
profesor_para_actividad180610_0412071276870327ocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j
profesor_para_actividad180610_0412071276870327ocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6
profesor_para_actividad190610_1157111276984631tm0i8gp096bob88y0kxveplmmlsw8pmx3zoghx2obiccy6mm7vi0d55hdvz1jl92o9ga5ar1	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j
profesor_para_actividad190610_1157111276984631tm0i8gp096bob88y0kxveplmmlsw8pmx3zoghx2obiccy6mm7vi0d55hdvz1jl92o9ga5ar1	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6
profesor_para_actividad200610_02003412769920349t6xot2zrtaarpugcs6jsx97rgigh91ypl5rldvhyyq9x5nj903k681vcawk3cq91rzzfcldzg2jyxb214vzeuny	grupo190610_05254312769611437npmmif4uzmf4y904srn1068msv147xo8rr05ebelf57tp47jkqhyuzpsxbx0lqr49oain4489iaftugvqk
profesor_para_actividad200610_02003412769920349t6xot2zrtaarpugcs6jsx97rgigh91ypl5rldvhyyq9x5nj903k681vcawk3cq91rzzfcldzg2jyxb214vzeuny	grupo190610_0525371276961137rzo3dgswxcnsgw6rlsl0f0u0k1993am5jtcf4r94awnm0ffyl2xjc1ze51xz30l9pidn7y373b2bgt51
profesor_para_actividad200610_02003412769920349t6xot2zrtaarpugcs6jsx97rgigh91ypl5rldvhyyq9x5nj903k681vcawk3cq91rzzfcldzg2jyxb214vzeuny	grupo190610_0525301276961130nz0n34be8bnw1ayhz3gz40vrsygo2rv1brdoljozc8zric2el21pkwo2u298gzemuwd3zdwxcg81
profesor_para_actividad200610_02003412769920349t6xot2zrtaarpugcs6jsx97rgigh91ypl5rldvhyyq9x5nj903k681vcawk3cq91rzzfcldzg2jyxb214vzeuny	grupo180610_0355511276869351d20t2ryrtwlz8jcb60y548n83w5xiim7dlu9692zs2vqr4md3zy86u64y9ud10cw9ia153ykmj2v8
profesor_para_actividad200610_02003412769920349t6xot2zrtaarpugcs6jsx97rgigh91ypl5rldvhyyq9x5nj903k681vcawk3cq91rzzfcldzg2jyxb214vzeuny	grupo190610_0525481276961148rtolufn9aee0u3gpqbq7ofrsvbg530aag60uraux8ky4ha5hvb0m21wn97y767w01uqhjjw
profesor_para_actividad200610_0200551276992055g63jucw9apk4o7mwb98u4vvnbirju7e82ayvpuya8bjodn0pkb0mna57x3wj0k9fza74x8d0hsv4gz	grupo190610_05254312769611437npmmif4uzmf4y904srn1068msv147xo8rr05ebelf57tp47jkqhyuzpsxbx0lqr49oain4489iaftugvqk
profesor_para_actividad200610_0200551276992055g63jucw9apk4o7mwb98u4vvnbirju7e82ayvpuya8bjodn0pkb0mna57x3wj0k9fza74x8d0hsv4gz	grupo190610_0525371276961137rzo3dgswxcnsgw6rlsl0f0u0k1993am5jtcf4r94awnm0ffyl2xjc1ze51xz30l9pidn7y373b2bgt51
profesor_para_actividad200610_0200551276992055g63jucw9apk4o7mwb98u4vvnbirju7e82ayvpuya8bjodn0pkb0mna57x3wj0k9fza74x8d0hsv4gz	grupo190610_0525301276961130nz0n34be8bnw1ayhz3gz40vrsygo2rv1brdoljozc8zric2el21pkwo2u298gzemuwd3zdwxcg81
profesor_para_actividad200610_0200551276992055g63jucw9apk4o7mwb98u4vvnbirju7e82ayvpuya8bjodn0pkb0mna57x3wj0k9fza74x8d0hsv4gz	grupo180610_0355511276869351d20t2ryrtwlz8jcb60y548n83w5xiim7dlu9692zs2vqr4md3zy86u64y9ud10cw9ia153ykmj2v8
profesor_para_actividad200610_0200551276992055g63jucw9apk4o7mwb98u4vvnbirju7e82ayvpuya8bjodn0pkb0mna57x3wj0k9fza74x8d0hsv4gz	grupo190610_0525481276961148rtolufn9aee0u3gpqbq7ofrsvbg530aag60uraux8ky4ha5hvb0m21wn97y767w01uqhjjw
profesor_para_actividad200610_0202241276992144jwpuqpra6vgx3wxycqq3cuzjw7a2b0psi1vjsxuhzy8cfl4c1s483z06e89wew4nv1e572aj7xcn86	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j
profesor_para_actividad200610_0202241276992144jwpuqpra6vgx3wxycqq3cuzjw7a2b0psi1vjsxuhzy8cfl4c1s483z06e89wew4nv1e572aj7xcn86	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6
profesor_para_actividad200610_0202411276992161zu618qq7pnc9wp92sg5yt37rz37uqla328aqd1cppl8bi34er1ocwj9	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j
profesor_para_actividad200610_0202411276992161zu618qq7pnc9wp92sg5yt37rz37uqla328aqd1cppl8bi34er1ocwj9	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6
profesor_para_actividad200610_02040612769922464dqx0lxdj934fdbwulf8b2mss0l2qclr4xbbd31kidcuzxlyscfado5o	grupo180410_0835461271637346x5fci174cifzalsppocifrp5sbxrnbm4c8wepzbe4410cijqtp11bn1v9tohd7j
profesor_para_actividad200610_02040712769922476pktb8hb9pizyegr31o6ps13ges2d3w14kmfp3vbrax3egoq8bfp03317ew42kswfa3yfsl7oqp30e9kqhk688	grupo190410_095128127168508835ilx6n4efw7ztf7mkhpxqllub6m4ehv0q8wlkppqcmfvrc82kno2y1m1wyt27f1mcmym3e35q9q9bozl2edq6pgrd1ia6
profesor_para_actividad200610_0204361276992276taadz3y2o5wvbkdpwz3vwg880mj3hi9ahr38sg8pb8e3l3u8jn0em2wjuvgx	grupo190610_05254312769611437npmmif4uzmf4y904srn1068msv147xo8rr05ebelf57tp47jkqhyuzpsxbx0lqr49oain4489iaftugvqk
profesor_para_actividad200610_0204361276992276taadz3y2o5wvbkdpwz3vwg880mj3hi9ahr38sg8pb8e3l3u8jn0em2wjuvgx	grupo190610_0525371276961137rzo3dgswxcnsgw6rlsl0f0u0k1993am5jtcf4r94awnm0ffyl2xjc1ze51xz30l9pidn7y373b2bgt51
profesor_para_actividad200610_0204361276992276taadz3y2o5wvbkdpwz3vwg880mj3hi9ahr38sg8pb8e3l3u8jn0em2wjuvgx	grupo190610_0525301276961130nz0n34be8bnw1ayhz3gz40vrsygo2rv1brdoljozc8zric2el21pkwo2u298gzemuwd3zdwxcg81
profesor_para_actividad200610_0204361276992276taadz3y2o5wvbkdpwz3vwg880mj3hi9ahr38sg8pb8e3l3u8jn0em2wjuvgx	grupo180610_0355511276869351d20t2ryrtwlz8jcb60y548n83w5xiim7dlu9692zs2vqr4md3zy86u64y9ud10cw9ia153ykmj2v8
profesor_para_actividad200610_0204361276992276taadz3y2o5wvbkdpwz3vwg880mj3hi9ahr38sg8pb8e3l3u8jn0em2wjuvgx	grupo190610_0525481276961148rtolufn9aee0u3gpqbq7ofrsvbg530aag60uraux8ky4ha5hvb0m21wn97y767w01uqhjjw
\.


--
-- TOC entry 2735 (class 0 OID 26650)
-- Dependencies: 1962
-- Data for Name: receso; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY receso (periodo, despues_del_turno, duracion) FROM stdin;
\.


--
-- TOC entry 2709 (class 0 OID 26387)
-- Dependencies: 1935
-- Data for Name: restriccion_condicional; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_condicional (id, planificacion, activa, clasif_act, asig, brigada, profesor, lugar, otro_recurso, especialidad, nivel, fecha_ini, fecha_fin, hora_ini, hora_fin, discreto_exacto, dia_ini_planif, dia_fin_planif, hora_ini_planif, hora_fin_planif, discreto_exacto_planif, periodo, dia_ini, dia_fin, turno_ini, turno_fin, discreto, hoy, ahora, dia_relativo_anterior, cant_dias_relativo_anterior, dia_relativo_posterior, cant_dias_relativo_posterior, rango_tiempo_relativo_anterior_ini, rango_tiempo_relativo_anterior_tamano, turno_relativo_anterior, cant_turnos_relativo_anterior, turno_relativo_posterior, cant_turnos_relativo_posterior, rango_tiempo_relativo_posterior_ini, rango_tiempo_relativo_posterior_tamano, entre_dias_antes, entre_dias_despues, entre_turnos_antes, entre_turnos_despues, entre_tiempo_antes, entre_tiempo_despues, cant_exa, cant_min, cant_max, consec_brigada, consec_profesor, consec_lugar, consec_otro_recurso, consec_periodo, consec_dia_ini, consec_dia_fin, consec_turno_ini, consec_turno_fin, consec_discreto, siempre, valor) FROM stdin;
\.


--
-- TOC entry 2736 (class 0 OID 26656)
-- Dependencies: 1963
-- Data for Name: restriccion_de_asignatura; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_de_asignatura (id, asignatura, periodo, dia, turno, valor) FROM stdin;
\.


--
-- TOC entry 2737 (class 0 OID 26664)
-- Dependencies: 1964
-- Data for Name: restriccion_de_brigada; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_de_brigada (id, brigada, periodo, dia, turno, valor, por_actividad, actividad) FROM stdin;
ancoragoh-2010-06-21 10:29:27.244+02-vitjejiwxbkn4589nos0-restriccion_de_brigada	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad
ancoragoh-2010-06-21 10:29:27.244+02-fqi30gln8xzs8oabz1ja-restriccion_de_brigada	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad
ancoragoh-2010-06-21 10:29:27.244+02-pywb05btgfwzk9d6n1ro-restriccion_de_brigada	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad
ancoragoh-2010-06-21 10:29:27.244+02-97ch3ilxkmtsm8yrr9mq-restriccion_de_brigada	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad
ancoragoh-2010-06-21 10:29:27.244+02-k727p87728g4wgx2utcm-restriccion_de_brigada	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad
ancoragoh-2010-06-21 10:29:27.244+02-nqbm9esoe3mpl0475a80-restriccion_de_brigada	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad
ancoragoh-2010-06-21 10:29:27.244+02-kd44mxq07rkhmwf5mtnm-restriccion_de_brigada	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	2	1	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad
ancoragoh-2010-06-21 10:29:27.244+02-psdpkh4e3l75s2xwm5by-restriccion_de_brigada	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad
ancoragoh-2010-06-21 10:29:27.244+02-jy0qn1musyh33t1f4gj5-restriccion_de_brigada	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad
ancoragoh-2010-06-21 10:29:27.244+02-liub33gpg5kntzpel0hy-restriccion_de_brigada	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	1	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad
ancoragoh-2010-06-21 10:29:27.244+02-f1vfw2rr37jg7pvve1pn-restriccion_de_brigada	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	2	1	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad
ancoragoh-2010-06-21 10:29:27.244+02-84u6j5hudww4qnpdhpp-restriccion_de_brigada	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad
ancoragoh-2010-06-21 10:29:27.244+02-sdo8zjebo8o0j19uxvoh-restriccion_de_brigada	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad
ancoragoh-2010-06-21 10:29:27.244+02-bd967zukwr0zmfs4ibrr-restriccion_de_brigada	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	1	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad
ancoragoh-2010-06-21 10:29:27.244+02-nsxp715np42r24rz6y2f-restriccion_de_brigada	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad
ancoragoh-2010-06-21 10:34:25.368+02-rnp7quljxr3q67b6yuf-restriccion_de_brigada	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	1	1	t	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad
ancoragoh-2010-06-21 10:34:25.368+02-d6hrzp6kig1k92c50d3c-restriccion_de_brigada	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	1	1	t	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad
ancoragoh-2010-06-21 10:34:25.368+02-i94p8olmc0xyc54kkdei-restriccion_de_brigada	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	6	1	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad
ancoragoh-2010-06-21 10:34:25.368+02-fcc7tgv6efppnz5dttm-restriccion_de_brigada	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	6	1	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad
ancoragoh-2010-06-21 10:34:25.368+02-wuu8fjlm8usdayvqlr41-restriccion_de_brigada	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	6	1	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad
ancoragoh-2010-06-21 10:34:25.368+02-a0jvt6ngz9ok5xfoe40-restriccion_de_brigada	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	2	1	t	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad
ancoragoh-2010-06-21 10:34:25.368+02-wusqmhe4lry9ie3vm5hg-restriccion_de_brigada	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	2	1	t	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad
ancoragoh-2010-06-21 10:34:25.368+02-4ozhu3b5xo6hqw3w9mo5-restriccion_de_brigada	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	1	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad
ancoragoh-2010-06-21 10:34:25.368+02-rvin9qvqo4vqdbw96pky-restriccion_de_brigada	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	1	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad
ancoragoh-2010-06-21 10:34:25.368+02-j866h9cy3p5h2n7onbfv-restriccion_de_brigada	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	1	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad
ancoragoh-2010-06-21 10:34:25.368+02-owmtdep0qfv7lwxxe1c-restriccion_de_brigada	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	1	1	t	ancoragoh-2010-06-21 10:34:25.368+02-4j6h9xj781z17uq4wdh-actividad
ancoragoh-2010-06-21 10:34:25.368+02-ta0u23snb866dg5z03rd-restriccion_de_brigada	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	4	1	t	ancoragoh-2010-06-21 10:34:25.368+02-2qlvbuuf1gstmwi621fq-actividad
ancoragoh-2010-06-21 10:34:25.368+02-z7dqfps6zkyqxira5wot-restriccion_de_brigada	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	2	1	t	ancoragoh-2010-06-21 10:34:25.368+02-uu4d0ms9imgd17js9kvh-actividad
ancoragoh-2010-06-21 10:34:25.368+02-gudu5qwm8pzprissuuwt-restriccion_de_brigada	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	1	t	ancoragoh-2010-06-21 10:34:25.368+02-jjfo5peus2gi1oqflxnb-actividad
ancoragoh-2010-06-21 10:34:25.368+02-2evmupk61c5d6yn5sgzy-restriccion_de_brigada	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	3	1	t	ancoragoh-2010-06-21 10:34:25.368+02-gz5metcrgni2sz9is4k-actividad
ancoragoh-2010-06-21 10:34:25.368+02-zucv4b0lyjsgzvfiebc-restriccion_de_brigada	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	2	1	t	ancoragoh-2010-06-21 10:34:25.368+02-rnjxg0d6i6mgnvl6cekc-actividad
ancoragoh-2010-06-21 10:34:25.368+02-t6brb8pqtug1gqyrvc8t-restriccion_de_brigada	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	3	1	t	ancoragoh-2010-06-21 10:34:25.368+02-993bfq5nzu2cg8n0op0-actividad
ancoragoh-2010-06-21 10:34:25.368+02-kwzvi270mtg9yyoyckkp-restriccion_de_brigada	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	1	1	t	ancoragoh-2010-06-21 10:34:25.368+02-xxcg1opaocqvg1pzn90j-actividad
ancoragoh-2010-06-21 10:34:25.368+02-g0cbftgjn3jgi6j4rp5-restriccion_de_brigada	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	6	1	t	ancoragoh-2010-06-21 10:34:25.368+02-291k2752pq8hkl0if3hu-actividad
ancoragoh-2010-06-21 10:34:25.368+02-bhf4ysi0zis1w28t93hu-restriccion_de_brigada	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	1	t	ancoragoh-2010-06-21 10:34:25.368+02-hufh2wrihcfmjyktjbnr-actividad
ancoragoh-2010-06-21 10:34:25.368+02-097z1t52lo7mcgmo01h-restriccion_de_brigada	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	3	1	t	ancoragoh-2010-06-21 10:34:25.368+02-z0p3fdhkzlurpgietr8s-actividad
ancoragoh-2010-06-21 10:34:25.368+02-ib1eto6ix9t2pq9n5h0l-restriccion_de_brigada	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	2	1	t	ancoragoh-2010-06-21 10:34:25.368+02-dheqwmhkype1m2nmjghf-actividad
ancoragoh-2010-06-21 10:34:25.368+02-di0g1wti6o78gh4p1kvd-restriccion_de_brigada	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	2	5	1	t	ancoragoh-2010-06-21 10:34:25.368+02-pi7oqevd0muvj3oqnyyj-actividad
ancoragoh-2010-06-21 10:34:25.368+02-eys3b9k921gfmg0kay0g-restriccion_de_brigada	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	4	1	t	ancoragoh-2010-06-21 10:34:25.368+02-8s77vuzj442o0pmbxsa-actividad
ancoragoh-2010-06-21 10:34:25.368+02-rwvk1h1o1916zbyo6nfe-restriccion_de_brigada	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	4	1	t	ancoragoh-2010-06-21 10:34:25.368+02-0toqess6ildwgfm5g0x4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-hy1zpzddloxfnpj6yf8z-restriccion_de_brigada	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad
ancoragoh-2010-06-21 10:29:27.244+02-rcrkelvetc639wqots6i-restriccion_de_brigada	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad
ancoragoh-2010-06-21 10:29:27.244+02-emfqzkcmdaul0354ehb-restriccion_de_brigada	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad
ancoragoh-2010-06-21 10:29:27.244+02-60otr3yjfocatmcujntf-restriccion_de_brigada	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad
ancoragoh-2010-06-21 10:29:27.244+02-6wuct0sigw8t8ptl3dhf-restriccion_de_brigada	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad
ancoragoh-2010-06-21 10:29:27.244+02-tee9j1qz6wx9gaqyop3q-restriccion_de_brigada	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	2	1	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad
ancoragoh-2010-06-21 10:29:27.244+02-ujeh2ybvilhp7ajlyvvk-restriccion_de_brigada	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	2	1	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad
ancoragoh-2010-06-21 10:29:27.244+02-q9lcjp2ofmkovame89pl-restriccion_de_brigada	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	1	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-yceyjl96khym4jwbgl7-restriccion_de_brigada	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	1	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-58rvubcy2pvc4ptogqlf-restriccion_de_brigada	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	1	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-gnj5ccjqzsso9w1j5m4-restriccion_de_brigada	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	3	1	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad
ancoragoh-2010-06-21 10:29:27.244+02-3n7o538i7tip2sk0413u-restriccion_de_brigada	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	3	1	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad
ancoragoh-2010-06-21 10:29:27.244+02-0fqlmufuvlf5gglha2jc-restriccion_de_brigada	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	4	1	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad
ancoragoh-2010-06-21 10:29:27.244+02-b8fw54v1shjm7r89ie7b-restriccion_de_brigada	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	4	1	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad
ancoragoh-2010-06-21 10:29:27.244+02-dm0vrqad3mhp3ljcy6r7-restriccion_de_brigada	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	4	1	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad
ancoragoh-2010-06-21 10:29:27.244+02-cjsdblf2ljtztrkw3ldg-restriccion_de_brigada	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	4	1	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad
ancoragoh-2010-06-21 10:29:27.244+02-no386kz2eqdv8fc0vdqv-restriccion_de_brigada	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	4	1	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad
ancoragoh-2010-06-21 10:29:27.244+02-fkurdssl4el2q2pqxj8-restriccion_de_brigada	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	3	1	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-x08nz9kpjnylqu4yfkko-restriccion_de_brigada	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	3	1	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-4hozfqxu9rx4d8o66afx-restriccion_de_brigada	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	3	1	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad
\.


--
-- TOC entry 2738 (class 0 OID 26673)
-- Dependencies: 1965
-- Data for Name: restriccion_de_clasif_act; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_de_clasif_act (id, clasificacion, periodo, dia, turno, valor) FROM stdin;
\.


--
-- TOC entry 2739 (class 0 OID 26681)
-- Dependencies: 1966
-- Data for Name: restriccion_de_especialidad; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_de_especialidad (id, especialidad, periodo, dia, turno, valor) FROM stdin;
\.


--
-- TOC entry 2740 (class 0 OID 26689)
-- Dependencies: 1967
-- Data for Name: restriccion_de_lugar; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_de_lugar (id, lugar, periodo, dia, turno, valor, por_actividad, actividad) FROM stdin;
ancoragoh-2010-06-21 10:34:25.368+02-q2i58hwb1x47kcwp0pki-restriccion_de_lugar	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	1	1	t	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad
ancoragoh-2010-06-21 10:34:25.368+02-wzozkwjkvb5wt048cn6t-restriccion_de_lugar	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	6	1	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad
ancoragoh-2010-06-21 10:34:25.368+02-px2d1iei6ndhnjcxxcz4-restriccion_de_lugar	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	2	1	t	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad
ancoragoh-2010-06-21 10:34:25.368+02-yya2499hcrockr7vzunb-restriccion_de_lugar	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	1	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad
ancoragoh-2010-06-21 10:34:25.368+02-56uddpbw3o6z8fce7r-restriccion_de_lugar	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	1	1	t	ancoragoh-2010-06-21 10:34:25.368+02-4j6h9xj781z17uq4wdh-actividad
ancoragoh-2010-06-21 10:34:25.368+02-bxdp3mmckx76twe7eyzi-restriccion_de_lugar	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	4	1	t	ancoragoh-2010-06-21 10:34:25.368+02-2qlvbuuf1gstmwi621fq-actividad
ancoragoh-2010-06-21 10:34:25.368+02-0e05wu8kh4gasy4gcz6y-restriccion_de_lugar	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	2	1	t	ancoragoh-2010-06-21 10:34:25.368+02-uu4d0ms9imgd17js9kvh-actividad
ancoragoh-2010-06-21 10:34:25.368+02-84nd8z41z06vgwhy3oz1-restriccion_de_lugar	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	1	t	ancoragoh-2010-06-21 10:34:25.368+02-jjfo5peus2gi1oqflxnb-actividad
ancoragoh-2010-06-21 10:34:25.368+02-iy7sgmxy1qmvkzlky3j-restriccion_de_lugar	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	3	1	t	ancoragoh-2010-06-21 10:34:25.368+02-gz5metcrgni2sz9is4k-actividad
ancoragoh-2010-06-21 10:34:25.368+02-x00rpyaqst3hx6ngr8yc-restriccion_de_lugar	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	2	1	t	ancoragoh-2010-06-21 10:34:25.368+02-rnjxg0d6i6mgnvl6cekc-actividad
ancoragoh-2010-06-21 10:34:25.368+02-h1uorzliwjb2s6lhrqn-restriccion_de_lugar	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	3	1	t	ancoragoh-2010-06-21 10:34:25.368+02-993bfq5nzu2cg8n0op0-actividad
ancoragoh-2010-06-21 10:34:25.368+02-dj2zt75jvsr6b3led9v0-restriccion_de_lugar	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	1	1	t	ancoragoh-2010-06-21 10:34:25.368+02-xxcg1opaocqvg1pzn90j-actividad
ancoragoh-2010-06-21 10:34:25.368+02-nhilqgqibfh0ltz0p1qw-restriccion_de_lugar	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	6	1	t	ancoragoh-2010-06-21 10:34:25.368+02-291k2752pq8hkl0if3hu-actividad
ancoragoh-2010-06-21 10:34:25.368+02-dp9z3esi0ks5wuz4xft-restriccion_de_lugar	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	1	t	ancoragoh-2010-06-21 10:34:25.368+02-hufh2wrihcfmjyktjbnr-actividad
ancoragoh-2010-06-21 10:34:25.368+02-qg19gc29lvm5mkkuthk-restriccion_de_lugar	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	3	1	t	ancoragoh-2010-06-21 10:34:25.368+02-z0p3fdhkzlurpgietr8s-actividad
ancoragoh-2010-06-21 10:34:25.368+02-8rcwh5qsl3kk8hqfgdp3-restriccion_de_lugar	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	2	1	t	ancoragoh-2010-06-21 10:34:25.368+02-dheqwmhkype1m2nmjghf-actividad
ancoragoh-2010-06-21 10:34:25.368+02-kilkibn1be7ytm55ucw-restriccion_de_lugar	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	2	5	1	t	ancoragoh-2010-06-21 10:34:25.368+02-pi7oqevd0muvj3oqnyyj-actividad
ancoragoh-2010-06-21 10:34:25.368+02-q62m5o19jnco0qwhihsd-restriccion_de_lugar	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	4	1	t	ancoragoh-2010-06-21 10:34:25.368+02-8s77vuzj442o0pmbxsa-actividad
ancoragoh-2010-06-21 10:34:25.368+02-ifye4h6vv2ipcwlg6izj-restriccion_de_lugar	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	4	1	t	ancoragoh-2010-06-21 10:34:25.368+02-0toqess6ildwgfm5g0x4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-kybxqjlp28l130diwh-restriccion_de_lugar	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad
ancoragoh-2010-06-21 10:29:27.244+02-sqd451112ftc8p5ytcxy-restriccion_de_lugar	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad
ancoragoh-2010-06-21 10:29:27.244+02-trnt1zzaxevjvnb3prz-restriccion_de_lugar	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	2	1	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad
ancoragoh-2010-06-21 10:29:27.244+02-p9b9228l0u2eo4riwiu7-restriccion_de_lugar	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	1	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-v7i4q5tr4i208pbkokpy-restriccion_de_lugar	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	3	1	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad
ancoragoh-2010-06-21 10:29:27.244+02-5841yzv2241ego5h8jnx-restriccion_de_lugar	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	4	1	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad
ancoragoh-2010-06-21 10:29:27.244+02-oon7iscy4o3gq332pxd6-restriccion_de_lugar	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	4	1	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad
ancoragoh-2010-06-21 10:29:27.244+02-tdxlv6gy6sp2224blaq4-restriccion_de_lugar	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	3	1	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-6cv48ofkuwohv2t3io0v-restriccion_de_lugar	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad
ancoragoh-2010-06-21 10:29:27.244+02-3bw9y81kk9qdkurpbrxe-restriccion_de_lugar	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad
ancoragoh-2010-06-21 10:29:27.244+02-070zvmsgeau2yrezchdm-restriccion_de_lugar	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad
ancoragoh-2010-06-21 10:29:27.244+02-hrlqwdrrbmmc3x34p3g7-restriccion_de_lugar	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	2	1	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad
ancoragoh-2010-06-21 10:29:27.244+02-zz4nbqicisu2tmfqx2oa-restriccion_de_lugar	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad
ancoragoh-2010-06-21 10:29:27.244+02-2i9x1pvxkvjp984k7s45-restriccion_de_lugar	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad
ancoragoh-2010-06-21 10:29:27.244+02-hdimv5j3dexz7muj5xom-restriccion_de_lugar	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	1	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad
ancoragoh-2010-06-21 10:29:27.244+02-bvne1wjclgfif9m0zli-restriccion_de_lugar	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	2	1	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad
ancoragoh-2010-06-21 10:29:27.244+02-594u8885mjwqczk52mc3-restriccion_de_lugar	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad
ancoragoh-2010-06-21 10:29:27.244+02-mxfl68zqhbniwslyfvot-restriccion_de_lugar	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad
ancoragoh-2010-06-21 10:29:27.244+02-zjcbvwuee633sr0jnll-restriccion_de_lugar	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	1	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad
ancoragoh-2010-06-21 10:29:27.244+02-dqujfc34klusutfbm1gj-restriccion_de_lugar	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad
\.


--
-- TOC entry 2741 (class 0 OID 26698)
-- Dependencies: 1968
-- Data for Name: restriccion_de_otro_recurso; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_de_otro_recurso (id, otro_recurso, periodo, dia, turno, valor, por_actividad, actividad) FROM stdin;
\.


--
-- TOC entry 2742 (class 0 OID 26707)
-- Dependencies: 1969
-- Data for Name: restriccion_de_periodo; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_de_periodo (id, periodo, dia, turno, valor) FROM stdin;
restriccion_de_periodo250710_02312412800610843vesc5qs4i8dyq5y13cju68lkak4s6qhjymrhfidns0aor8zok9g7wn	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	1
restriccion_de_periodo250710_0231241280061084b1aypljgdv7lzlmtlxo4snowpxh8vighglepzxoblp6bvn55wzhs0ejp476vyznrx3wbn5927dhw5k1bkvnb52rdva6e	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	2	1
restriccion_de_periodo250710_0231241280061084c8vtertp85n6x7crc0emn8rckcw1ajs9k6w3mz756f5hd0or1wc3b53y	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	3	1
\.


--
-- TOC entry 2743 (class 0 OID 26715)
-- Dependencies: 1970
-- Data for Name: restriccion_de_profesor; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_de_profesor (id, profesor, periodo, dia, turno, valor, por_actividad, actividad) FROM stdin;
ancoragoh-2010-06-21 10:34:25.368+02-j3w1cbcqad715zxq45gv-restriccion_de_profesor	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	1	1	t	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad
ancoragoh-2010-06-21 10:34:25.368+02-n7dgztit4szyw4p1bqi-restriccion_de_profesor	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	6	1	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad
ancoragoh-2010-06-21 10:34:25.368+02-8cl4h2jo4ew1fjyt33s8-restriccion_de_profesor	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	2	1	t	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad
ancoragoh-2010-06-21 10:34:25.368+02-72pkw083wkiyhgigeuqy-restriccion_de_profesor	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	1	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad
ancoragoh-2010-06-21 10:34:25.368+02-smdq006l3izemxzjtd47-restriccion_de_profesor	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	1	1	t	ancoragoh-2010-06-21 10:34:25.368+02-4j6h9xj781z17uq4wdh-actividad
ancoragoh-2010-06-21 10:34:25.368+02-xzfxr66v4o9ibl4wtj-restriccion_de_profesor	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	4	1	t	ancoragoh-2010-06-21 10:34:25.368+02-2qlvbuuf1gstmwi621fq-actividad
ancoragoh-2010-06-21 10:34:25.368+02-mluzfhpopw9mwnorn5ca-restriccion_de_profesor	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	2	1	t	ancoragoh-2010-06-21 10:34:25.368+02-uu4d0ms9imgd17js9kvh-actividad
ancoragoh-2010-06-21 10:34:25.368+02-krsbc38xh4oez3uesld-restriccion_de_profesor	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	1	t	ancoragoh-2010-06-21 10:34:25.368+02-jjfo5peus2gi1oqflxnb-actividad
ancoragoh-2010-06-21 10:34:25.368+02-ew258xb8yhp6cizys3uy-restriccion_de_profesor	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	3	1	t	ancoragoh-2010-06-21 10:34:25.368+02-gz5metcrgni2sz9is4k-actividad
ancoragoh-2010-06-21 10:34:25.368+02-ld4qqjfk8k2rggxsk2d2-restriccion_de_profesor	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	2	1	t	ancoragoh-2010-06-21 10:34:25.368+02-rnjxg0d6i6mgnvl6cekc-actividad
ancoragoh-2010-06-21 10:34:25.368+02-ego0cl09f32z8fas8cl-restriccion_de_profesor	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	3	1	t	ancoragoh-2010-06-21 10:34:25.368+02-993bfq5nzu2cg8n0op0-actividad
ancoragoh-2010-06-21 10:34:25.368+02-97brz7xcxsym0yqxhr4z-restriccion_de_profesor	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	1	1	t	ancoragoh-2010-06-21 10:34:25.368+02-xxcg1opaocqvg1pzn90j-actividad
ancoragoh-2010-06-21 10:34:25.368+02-oqh00f63dpfwgniv20q-restriccion_de_profesor	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	6	1	t	ancoragoh-2010-06-21 10:34:25.368+02-291k2752pq8hkl0if3hu-actividad
ancoragoh-2010-06-21 10:34:25.368+02-x4opkg5vbn7n5fv7c9xp-restriccion_de_profesor	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	1	t	ancoragoh-2010-06-21 10:34:25.368+02-hufh2wrihcfmjyktjbnr-actividad
ancoragoh-2010-06-21 10:34:25.368+02-fqxj3xh7iavh0k447hu6-restriccion_de_profesor	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	3	1	t	ancoragoh-2010-06-21 10:34:25.368+02-z0p3fdhkzlurpgietr8s-actividad
ancoragoh-2010-06-21 10:34:25.368+02-3eeb43c35kn00rxl84l1-restriccion_de_profesor	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	2	1	t	ancoragoh-2010-06-21 10:34:25.368+02-dheqwmhkype1m2nmjghf-actividad
ancoragoh-2010-06-21 10:34:25.368+02-lp6fjsakxd2916ux8p7v-restriccion_de_profesor	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	2	5	1	t	ancoragoh-2010-06-21 10:34:25.368+02-pi7oqevd0muvj3oqnyyj-actividad
ancoragoh-2010-06-21 10:34:25.368+02-1pb43cxst9vxsca4c4u2-restriccion_de_profesor	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	4	1	t	ancoragoh-2010-06-21 10:34:25.368+02-8s77vuzj442o0pmbxsa-actividad
ancoragoh-2010-06-21 10:34:25.368+02-bwbxr0zr0olprw0qmgu1-restriccion_de_profesor	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	4	1	t	ancoragoh-2010-06-21 10:34:25.368+02-0toqess6ildwgfm5g0x4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-mi368ghaud7aljo2qtby-restriccion_de_profesor	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad
ancoragoh-2010-06-21 10:29:27.244+02-19tcwjx39pqm8qjn7awe-restriccion_de_profesor	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad
ancoragoh-2010-06-21 10:29:27.244+02-hjt110jit9c6m6myux8e-restriccion_de_profesor	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad
ancoragoh-2010-06-21 10:29:27.244+02-s35c5vkrtpj7l7y074rv-restriccion_de_profesor	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	2	1	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad
ancoragoh-2010-06-21 10:29:27.244+02-5xdxo9xcvucyzt0f404j-restriccion_de_profesor	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	1	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-hsgk8kt1lkbgzhncse8x-restriccion_de_profesor	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	3	1	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad
ancoragoh-2010-06-21 10:29:27.244+02-3unnocydqksojknp6hx-restriccion_de_profesor	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	4	1	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad
ancoragoh-2010-06-21 10:29:27.244+02-w900esri270f1mjb2b5n-restriccion_de_profesor	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	4	1	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad
ancoragoh-2010-06-21 10:29:27.244+02-bmcyw9xfy4sborhzr7i-restriccion_de_profesor	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	3	1	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-4tif6851v1nsv4bx5ks-restriccion_de_profesor	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad
ancoragoh-2010-06-21 10:29:27.244+02-ddifk3ix3efbqglioenp-restriccion_de_profesor	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad
ancoragoh-2010-06-21 10:29:27.244+02-ijwsqjk7jlfpffkagub-restriccion_de_profesor	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad
ancoragoh-2010-06-21 10:29:27.244+02-x00b8rnolrdi1frw6bot-restriccion_de_profesor	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	2	1	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad
ancoragoh-2010-06-21 10:29:27.244+02-yhm4k08t4xkot6np6rs-restriccion_de_profesor	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad
ancoragoh-2010-06-21 10:29:27.244+02-38wsvrx5e5z84k8x26i-restriccion_de_profesor	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	6	1	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad
ancoragoh-2010-06-21 10:29:27.244+02-o9imgu9g82sxvpuwtpdv-restriccion_de_profesor	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	1	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad
ancoragoh-2010-06-21 10:29:27.244+02-w9b81400mj0pqg9bowkc-restriccion_de_profesor	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	2	1	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad
ancoragoh-2010-06-21 10:29:27.244+02-y5jk4d7kty90leqe0gg-restriccion_de_profesor	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad
ancoragoh-2010-06-21 10:29:27.244+02-gfdml61xh62lb7einno3-restriccion_de_profesor	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	1	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad
ancoragoh-2010-06-21 10:29:27.244+02-elcwprioido1u9g2u88u-restriccion_de_profesor	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	1	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad
\.


--
-- TOC entry 2744 (class 0 OID 26724)
-- Dependencies: 1971
-- Data for Name: restriccion_por_actividad; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_por_actividad (restriccion_nominal, actividad) FROM stdin;
\.


--
-- TOC entry 2745 (class 0 OID 26730)
-- Dependencies: 1972
-- Data for Name: restriccion_por_actividad_de_brigada; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_por_actividad_de_brigada (restriccion_nominal, actividad) FROM stdin;
ancoragoh-2010-06-21 10:29:27.244+02-vitjejiwxbkn4589nos0-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad
ancoragoh-2010-06-21 10:29:27.244+02-fqi30gln8xzs8oabz1ja-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad
ancoragoh-2010-06-21 10:29:27.244+02-pywb05btgfwzk9d6n1ro-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad
ancoragoh-2010-06-21 10:29:27.244+02-97ch3ilxkmtsm8yrr9mq-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad
ancoragoh-2010-06-21 10:29:27.244+02-k727p87728g4wgx2utcm-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad
ancoragoh-2010-06-21 10:29:27.244+02-nqbm9esoe3mpl0475a80-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad
ancoragoh-2010-06-21 10:29:27.244+02-kd44mxq07rkhmwf5mtnm-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad
ancoragoh-2010-06-21 10:29:27.244+02-psdpkh4e3l75s2xwm5by-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad
ancoragoh-2010-06-21 10:29:27.244+02-jy0qn1musyh33t1f4gj5-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad
ancoragoh-2010-06-21 10:29:27.244+02-liub33gpg5kntzpel0hy-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad
ancoragoh-2010-06-21 10:29:27.244+02-f1vfw2rr37jg7pvve1pn-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad
ancoragoh-2010-06-21 10:29:27.244+02-84u6j5hudww4qnpdhpp-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad
ancoragoh-2010-06-21 10:29:27.244+02-sdo8zjebo8o0j19uxvoh-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad
ancoragoh-2010-06-21 10:29:27.244+02-bd967zukwr0zmfs4ibrr-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad
ancoragoh-2010-06-21 10:29:27.244+02-nsxp715np42r24rz6y2f-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad
ancoragoh-2010-06-21 10:34:25.368+02-rnp7quljxr3q67b6yuf-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad
ancoragoh-2010-06-21 10:34:25.368+02-d6hrzp6kig1k92c50d3c-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad
ancoragoh-2010-06-21 10:34:25.368+02-i94p8olmc0xyc54kkdei-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad
ancoragoh-2010-06-21 10:34:25.368+02-fcc7tgv6efppnz5dttm-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad
ancoragoh-2010-06-21 10:34:25.368+02-wuu8fjlm8usdayvqlr41-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad
ancoragoh-2010-06-21 10:34:25.368+02-a0jvt6ngz9ok5xfoe40-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad
ancoragoh-2010-06-21 10:34:25.368+02-wusqmhe4lry9ie3vm5hg-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad
ancoragoh-2010-06-21 10:34:25.368+02-4ozhu3b5xo6hqw3w9mo5-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad
ancoragoh-2010-06-21 10:34:25.368+02-rvin9qvqo4vqdbw96pky-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad
ancoragoh-2010-06-21 10:34:25.368+02-j866h9cy3p5h2n7onbfv-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad
ancoragoh-2010-06-21 10:34:25.368+02-owmtdep0qfv7lwxxe1c-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-4j6h9xj781z17uq4wdh-actividad
ancoragoh-2010-06-21 10:34:25.368+02-ta0u23snb866dg5z03rd-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-2qlvbuuf1gstmwi621fq-actividad
ancoragoh-2010-06-21 10:34:25.368+02-z7dqfps6zkyqxira5wot-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-uu4d0ms9imgd17js9kvh-actividad
ancoragoh-2010-06-21 10:34:25.368+02-gudu5qwm8pzprissuuwt-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-jjfo5peus2gi1oqflxnb-actividad
ancoragoh-2010-06-21 10:34:25.368+02-2evmupk61c5d6yn5sgzy-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-gz5metcrgni2sz9is4k-actividad
ancoragoh-2010-06-21 10:34:25.368+02-zucv4b0lyjsgzvfiebc-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-rnjxg0d6i6mgnvl6cekc-actividad
ancoragoh-2010-06-21 10:34:25.368+02-t6brb8pqtug1gqyrvc8t-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-993bfq5nzu2cg8n0op0-actividad
ancoragoh-2010-06-21 10:34:25.368+02-kwzvi270mtg9yyoyckkp-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-xxcg1opaocqvg1pzn90j-actividad
ancoragoh-2010-06-21 10:34:25.368+02-g0cbftgjn3jgi6j4rp5-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-291k2752pq8hkl0if3hu-actividad
ancoragoh-2010-06-21 10:34:25.368+02-bhf4ysi0zis1w28t93hu-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-hufh2wrihcfmjyktjbnr-actividad
ancoragoh-2010-06-21 10:34:25.368+02-097z1t52lo7mcgmo01h-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-z0p3fdhkzlurpgietr8s-actividad
ancoragoh-2010-06-21 10:34:25.368+02-ib1eto6ix9t2pq9n5h0l-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-dheqwmhkype1m2nmjghf-actividad
ancoragoh-2010-06-21 10:34:25.368+02-di0g1wti6o78gh4p1kvd-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-pi7oqevd0muvj3oqnyyj-actividad
ancoragoh-2010-06-21 10:34:25.368+02-eys3b9k921gfmg0kay0g-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-8s77vuzj442o0pmbxsa-actividad
ancoragoh-2010-06-21 10:34:25.368+02-rwvk1h1o1916zbyo6nfe-restriccion_de_brigada	ancoragoh-2010-06-21 10:34:25.368+02-0toqess6ildwgfm5g0x4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-6wuct0sigw8t8ptl3dhf-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad
ancoragoh-2010-06-21 10:29:27.244+02-hy1zpzddloxfnpj6yf8z-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad
ancoragoh-2010-06-21 10:29:27.244+02-rcrkelvetc639wqots6i-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad
ancoragoh-2010-06-21 10:29:27.244+02-emfqzkcmdaul0354ehb-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad
ancoragoh-2010-06-21 10:29:27.244+02-60otr3yjfocatmcujntf-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad
ancoragoh-2010-06-21 10:29:27.244+02-tee9j1qz6wx9gaqyop3q-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad
ancoragoh-2010-06-21 10:29:27.244+02-ujeh2ybvilhp7ajlyvvk-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad
ancoragoh-2010-06-21 10:29:27.244+02-q9lcjp2ofmkovame89pl-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-yceyjl96khym4jwbgl7-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-58rvubcy2pvc4ptogqlf-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-gnj5ccjqzsso9w1j5m4-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad
ancoragoh-2010-06-21 10:29:27.244+02-3n7o538i7tip2sk0413u-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad
ancoragoh-2010-06-21 10:29:27.244+02-0fqlmufuvlf5gglha2jc-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad
ancoragoh-2010-06-21 10:29:27.244+02-b8fw54v1shjm7r89ie7b-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad
ancoragoh-2010-06-21 10:29:27.244+02-dm0vrqad3mhp3ljcy6r7-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad
ancoragoh-2010-06-21 10:29:27.244+02-cjsdblf2ljtztrkw3ldg-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad
ancoragoh-2010-06-21 10:29:27.244+02-no386kz2eqdv8fc0vdqv-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad
ancoragoh-2010-06-21 10:29:27.244+02-fkurdssl4el2q2pqxj8-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-x08nz9kpjnylqu4yfkko-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-4hozfqxu9rx4d8o66afx-restriccion_de_brigada	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad
\.


--
-- TOC entry 2746 (class 0 OID 26736)
-- Dependencies: 1973
-- Data for Name: restriccion_por_actividad_de_lugar; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_por_actividad_de_lugar (restriccion_nominal, actividad) FROM stdin;
ancoragoh-2010-06-21 10:29:27.244+02-kybxqjlp28l130diwh-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad
ancoragoh-2010-06-21 10:29:27.244+02-sqd451112ftc8p5ytcxy-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad
ancoragoh-2010-06-21 10:29:27.244+02-trnt1zzaxevjvnb3prz-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad
ancoragoh-2010-06-21 10:29:27.244+02-p9b9228l0u2eo4riwiu7-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-v7i4q5tr4i208pbkokpy-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad
ancoragoh-2010-06-21 10:29:27.244+02-5841yzv2241ego5h8jnx-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad
ancoragoh-2010-06-21 10:29:27.244+02-oon7iscy4o3gq332pxd6-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad
ancoragoh-2010-06-21 10:29:27.244+02-tdxlv6gy6sp2224blaq4-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-6cv48ofkuwohv2t3io0v-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad
ancoragoh-2010-06-21 10:29:27.244+02-3bw9y81kk9qdkurpbrxe-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad
ancoragoh-2010-06-21 10:29:27.244+02-070zvmsgeau2yrezchdm-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad
ancoragoh-2010-06-21 10:29:27.244+02-hrlqwdrrbmmc3x34p3g7-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad
ancoragoh-2010-06-21 10:29:27.244+02-zz4nbqicisu2tmfqx2oa-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad
ancoragoh-2010-06-21 10:29:27.244+02-2i9x1pvxkvjp984k7s45-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad
ancoragoh-2010-06-21 10:29:27.244+02-hdimv5j3dexz7muj5xom-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad
ancoragoh-2010-06-21 10:29:27.244+02-bvne1wjclgfif9m0zli-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad
ancoragoh-2010-06-21 10:29:27.244+02-594u8885mjwqczk52mc3-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad
ancoragoh-2010-06-21 10:29:27.244+02-mxfl68zqhbniwslyfvot-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad
ancoragoh-2010-06-21 10:29:27.244+02-zjcbvwuee633sr0jnll-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad
ancoragoh-2010-06-21 10:29:27.244+02-dqujfc34klusutfbm1gj-restriccion_de_lugar	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad
ancoragoh-2010-06-21 10:34:25.368+02-q2i58hwb1x47kcwp0pki-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad
ancoragoh-2010-06-21 10:34:25.368+02-wzozkwjkvb5wt048cn6t-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad
ancoragoh-2010-06-21 10:34:25.368+02-px2d1iei6ndhnjcxxcz4-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad
ancoragoh-2010-06-21 10:34:25.368+02-yya2499hcrockr7vzunb-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad
ancoragoh-2010-06-21 10:34:25.368+02-56uddpbw3o6z8fce7r-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-4j6h9xj781z17uq4wdh-actividad
ancoragoh-2010-06-21 10:34:25.368+02-bxdp3mmckx76twe7eyzi-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-2qlvbuuf1gstmwi621fq-actividad
ancoragoh-2010-06-21 10:34:25.368+02-0e05wu8kh4gasy4gcz6y-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-uu4d0ms9imgd17js9kvh-actividad
ancoragoh-2010-06-21 10:34:25.368+02-84nd8z41z06vgwhy3oz1-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-jjfo5peus2gi1oqflxnb-actividad
ancoragoh-2010-06-21 10:34:25.368+02-iy7sgmxy1qmvkzlky3j-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-gz5metcrgni2sz9is4k-actividad
ancoragoh-2010-06-21 10:34:25.368+02-x00rpyaqst3hx6ngr8yc-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-rnjxg0d6i6mgnvl6cekc-actividad
ancoragoh-2010-06-21 10:34:25.368+02-h1uorzliwjb2s6lhrqn-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-993bfq5nzu2cg8n0op0-actividad
ancoragoh-2010-06-21 10:34:25.368+02-dj2zt75jvsr6b3led9v0-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-xxcg1opaocqvg1pzn90j-actividad
ancoragoh-2010-06-21 10:34:25.368+02-nhilqgqibfh0ltz0p1qw-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-291k2752pq8hkl0if3hu-actividad
ancoragoh-2010-06-21 10:34:25.368+02-dp9z3esi0ks5wuz4xft-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-hufh2wrihcfmjyktjbnr-actividad
ancoragoh-2010-06-21 10:34:25.368+02-qg19gc29lvm5mkkuthk-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-z0p3fdhkzlurpgietr8s-actividad
ancoragoh-2010-06-21 10:34:25.368+02-8rcwh5qsl3kk8hqfgdp3-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-dheqwmhkype1m2nmjghf-actividad
ancoragoh-2010-06-21 10:34:25.368+02-kilkibn1be7ytm55ucw-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-pi7oqevd0muvj3oqnyyj-actividad
ancoragoh-2010-06-21 10:34:25.368+02-q62m5o19jnco0qwhihsd-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-8s77vuzj442o0pmbxsa-actividad
ancoragoh-2010-06-21 10:34:25.368+02-ifye4h6vv2ipcwlg6izj-restriccion_de_lugar	ancoragoh-2010-06-21 10:34:25.368+02-0toqess6ildwgfm5g0x4-actividad
\.


--
-- TOC entry 2747 (class 0 OID 26742)
-- Dependencies: 1974
-- Data for Name: restriccion_por_actividad_de_otro_recurso; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_por_actividad_de_otro_recurso (restriccion_nominal, actividad) FROM stdin;
\.


--
-- TOC entry 2748 (class 0 OID 26748)
-- Dependencies: 1975
-- Data for Name: restriccion_por_actividad_de_profesor; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_por_actividad_de_profesor (restriccion_nominal, actividad) FROM stdin;
ancoragoh-2010-06-21 10:29:27.244+02-19tcwjx39pqm8qjn7awe-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad
ancoragoh-2010-06-21 10:29:27.244+02-hjt110jit9c6m6myux8e-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad
ancoragoh-2010-06-21 10:29:27.244+02-s35c5vkrtpj7l7y074rv-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad
ancoragoh-2010-06-21 10:29:27.244+02-5xdxo9xcvucyzt0f404j-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-hsgk8kt1lkbgzhncse8x-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad
ancoragoh-2010-06-21 10:29:27.244+02-3unnocydqksojknp6hx-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad
ancoragoh-2010-06-21 10:29:27.244+02-w900esri270f1mjb2b5n-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad
ancoragoh-2010-06-21 10:29:27.244+02-bmcyw9xfy4sborhzr7i-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad
ancoragoh-2010-06-21 10:29:27.244+02-4tif6851v1nsv4bx5ks-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad
ancoragoh-2010-06-21 10:29:27.244+02-ddifk3ix3efbqglioenp-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad
ancoragoh-2010-06-21 10:29:27.244+02-ijwsqjk7jlfpffkagub-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad
ancoragoh-2010-06-21 10:29:27.244+02-x00b8rnolrdi1frw6bot-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad
ancoragoh-2010-06-21 10:29:27.244+02-yhm4k08t4xkot6np6rs-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad
ancoragoh-2010-06-21 10:29:27.244+02-38wsvrx5e5z84k8x26i-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad
ancoragoh-2010-06-21 10:29:27.244+02-o9imgu9g82sxvpuwtpdv-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad
ancoragoh-2010-06-21 10:29:27.244+02-w9b81400mj0pqg9bowkc-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad
ancoragoh-2010-06-21 10:29:27.244+02-y5jk4d7kty90leqe0gg-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad
ancoragoh-2010-06-21 10:29:27.244+02-gfdml61xh62lb7einno3-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad
ancoragoh-2010-06-21 10:29:27.244+02-elcwprioido1u9g2u88u-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad
ancoragoh-2010-06-21 10:29:27.244+02-mi368ghaud7aljo2qtby-restriccion_de_profesor	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad
ancoragoh-2010-06-21 10:34:25.368+02-j3w1cbcqad715zxq45gv-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad
ancoragoh-2010-06-21 10:34:25.368+02-n7dgztit4szyw4p1bqi-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad
ancoragoh-2010-06-21 10:34:25.368+02-8cl4h2jo4ew1fjyt33s8-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad
ancoragoh-2010-06-21 10:34:25.368+02-72pkw083wkiyhgigeuqy-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad
ancoragoh-2010-06-21 10:34:25.368+02-smdq006l3izemxzjtd47-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-4j6h9xj781z17uq4wdh-actividad
ancoragoh-2010-06-21 10:34:25.368+02-xzfxr66v4o9ibl4wtj-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-2qlvbuuf1gstmwi621fq-actividad
ancoragoh-2010-06-21 10:34:25.368+02-mluzfhpopw9mwnorn5ca-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-uu4d0ms9imgd17js9kvh-actividad
ancoragoh-2010-06-21 10:34:25.368+02-krsbc38xh4oez3uesld-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-jjfo5peus2gi1oqflxnb-actividad
ancoragoh-2010-06-21 10:34:25.368+02-ew258xb8yhp6cizys3uy-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-gz5metcrgni2sz9is4k-actividad
ancoragoh-2010-06-21 10:34:25.368+02-ld4qqjfk8k2rggxsk2d2-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-rnjxg0d6i6mgnvl6cekc-actividad
ancoragoh-2010-06-21 10:34:25.368+02-ego0cl09f32z8fas8cl-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-993bfq5nzu2cg8n0op0-actividad
ancoragoh-2010-06-21 10:34:25.368+02-97brz7xcxsym0yqxhr4z-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-xxcg1opaocqvg1pzn90j-actividad
ancoragoh-2010-06-21 10:34:25.368+02-oqh00f63dpfwgniv20q-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-291k2752pq8hkl0if3hu-actividad
ancoragoh-2010-06-21 10:34:25.368+02-x4opkg5vbn7n5fv7c9xp-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-hufh2wrihcfmjyktjbnr-actividad
ancoragoh-2010-06-21 10:34:25.368+02-fqxj3xh7iavh0k447hu6-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-z0p3fdhkzlurpgietr8s-actividad
ancoragoh-2010-06-21 10:34:25.368+02-3eeb43c35kn00rxl84l1-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-dheqwmhkype1m2nmjghf-actividad
ancoragoh-2010-06-21 10:34:25.368+02-lp6fjsakxd2916ux8p7v-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-pi7oqevd0muvj3oqnyyj-actividad
ancoragoh-2010-06-21 10:34:25.368+02-1pb43cxst9vxsca4c4u2-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-8s77vuzj442o0pmbxsa-actividad
ancoragoh-2010-06-21 10:34:25.368+02-bwbxr0zr0olprw0qmgu1-restriccion_de_profesor	ancoragoh-2010-06-21 10:34:25.368+02-0toqess6ildwgfm5g0x4-actividad
\.


--
-- TOC entry 2749 (class 0 OID 26754)
-- Dependencies: 1976
-- Data for Name: restriccion_real; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_real (restriccion_nominal, periodicidad, fecha, hora_inicial, hora_final, por_actividad, actividad, objeto) FROM stdin;
\.


--
-- TOC entry 2750 (class 0 OID 26761)
-- Dependencies: 1977
-- Data for Name: restriccion_real_de_asignatura; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_real_de_asignatura (restriccion_nominal, periodicidad, fecha, hora_inicial, hora_final, por_actividad, actividad, objeto) FROM stdin;
\.


--
-- TOC entry 2751 (class 0 OID 26768)
-- Dependencies: 1978
-- Data for Name: restriccion_real_de_brigada; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_real_de_brigada (restriccion_nominal, periodicidad, fecha, hora_inicial, hora_final, por_actividad, actividad, objeto) FROM stdin;
ancoragoh-2010-06-21 10:34:25.368+02-rnp7quljxr3q67b6yuf-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:34:25.368+02-rnp7quljxr3q67b6yuf-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:34:25.368+02-d6hrzp6kig1k92c50d3c-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:34:25.368+02-d6hrzp6kig1k92c50d3c-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-6wuct0sigw8t8ptl3dhf-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-6wuct0sigw8t8ptl3dhf-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-6wuct0sigw8t8ptl3dhf-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-6wuct0sigw8t8ptl3dhf-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-6wuct0sigw8t8ptl3dhf-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-6wuct0sigw8t8ptl3dhf-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:34:25.368+02-i94p8olmc0xyc54kkdei-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:34:25.368+02-i94p8olmc0xyc54kkdei-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-vitjejiwxbkn4589nos0-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3898	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-vitjejiwxbkn4589nos0-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3904	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-vitjejiwxbkn4589nos0-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3911	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-vitjejiwxbkn4589nos0-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3924	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-vitjejiwxbkn4589nos0-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3944	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-vitjejiwxbkn4589nos0-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3960	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-vitjejiwxbkn4589nos0-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3987	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-hy1zpzddloxfnpj6yf8z-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-hy1zpzddloxfnpj6yf8z-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-vitjejiwxbkn4589nos0-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	4000	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:34:25.368+02-fcc7tgv6efppnz5dttm-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:34:25.368+02-fcc7tgv6efppnz5dttm-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:34:25.368+02-wuu8fjlm8usdayvqlr41-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:34:25.368+02-wuu8fjlm8usdayvqlr41-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-6wuct0sigw8t8ptl3dhf-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-6wuct0sigw8t8ptl3dhf-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:34:25.368+02-a0jvt6ngz9ok5xfoe40-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:34:25.368+02-a0jvt6ngz9ok5xfoe40-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-hy1zpzddloxfnpj6yf8z-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-hy1zpzddloxfnpj6yf8z-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-hy1zpzddloxfnpj6yf8z-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-hy1zpzddloxfnpj6yf8z-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:34:25.368+02-wusqmhe4lry9ie3vm5hg-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:34:25.368+02-wusqmhe4lry9ie3vm5hg-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:34:25.368+02-4ozhu3b5xo6hqw3w9mo5-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:34:25.368+02-4ozhu3b5xo6hqw3w9mo5-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-hy1zpzddloxfnpj6yf8z-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-hy1zpzddloxfnpj6yf8z-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:34:25.368+02-rvin9qvqo4vqdbw96pky-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:34:25.368+02-rvin9qvqo4vqdbw96pky-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-rcrkelvetc639wqots6i-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-rcrkelvetc639wqots6i-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:34:25.368+02-j866h9cy3p5h2n7onbfv-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:34:25.368+02-j866h9cy3p5h2n7onbfv-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:34:25.368+02-owmtdep0qfv7lwxxe1c-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-4j6h9xj781z17uq4wdh-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-rcrkelvetc639wqots6i-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-rcrkelvetc639wqots6i-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-rcrkelvetc639wqots6i-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-rcrkelvetc639wqots6i-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-rcrkelvetc639wqots6i-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-rcrkelvetc639wqots6i-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-emfqzkcmdaul0354ehb-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:34:25.368+02-owmtdep0qfv7lwxxe1c-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-4j6h9xj781z17uq4wdh-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:34:25.368+02-ta0u23snb866dg5z03rd-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-2qlvbuuf1gstmwi621fq-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-60otr3yjfocatmcujntf-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-60otr3yjfocatmcujntf-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:34:25.368+02-ta0u23snb866dg5z03rd-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-2qlvbuuf1gstmwi621fq-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-fqi30gln8xzs8oabz1ja-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3898	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-fqi30gln8xzs8oabz1ja-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3904	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:34:25.368+02-z7dqfps6zkyqxira5wot-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-uu4d0ms9imgd17js9kvh-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-emfqzkcmdaul0354ehb-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-emfqzkcmdaul0354ehb-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-emfqzkcmdaul0354ehb-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-emfqzkcmdaul0354ehb-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-emfqzkcmdaul0354ehb-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-emfqzkcmdaul0354ehb-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-emfqzkcmdaul0354ehb-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:34:25.368+02-z7dqfps6zkyqxira5wot-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-uu4d0ms9imgd17js9kvh-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:34:25.368+02-gudu5qwm8pzprissuuwt-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-jjfo5peus2gi1oqflxnb-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-60otr3yjfocatmcujntf-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-60otr3yjfocatmcujntf-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:34:25.368+02-gudu5qwm8pzprissuuwt-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-jjfo5peus2gi1oqflxnb-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-60otr3yjfocatmcujntf-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-60otr3yjfocatmcujntf-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:34:25.368+02-2evmupk61c5d6yn5sgzy-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-gz5metcrgni2sz9is4k-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-60otr3yjfocatmcujntf-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-60otr3yjfocatmcujntf-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:34:25.368+02-2evmupk61c5d6yn5sgzy-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-gz5metcrgni2sz9is4k-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:34:25.368+02-zucv4b0lyjsgzvfiebc-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-rnjxg0d6i6mgnvl6cekc-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:34:25.368+02-zucv4b0lyjsgzvfiebc-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-rnjxg0d6i6mgnvl6cekc-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-fqi30gln8xzs8oabz1ja-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3911	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-fqi30gln8xzs8oabz1ja-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3924	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-fqi30gln8xzs8oabz1ja-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3944	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-fqi30gln8xzs8oabz1ja-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3960	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-fqi30gln8xzs8oabz1ja-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3987	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-fqi30gln8xzs8oabz1ja-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	4000	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-ujeh2ybvilhp7ajlyvvk-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:34:25.368+02-t6brb8pqtug1gqyrvc8t-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-993bfq5nzu2cg8n0op0-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:34:25.368+02-t6brb8pqtug1gqyrvc8t-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-993bfq5nzu2cg8n0op0-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-tee9j1qz6wx9gaqyop3q-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-tee9j1qz6wx9gaqyop3q-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-tee9j1qz6wx9gaqyop3q-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-tee9j1qz6wx9gaqyop3q-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-tee9j1qz6wx9gaqyop3q-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-tee9j1qz6wx9gaqyop3q-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-tee9j1qz6wx9gaqyop3q-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-tee9j1qz6wx9gaqyop3q-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:34:25.368+02-kwzvi270mtg9yyoyckkp-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-xxcg1opaocqvg1pzn90j-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:34:25.368+02-kwzvi270mtg9yyoyckkp-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-xxcg1opaocqvg1pzn90j-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:34:25.368+02-g0cbftgjn3jgi6j4rp5-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-291k2752pq8hkl0if3hu-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:34:25.368+02-g0cbftgjn3jgi6j4rp5-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-291k2752pq8hkl0if3hu-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-ujeh2ybvilhp7ajlyvvk-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-ujeh2ybvilhp7ajlyvvk-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-ujeh2ybvilhp7ajlyvvk-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-ujeh2ybvilhp7ajlyvvk-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-ujeh2ybvilhp7ajlyvvk-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-ujeh2ybvilhp7ajlyvvk-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-ujeh2ybvilhp7ajlyvvk-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:34:25.368+02-bhf4ysi0zis1w28t93hu-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-hufh2wrihcfmjyktjbnr-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:34:25.368+02-bhf4ysi0zis1w28t93hu-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-hufh2wrihcfmjyktjbnr-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:34:25.368+02-097z1t52lo7mcgmo01h-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-z0p3fdhkzlurpgietr8s-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:34:25.368+02-097z1t52lo7mcgmo01h-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-z0p3fdhkzlurpgietr8s-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:34:25.368+02-ib1eto6ix9t2pq9n5h0l-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-dheqwmhkype1m2nmjghf-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:34:25.368+02-ib1eto6ix9t2pq9n5h0l-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-dheqwmhkype1m2nmjghf-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-q9lcjp2ofmkovame89pl-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-q9lcjp2ofmkovame89pl-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-q9lcjp2ofmkovame89pl-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-q9lcjp2ofmkovame89pl-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-q9lcjp2ofmkovame89pl-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-q9lcjp2ofmkovame89pl-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-q9lcjp2ofmkovame89pl-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-q9lcjp2ofmkovame89pl-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:34:25.368+02-di0g1wti6o78gh4p1kvd-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3898	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-pi7oqevd0muvj3oqnyyj-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:34:25.368+02-di0g1wti6o78gh4p1kvd-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3904	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-pi7oqevd0muvj3oqnyyj-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:34:25.368+02-eys3b9k921gfmg0kay0g-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-8s77vuzj442o0pmbxsa-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:34:25.368+02-eys3b9k921gfmg0kay0g-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-8s77vuzj442o0pmbxsa-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:34:25.368+02-rwvk1h1o1916zbyo6nfe-restriccion_de_brigada	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-0toqess6ildwgfm5g0x4-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:34:25.368+02-rwvk1h1o1916zbyo6nfe-restriccion_de_brigada	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-0toqess6ildwgfm5g0x4-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-yceyjl96khym4jwbgl7-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-yceyjl96khym4jwbgl7-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-yceyjl96khym4jwbgl7-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-yceyjl96khym4jwbgl7-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-yceyjl96khym4jwbgl7-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-yceyjl96khym4jwbgl7-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-yceyjl96khym4jwbgl7-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-yceyjl96khym4jwbgl7-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-58rvubcy2pvc4ptogqlf-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-58rvubcy2pvc4ptogqlf-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-pywb05btgfwzk9d6n1ro-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3898	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-pywb05btgfwzk9d6n1ro-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3904	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-58rvubcy2pvc4ptogqlf-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-58rvubcy2pvc4ptogqlf-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-58rvubcy2pvc4ptogqlf-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-gnj5ccjqzsso9w1j5m4-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-gnj5ccjqzsso9w1j5m4-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-58rvubcy2pvc4ptogqlf-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-58rvubcy2pvc4ptogqlf-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-58rvubcy2pvc4ptogqlf-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-gnj5ccjqzsso9w1j5m4-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-gnj5ccjqzsso9w1j5m4-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-gnj5ccjqzsso9w1j5m4-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-gnj5ccjqzsso9w1j5m4-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-gnj5ccjqzsso9w1j5m4-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-gnj5ccjqzsso9w1j5m4-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-3n7o538i7tip2sk0413u-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-3n7o538i7tip2sk0413u-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-3n7o538i7tip2sk0413u-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-3n7o538i7tip2sk0413u-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-3n7o538i7tip2sk0413u-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-3n7o538i7tip2sk0413u-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-3n7o538i7tip2sk0413u-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-3n7o538i7tip2sk0413u-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-0fqlmufuvlf5gglha2jc-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-pywb05btgfwzk9d6n1ro-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3911	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-pywb05btgfwzk9d6n1ro-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3924	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-pywb05btgfwzk9d6n1ro-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3944	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-pywb05btgfwzk9d6n1ro-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3960	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-b8fw54v1shjm7r89ie7b-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-b8fw54v1shjm7r89ie7b-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-b8fw54v1shjm7r89ie7b-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-b8fw54v1shjm7r89ie7b-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-0fqlmufuvlf5gglha2jc-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-0fqlmufuvlf5gglha2jc-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-0fqlmufuvlf5gglha2jc-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-0fqlmufuvlf5gglha2jc-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-0fqlmufuvlf5gglha2jc-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-0fqlmufuvlf5gglha2jc-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-0fqlmufuvlf5gglha2jc-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-pywb05btgfwzk9d6n1ro-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3987	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-pywb05btgfwzk9d6n1ro-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	4000	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-dm0vrqad3mhp3ljcy6r7-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-dm0vrqad3mhp3ljcy6r7-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-dm0vrqad3mhp3ljcy6r7-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-b8fw54v1shjm7r89ie7b-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-b8fw54v1shjm7r89ie7b-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-b8fw54v1shjm7r89ie7b-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-b8fw54v1shjm7r89ie7b-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-dm0vrqad3mhp3ljcy6r7-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-dm0vrqad3mhp3ljcy6r7-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-dm0vrqad3mhp3ljcy6r7-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-dm0vrqad3mhp3ljcy6r7-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-dm0vrqad3mhp3ljcy6r7-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-cjsdblf2ljtztrkw3ldg-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-97ch3ilxkmtsm8yrr9mq-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3898	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-97ch3ilxkmtsm8yrr9mq-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3904	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-97ch3ilxkmtsm8yrr9mq-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3911	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-97ch3ilxkmtsm8yrr9mq-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3924	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-97ch3ilxkmtsm8yrr9mq-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3944	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-cjsdblf2ljtztrkw3ldg-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-cjsdblf2ljtztrkw3ldg-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-cjsdblf2ljtztrkw3ldg-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-no386kz2eqdv8fc0vdqv-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-cjsdblf2ljtztrkw3ldg-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-cjsdblf2ljtztrkw3ldg-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-cjsdblf2ljtztrkw3ldg-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-cjsdblf2ljtztrkw3ldg-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-no386kz2eqdv8fc0vdqv-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-no386kz2eqdv8fc0vdqv-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-no386kz2eqdv8fc0vdqv-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-no386kz2eqdv8fc0vdqv-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-no386kz2eqdv8fc0vdqv-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-no386kz2eqdv8fc0vdqv-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-no386kz2eqdv8fc0vdqv-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-fkurdssl4el2q2pqxj8-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-fkurdssl4el2q2pqxj8-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-fkurdssl4el2q2pqxj8-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-fkurdssl4el2q2pqxj8-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-fkurdssl4el2q2pqxj8-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-fkurdssl4el2q2pqxj8-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-fkurdssl4el2q2pqxj8-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-fkurdssl4el2q2pqxj8-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-97ch3ilxkmtsm8yrr9mq-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3960	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-97ch3ilxkmtsm8yrr9mq-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3987	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-97ch3ilxkmtsm8yrr9mq-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	4000	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-4hozfqxu9rx4d8o66afx-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-4hozfqxu9rx4d8o66afx-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-x08nz9kpjnylqu4yfkko-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-x08nz9kpjnylqu4yfkko-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-x08nz9kpjnylqu4yfkko-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-x08nz9kpjnylqu4yfkko-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-x08nz9kpjnylqu4yfkko-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-x08nz9kpjnylqu4yfkko-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-x08nz9kpjnylqu4yfkko-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-x08nz9kpjnylqu4yfkko-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-k727p87728g4wgx2utcm-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3898	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-4hozfqxu9rx4d8o66afx-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-4hozfqxu9rx4d8o66afx-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-4hozfqxu9rx4d8o66afx-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-4hozfqxu9rx4d8o66afx-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-4hozfqxu9rx4d8o66afx-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-4hozfqxu9rx4d8o66afx-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-k727p87728g4wgx2utcm-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3904	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-k727p87728g4wgx2utcm-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3911	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-k727p87728g4wgx2utcm-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3924	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-k727p87728g4wgx2utcm-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3944	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-k727p87728g4wgx2utcm-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3960	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-k727p87728g4wgx2utcm-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3987	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-k727p87728g4wgx2utcm-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	4000	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-nqbm9esoe3mpl0475a80-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-nqbm9esoe3mpl0475a80-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-nqbm9esoe3mpl0475a80-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-nqbm9esoe3mpl0475a80-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-nqbm9esoe3mpl0475a80-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-nqbm9esoe3mpl0475a80-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-nqbm9esoe3mpl0475a80-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-nqbm9esoe3mpl0475a80-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-kd44mxq07rkhmwf5mtnm-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-kd44mxq07rkhmwf5mtnm-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-kd44mxq07rkhmwf5mtnm-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-kd44mxq07rkhmwf5mtnm-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-kd44mxq07rkhmwf5mtnm-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-kd44mxq07rkhmwf5mtnm-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-kd44mxq07rkhmwf5mtnm-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-kd44mxq07rkhmwf5mtnm-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-psdpkh4e3l75s2xwm5by-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3898	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-psdpkh4e3l75s2xwm5by-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3904	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-psdpkh4e3l75s2xwm5by-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3911	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-psdpkh4e3l75s2xwm5by-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3924	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-psdpkh4e3l75s2xwm5by-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3944	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-psdpkh4e3l75s2xwm5by-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3960	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-psdpkh4e3l75s2xwm5by-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3987	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-psdpkh4e3l75s2xwm5by-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	4000	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-jy0qn1musyh33t1f4gj5-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-jy0qn1musyh33t1f4gj5-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-jy0qn1musyh33t1f4gj5-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-jy0qn1musyh33t1f4gj5-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-jy0qn1musyh33t1f4gj5-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-jy0qn1musyh33t1f4gj5-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-jy0qn1musyh33t1f4gj5-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-jy0qn1musyh33t1f4gj5-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-liub33gpg5kntzpel0hy-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-liub33gpg5kntzpel0hy-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-liub33gpg5kntzpel0hy-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-liub33gpg5kntzpel0hy-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-liub33gpg5kntzpel0hy-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-liub33gpg5kntzpel0hy-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-liub33gpg5kntzpel0hy-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-liub33gpg5kntzpel0hy-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-f1vfw2rr37jg7pvve1pn-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-f1vfw2rr37jg7pvve1pn-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-f1vfw2rr37jg7pvve1pn-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-f1vfw2rr37jg7pvve1pn-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-f1vfw2rr37jg7pvve1pn-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-f1vfw2rr37jg7pvve1pn-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-f1vfw2rr37jg7pvve1pn-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-f1vfw2rr37jg7pvve1pn-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	brigada130410_0142571271180577xtci6r84q8ae993ppa5tpqt1qq6bpsodcrl89j1og2tfzmuempy36itmzone83h0jt8
ancoragoh-2010-06-21 10:29:27.244+02-84u6j5hudww4qnpdhpp-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-84u6j5hudww4qnpdhpp-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-84u6j5hudww4qnpdhpp-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-84u6j5hudww4qnpdhpp-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-84u6j5hudww4qnpdhpp-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-84u6j5hudww4qnpdhpp-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-84u6j5hudww4qnpdhpp-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-84u6j5hudww4qnpdhpp-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	brigada180610_0352551276869175zq4rflb639herm77v9ip814q5gyhhjqmlojecwxj2n10jwea756tt0vkstosxq57micxn4wteyx1o2y0vv6s
ancoragoh-2010-06-21 10:29:27.244+02-sdo8zjebo8o0j19uxvoh-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-sdo8zjebo8o0j19uxvoh-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-sdo8zjebo8o0j19uxvoh-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-sdo8zjebo8o0j19uxvoh-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-sdo8zjebo8o0j19uxvoh-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-sdo8zjebo8o0j19uxvoh-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-sdo8zjebo8o0j19uxvoh-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-sdo8zjebo8o0j19uxvoh-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	brigada180410_1055101271645710rjuk5zjl2xcqjz1c38r0l9btl6vu2j7qdheaklbu5z5az8jymdrn13jqle44ey51uuudwmzh3es8a
ancoragoh-2010-06-21 10:29:27.244+02-bd967zukwr0zmfs4ibrr-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-bd967zukwr0zmfs4ibrr-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-bd967zukwr0zmfs4ibrr-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-bd967zukwr0zmfs4ibrr-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-bd967zukwr0zmfs4ibrr-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-bd967zukwr0zmfs4ibrr-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-bd967zukwr0zmfs4ibrr-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-bd967zukwr0zmfs4ibrr-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	brigada110410_08492412710333645wkda1m5jennlg53n13ygdwe7fl1linvwg1ltl60js2hte6bv9fdeodqmix106lbkdb88kksfkboxp
ancoragoh-2010-06-21 10:29:27.244+02-nsxp715np42r24rz6y2f-restriccion_de_brigada	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-nsxp715np42r24rz6y2f-restriccion_de_brigada	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-nsxp715np42r24rz6y2f-restriccion_de_brigada	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-nsxp715np42r24rz6y2f-restriccion_de_brigada	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-nsxp715np42r24rz6y2f-restriccion_de_brigada	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-nsxp715np42r24rz6y2f-restriccion_de_brigada	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-nsxp715np42r24rz6y2f-restriccion_de_brigada	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
ancoragoh-2010-06-21 10:29:27.244+02-nsxp715np42r24rz6y2f-restriccion_de_brigada	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	brigada180410_100147127164250857qcattk9jxerbnmam2elfqh28s3ixtwa2x69iplqu9fkz5d5ffn94sy9gajzjh4
\.


--
-- TOC entry 2752 (class 0 OID 26775)
-- Dependencies: 1979
-- Data for Name: restriccion_real_de_clasif_act; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_real_de_clasif_act (restriccion_nominal, periodicidad, fecha, hora_inicial, hora_final, por_actividad, actividad, objeto) FROM stdin;
\.


--
-- TOC entry 2753 (class 0 OID 26782)
-- Dependencies: 1980
-- Data for Name: restriccion_real_de_especialidad; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_real_de_especialidad (restriccion_nominal, periodicidad, fecha, hora_inicial, hora_final, por_actividad, actividad, objeto) FROM stdin;
\.


--
-- TOC entry 2754 (class 0 OID 26789)
-- Dependencies: 1981
-- Data for Name: restriccion_real_de_lugar; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_real_de_lugar (restriccion_nominal, periodicidad, fecha, hora_inicial, hora_final, por_actividad, actividad, objeto) FROM stdin;
ancoragoh-2010-06-21 10:34:25.368+02-q2i58hwb1x47kcwp0pki-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:34:25.368+02-q2i58hwb1x47kcwp0pki-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:34:25.368+02-wzozkwjkvb5wt048cn6t-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:34:25.368+02-wzozkwjkvb5wt048cn6t-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-kybxqjlp28l130diwh-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-kybxqjlp28l130diwh-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-kybxqjlp28l130diwh-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-kybxqjlp28l130diwh-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-kybxqjlp28l130diwh-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-kybxqjlp28l130diwh-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:34:25.368+02-px2d1iei6ndhnjcxxcz4-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:34:25.368+02-px2d1iei6ndhnjcxxcz4-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-6cv48ofkuwohv2t3io0v-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3898	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-6cv48ofkuwohv2t3io0v-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3904	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-sqd451112ftc8p5ytcxy-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-sqd451112ftc8p5ytcxy-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-6cv48ofkuwohv2t3io0v-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3911	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-6cv48ofkuwohv2t3io0v-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3924	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-sqd451112ftc8p5ytcxy-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-sqd451112ftc8p5ytcxy-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:34:25.368+02-yya2499hcrockr7vzunb-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:34:25.368+02-yya2499hcrockr7vzunb-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-sqd451112ftc8p5ytcxy-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-sqd451112ftc8p5ytcxy-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:34:25.368+02-56uddpbw3o6z8fce7r-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-4j6h9xj781z17uq4wdh-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:34:25.368+02-56uddpbw3o6z8fce7r-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-4j6h9xj781z17uq4wdh-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-kybxqjlp28l130diwh-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-kybxqjlp28l130diwh-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-6cv48ofkuwohv2t3io0v-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3944	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-6cv48ofkuwohv2t3io0v-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3960	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-trnt1zzaxevjvnb3prz-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-6cv48ofkuwohv2t3io0v-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3987	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-6cv48ofkuwohv2t3io0v-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	4000	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-trnt1zzaxevjvnb3prz-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-trnt1zzaxevjvnb3prz-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:34:25.368+02-bxdp3mmckx76twe7eyzi-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-2qlvbuuf1gstmwi621fq-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:34:25.368+02-bxdp3mmckx76twe7eyzi-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-2qlvbuuf1gstmwi621fq-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-trnt1zzaxevjvnb3prz-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-trnt1zzaxevjvnb3prz-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-trnt1zzaxevjvnb3prz-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:34:25.368+02-0e05wu8kh4gasy4gcz6y-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-uu4d0ms9imgd17js9kvh-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:34:25.368+02-0e05wu8kh4gasy4gcz6y-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-uu4d0ms9imgd17js9kvh-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-sqd451112ftc8p5ytcxy-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-sqd451112ftc8p5ytcxy-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:34:25.368+02-84nd8z41z06vgwhy3oz1-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-jjfo5peus2gi1oqflxnb-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:34:25.368+02-84nd8z41z06vgwhy3oz1-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-jjfo5peus2gi1oqflxnb-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-p9b9228l0u2eo4riwiu7-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-p9b9228l0u2eo4riwiu7-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:34:25.368+02-iy7sgmxy1qmvkzlky3j-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-gz5metcrgni2sz9is4k-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:34:25.368+02-iy7sgmxy1qmvkzlky3j-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-gz5metcrgni2sz9is4k-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-p9b9228l0u2eo4riwiu7-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-p9b9228l0u2eo4riwiu7-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-p9b9228l0u2eo4riwiu7-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-p9b9228l0u2eo4riwiu7-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:34:25.368+02-x00rpyaqst3hx6ngr8yc-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-rnjxg0d6i6mgnvl6cekc-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:34:25.368+02-x00rpyaqst3hx6ngr8yc-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-rnjxg0d6i6mgnvl6cekc-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-trnt1zzaxevjvnb3prz-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-trnt1zzaxevjvnb3prz-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:34:25.368+02-h1uorzliwjb2s6lhrqn-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-993bfq5nzu2cg8n0op0-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:34:25.368+02-h1uorzliwjb2s6lhrqn-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-993bfq5nzu2cg8n0op0-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-3bw9y81kk9qdkurpbrxe-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3898	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-3bw9y81kk9qdkurpbrxe-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3904	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-3bw9y81kk9qdkurpbrxe-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3911	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-3bw9y81kk9qdkurpbrxe-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3924	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:34:25.368+02-dj2zt75jvsr6b3led9v0-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-xxcg1opaocqvg1pzn90j-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:34:25.368+02-dj2zt75jvsr6b3led9v0-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-xxcg1opaocqvg1pzn90j-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-v7i4q5tr4i208pbkokpy-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-v7i4q5tr4i208pbkokpy-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-v7i4q5tr4i208pbkokpy-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-v7i4q5tr4i208pbkokpy-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:34:25.368+02-nhilqgqibfh0ltz0p1qw-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-291k2752pq8hkl0if3hu-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:34:25.368+02-nhilqgqibfh0ltz0p1qw-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-291k2752pq8hkl0if3hu-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-p9b9228l0u2eo4riwiu7-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-p9b9228l0u2eo4riwiu7-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-3bw9y81kk9qdkurpbrxe-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3944	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-3bw9y81kk9qdkurpbrxe-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3960	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-3bw9y81kk9qdkurpbrxe-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3987	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:29:27.244+02-3bw9y81kk9qdkurpbrxe-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	4000	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	lugar220410_09240612719858468ai705s9rjqm1ebwn1y8x0o4kgxosgojblescq22l84bbuia81s1g
ancoragoh-2010-06-21 10:34:25.368+02-dp9z3esi0ks5wuz4xft-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-hufh2wrihcfmjyktjbnr-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:34:25.368+02-dp9z3esi0ks5wuz4xft-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-hufh2wrihcfmjyktjbnr-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-5841yzv2241ego5h8jnx-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-5841yzv2241ego5h8jnx-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-5841yzv2241ego5h8jnx-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-5841yzv2241ego5h8jnx-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:34:25.368+02-qg19gc29lvm5mkkuthk-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-z0p3fdhkzlurpgietr8s-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:34:25.368+02-qg19gc29lvm5mkkuthk-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-z0p3fdhkzlurpgietr8s-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-v7i4q5tr4i208pbkokpy-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-v7i4q5tr4i208pbkokpy-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-v7i4q5tr4i208pbkokpy-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-v7i4q5tr4i208pbkokpy-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:34:25.368+02-8rcwh5qsl3kk8hqfgdp3-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-dheqwmhkype1m2nmjghf-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:34:25.368+02-8rcwh5qsl3kk8hqfgdp3-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-dheqwmhkype1m2nmjghf-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:34:25.368+02-kilkibn1be7ytm55ucw-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3898	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-pi7oqevd0muvj3oqnyyj-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:34:25.368+02-kilkibn1be7ytm55ucw-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3904	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-pi7oqevd0muvj3oqnyyj-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-oon7iscy4o3gq332pxd6-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-oon7iscy4o3gq332pxd6-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-oon7iscy4o3gq332pxd6-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-oon7iscy4o3gq332pxd6-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-5841yzv2241ego5h8jnx-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-5841yzv2241ego5h8jnx-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-5841yzv2241ego5h8jnx-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-5841yzv2241ego5h8jnx-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:34:25.368+02-q62m5o19jnco0qwhihsd-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-8s77vuzj442o0pmbxsa-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:34:25.368+02-q62m5o19jnco0qwhihsd-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-8s77vuzj442o0pmbxsa-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:34:25.368+02-ifye4h6vv2ipcwlg6izj-restriccion_de_lugar	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-0toqess6ildwgfm5g0x4-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:34:25.368+02-ifye4h6vv2ipcwlg6izj-restriccion_de_lugar	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-0toqess6ildwgfm5g0x4-actividad	lugar120410_04235012711038302tl74edasoe27m25qqqlgeq2r0ponbtev5bo0eortsip50klq2ww6dnm
ancoragoh-2010-06-21 10:29:27.244+02-oon7iscy4o3gq332pxd6-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-oon7iscy4o3gq332pxd6-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-oon7iscy4o3gq332pxd6-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-oon7iscy4o3gq332pxd6-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-tdxlv6gy6sp2224blaq4-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-tdxlv6gy6sp2224blaq4-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-tdxlv6gy6sp2224blaq4-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-tdxlv6gy6sp2224blaq4-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-070zvmsgeau2yrezchdm-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-070zvmsgeau2yrezchdm-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-070zvmsgeau2yrezchdm-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-070zvmsgeau2yrezchdm-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-070zvmsgeau2yrezchdm-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-070zvmsgeau2yrezchdm-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-070zvmsgeau2yrezchdm-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-070zvmsgeau2yrezchdm-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-tdxlv6gy6sp2224blaq4-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-tdxlv6gy6sp2224blaq4-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-tdxlv6gy6sp2224blaq4-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-tdxlv6gy6sp2224blaq4-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-hrlqwdrrbmmc3x34p3g7-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hrlqwdrrbmmc3x34p3g7-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hrlqwdrrbmmc3x34p3g7-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hrlqwdrrbmmc3x34p3g7-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hrlqwdrrbmmc3x34p3g7-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hrlqwdrrbmmc3x34p3g7-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hrlqwdrrbmmc3x34p3g7-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hrlqwdrrbmmc3x34p3g7-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-zz4nbqicisu2tmfqx2oa-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3898	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-zz4nbqicisu2tmfqx2oa-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3904	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-zz4nbqicisu2tmfqx2oa-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3911	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-zz4nbqicisu2tmfqx2oa-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3924	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-zz4nbqicisu2tmfqx2oa-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3944	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-zz4nbqicisu2tmfqx2oa-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3960	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-zz4nbqicisu2tmfqx2oa-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3987	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-zz4nbqicisu2tmfqx2oa-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	4000	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-2i9x1pvxkvjp984k7s45-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-2i9x1pvxkvjp984k7s45-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-2i9x1pvxkvjp984k7s45-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-2i9x1pvxkvjp984k7s45-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-2i9x1pvxkvjp984k7s45-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-2i9x1pvxkvjp984k7s45-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-2i9x1pvxkvjp984k7s45-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-2i9x1pvxkvjp984k7s45-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hdimv5j3dexz7muj5xom-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hdimv5j3dexz7muj5xom-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hdimv5j3dexz7muj5xom-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hdimv5j3dexz7muj5xom-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hdimv5j3dexz7muj5xom-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hdimv5j3dexz7muj5xom-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hdimv5j3dexz7muj5xom-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-hdimv5j3dexz7muj5xom-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	lugar200610_0201351276992095d80paklpkwhfe1ufja7t8jg6365lfx7ox83itvspt9rc66z1iz
ancoragoh-2010-06-21 10:29:27.244+02-bvne1wjclgfif9m0zli-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-bvne1wjclgfif9m0zli-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-bvne1wjclgfif9m0zli-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-bvne1wjclgfif9m0zli-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-bvne1wjclgfif9m0zli-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-bvne1wjclgfif9m0zli-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-bvne1wjclgfif9m0zli-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-bvne1wjclgfif9m0zli-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-594u8885mjwqczk52mc3-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-594u8885mjwqczk52mc3-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-594u8885mjwqczk52mc3-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-594u8885mjwqczk52mc3-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-594u8885mjwqczk52mc3-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-594u8885mjwqczk52mc3-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-594u8885mjwqczk52mc3-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-594u8885mjwqczk52mc3-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-mxfl68zqhbniwslyfvot-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-mxfl68zqhbniwslyfvot-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-mxfl68zqhbniwslyfvot-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-mxfl68zqhbniwslyfvot-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-mxfl68zqhbniwslyfvot-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-mxfl68zqhbniwslyfvot-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-mxfl68zqhbniwslyfvot-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-mxfl68zqhbniwslyfvot-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	lugar220410_0924001271985840ob1li075ypisy0ovzzxa1izcix2vftt000kbzwzd1rabdh7hi6xoe
ancoragoh-2010-06-21 10:29:27.244+02-zjcbvwuee633sr0jnll-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-zjcbvwuee633sr0jnll-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-zjcbvwuee633sr0jnll-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-zjcbvwuee633sr0jnll-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-zjcbvwuee633sr0jnll-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-zjcbvwuee633sr0jnll-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-zjcbvwuee633sr0jnll-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-zjcbvwuee633sr0jnll-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-dqujfc34klusutfbm1gj-restriccion_de_lugar	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-dqujfc34klusutfbm1gj-restriccion_de_lugar	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-dqujfc34klusutfbm1gj-restriccion_de_lugar	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-dqujfc34klusutfbm1gj-restriccion_de_lugar	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-dqujfc34klusutfbm1gj-restriccion_de_lugar	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-dqujfc34klusutfbm1gj-restriccion_de_lugar	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-dqujfc34klusutfbm1gj-restriccion_de_lugar	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
ancoragoh-2010-06-21 10:29:27.244+02-dqujfc34klusutfbm1gj-restriccion_de_lugar	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	lugar210410_0924521271899492tdcms7c3birqgmflt8n4iib948rfpksqx0tjtjmu15k5k9jb5jtyqb0xz00r1llee2nb8s0h3mrd3o1ysq
\.


--
-- TOC entry 2755 (class 0 OID 26796)
-- Dependencies: 1982
-- Data for Name: restriccion_real_de_otro_recurso; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_real_de_otro_recurso (restriccion_nominal, periodicidad, fecha, hora_inicial, hora_final, por_actividad, actividad, objeto) FROM stdin;
\.


--
-- TOC entry 2756 (class 0 OID 26803)
-- Dependencies: 1983
-- Data for Name: restriccion_real_de_periodo; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_real_de_periodo (restriccion_nominal, periodicidad, fecha, hora_inicial, hora_final, por_actividad, actividad, objeto) FROM stdin;
restriccion_de_periodo250710_02312412800610843vesc5qs4i8dyq5y13cju68lkak4s6qhjymrhfidns0aor8zok9g7wn	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	480	570	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_02312412800610843vesc5qs4i8dyq5y13cju68lkak4s6qhjymrhfidns0aor8zok9g7wn	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	480	570	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_02312412800610843vesc5qs4i8dyq5y13cju68lkak4s6qhjymrhfidns0aor8zok9g7wn	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	480	570	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_02312412800610843vesc5qs4i8dyq5y13cju68lkak4s6qhjymrhfidns0aor8zok9g7wn	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	480	570	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_02312412800610843vesc5qs4i8dyq5y13cju68lkak4s6qhjymrhfidns0aor8zok9g7wn	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	480	570	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_02312412800610843vesc5qs4i8dyq5y13cju68lkak4s6qhjymrhfidns0aor8zok9g7wn	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	480	570	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_02312412800610843vesc5qs4i8dyq5y13cju68lkak4s6qhjymrhfidns0aor8zok9g7wn	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	480	570	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_02312412800610843vesc5qs4i8dyq5y13cju68lkak4s6qhjymrhfidns0aor8zok9g7wn	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	480	570	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084b1aypljgdv7lzlmtlxo4snowpxh8vighglepzxoblp6bvn55wzhs0ejp476vyznrx3wbn5927dhw5k1bkvnb52rdva6e	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	570	660	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084b1aypljgdv7lzlmtlxo4snowpxh8vighglepzxoblp6bvn55wzhs0ejp476vyznrx3wbn5927dhw5k1bkvnb52rdva6e	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	570	660	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084b1aypljgdv7lzlmtlxo4snowpxh8vighglepzxoblp6bvn55wzhs0ejp476vyznrx3wbn5927dhw5k1bkvnb52rdva6e	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	570	660	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084b1aypljgdv7lzlmtlxo4snowpxh8vighglepzxoblp6bvn55wzhs0ejp476vyznrx3wbn5927dhw5k1bkvnb52rdva6e	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	570	660	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084b1aypljgdv7lzlmtlxo4snowpxh8vighglepzxoblp6bvn55wzhs0ejp476vyznrx3wbn5927dhw5k1bkvnb52rdva6e	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	570	660	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084b1aypljgdv7lzlmtlxo4snowpxh8vighglepzxoblp6bvn55wzhs0ejp476vyznrx3wbn5927dhw5k1bkvnb52rdva6e	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	570	660	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084b1aypljgdv7lzlmtlxo4snowpxh8vighglepzxoblp6bvn55wzhs0ejp476vyznrx3wbn5927dhw5k1bkvnb52rdva6e	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	570	660	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084b1aypljgdv7lzlmtlxo4snowpxh8vighglepzxoblp6bvn55wzhs0ejp476vyznrx3wbn5927dhw5k1bkvnb52rdva6e	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	570	660	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084c8vtertp85n6x7crc0emn8rckcw1ajs9k6w3mz756f5hd0or1wc3b53y	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	660	750	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084c8vtertp85n6x7crc0emn8rckcw1ajs9k6w3mz756f5hd0or1wc3b53y	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	660	750	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084c8vtertp85n6x7crc0emn8rckcw1ajs9k6w3mz756f5hd0or1wc3b53y	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	660	750	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084c8vtertp85n6x7crc0emn8rckcw1ajs9k6w3mz756f5hd0or1wc3b53y	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	660	750	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084c8vtertp85n6x7crc0emn8rckcw1ajs9k6w3mz756f5hd0or1wc3b53y	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	660	750	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084c8vtertp85n6x7crc0emn8rckcw1ajs9k6w3mz756f5hd0or1wc3b53y	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	660	750	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084c8vtertp85n6x7crc0emn8rckcw1ajs9k6w3mz756f5hd0or1wc3b53y	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	660	750	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
restriccion_de_periodo250710_0231241280061084c8vtertp85n6x7crc0emn8rckcw1ajs9k6w3mz756f5hd0or1wc3b53y	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	660	750	f	\N	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q
\.


--
-- TOC entry 2757 (class 0 OID 26810)
-- Dependencies: 1984
-- Data for Name: restriccion_real_de_profesor; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY restriccion_real_de_profesor (restriccion_nominal, periodicidad, fecha, hora_inicial, hora_final, por_actividad, actividad, objeto) FROM stdin;
ancoragoh-2010-06-21 10:34:25.368+02-j3w1cbcqad715zxq45gv-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:34:25.368+02-j3w1cbcqad715zxq45gv-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-zu9fmsi64gh1y2gqp4e6-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:34:25.368+02-n7dgztit4szyw4p1bqi-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:34:25.368+02-n7dgztit4szyw4p1bqi-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-f9ezr2rtmwhxzkcw89p-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:29:27.244+02-19tcwjx39pqm8qjn7awe-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:29:27.244+02-19tcwjx39pqm8qjn7awe-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:29:27.244+02-19tcwjx39pqm8qjn7awe-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:29:27.244+02-19tcwjx39pqm8qjn7awe-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:29:27.244+02-19tcwjx39pqm8qjn7awe-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:29:27.244+02-19tcwjx39pqm8qjn7awe-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:34:25.368+02-8cl4h2jo4ew1fjyt33s8-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:34:25.368+02-8cl4h2jo4ew1fjyt33s8-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-6eixsf2gkpk81ts2ahok-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:34:25.368+02-72pkw083wkiyhgigeuqy-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-4tif6851v1nsv4bx5ks-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3898	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-hjt110jit9c6m6myux8e-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:29:27.244+02-hjt110jit9c6m6myux8e-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:29:27.244+02-4tif6851v1nsv4bx5ks-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3904	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:34:25.368+02-72pkw083wkiyhgigeuqy-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-2qxsh7huq11j3vm89v2h-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-hjt110jit9c6m6myux8e-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:29:27.244+02-hjt110jit9c6m6myux8e-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:29:27.244+02-hjt110jit9c6m6myux8e-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:34:25.368+02-smdq006l3izemxzjtd47-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-4j6h9xj781z17uq4wdh-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:34:25.368+02-smdq006l3izemxzjtd47-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-4j6h9xj781z17uq4wdh-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:34:25.368+02-xzfxr66v4o9ibl4wtj-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-2qlvbuuf1gstmwi621fq-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-19tcwjx39pqm8qjn7awe-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:29:27.244+02-19tcwjx39pqm8qjn7awe-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tr0nsmiuor3kmchvah7h-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:29:27.244+02-4tif6851v1nsv4bx5ks-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3911	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-4tif6851v1nsv4bx5ks-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3924	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-4tif6851v1nsv4bx5ks-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3944	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-s35c5vkrtpj7l7y074rv-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:29:27.244+02-s35c5vkrtpj7l7y074rv-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:29:27.244+02-4tif6851v1nsv4bx5ks-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3960	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:34:25.368+02-xzfxr66v4o9ibl4wtj-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-2qlvbuuf1gstmwi621fq-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-s35c5vkrtpj7l7y074rv-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:29:27.244+02-s35c5vkrtpj7l7y074rv-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:34:25.368+02-mluzfhpopw9mwnorn5ca-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-uu4d0ms9imgd17js9kvh-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:34:25.368+02-mluzfhpopw9mwnorn5ca-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-uu4d0ms9imgd17js9kvh-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:34:25.368+02-krsbc38xh4oez3uesld-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-jjfo5peus2gi1oqflxnb-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-hjt110jit9c6m6myux8e-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:29:27.244+02-hjt110jit9c6m6myux8e-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:29:27.244+02-hjt110jit9c6m6myux8e-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-mc3fry8se7hfwfvamxz-actividad	profesor150610_1105411276592741ocbuboq1e8jun07faond3sdr5gc7012qncf11qy4l2ml3mvmpcpq8ij2rzd554o4hpfjl53kn
ancoragoh-2010-06-21 10:34:25.368+02-krsbc38xh4oez3uesld-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-jjfo5peus2gi1oqflxnb-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:34:25.368+02-ew258xb8yhp6cizys3uy-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-gz5metcrgni2sz9is4k-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:34:25.368+02-ew258xb8yhp6cizys3uy-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-gz5metcrgni2sz9is4k-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:34:25.368+02-ld4qqjfk8k2rggxsk2d2-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-rnjxg0d6i6mgnvl6cekc-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:34:25.368+02-ld4qqjfk8k2rggxsk2d2-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-rnjxg0d6i6mgnvl6cekc-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:34:25.368+02-ego0cl09f32z8fas8cl-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-993bfq5nzu2cg8n0op0-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:34:25.368+02-ego0cl09f32z8fas8cl-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-993bfq5nzu2cg8n0op0-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:29:27.244+02-s35c5vkrtpj7l7y074rv-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:29:27.244+02-s35c5vkrtpj7l7y074rv-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:29:27.244+02-s35c5vkrtpj7l7y074rv-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:29:27.244+02-s35c5vkrtpj7l7y074rv-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-q20nwwqxwram6dz3m8lw-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:29:27.244+02-5xdxo9xcvucyzt0f404j-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:29:27.244+02-5xdxo9xcvucyzt0f404j-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:34:25.368+02-97brz7xcxsym0yqxhr4z-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-xxcg1opaocqvg1pzn90j-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:34:25.368+02-97brz7xcxsym0yqxhr4z-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	480	570	t	ancoragoh-2010-06-21 10:34:25.368+02-xxcg1opaocqvg1pzn90j-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:34:25.368+02-oqh00f63dpfwgniv20q-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-291k2752pq8hkl0if3hu-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:34:25.368+02-oqh00f63dpfwgniv20q-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	930	1020	t	ancoragoh-2010-06-21 10:34:25.368+02-291k2752pq8hkl0if3hu-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:29:27.244+02-5xdxo9xcvucyzt0f404j-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:29:27.244+02-hsgk8kt1lkbgzhncse8x-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-5xdxo9xcvucyzt0f404j-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:29:27.244+02-5xdxo9xcvucyzt0f404j-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:29:27.244+02-5xdxo9xcvucyzt0f404j-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:29:27.244+02-5xdxo9xcvucyzt0f404j-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:29:27.244+02-5xdxo9xcvucyzt0f404j-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-1jq0pded9tercoilu4-actividad	profesor190410_0342481271706168lwsjsk4dx2lekdit9u8uikbay0sbuh374kfmv0pj22nc5vv4ftoo5ppsp9u0fm60xblib2r3s55nqqhw0va5bpmq
ancoragoh-2010-06-21 10:29:27.244+02-hsgk8kt1lkbgzhncse8x-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-hsgk8kt1lkbgzhncse8x-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-hsgk8kt1lkbgzhncse8x-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-4tif6851v1nsv4bx5ks-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3987	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-4tif6851v1nsv4bx5ks-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	4000	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-tpjmgxrnoddq7wl2z54y-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:34:25.368+02-x4opkg5vbn7n5fv7c9xp-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-hufh2wrihcfmjyktjbnr-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:34:25.368+02-x4opkg5vbn7n5fv7c9xp-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-hufh2wrihcfmjyktjbnr-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:29:27.244+02-3unnocydqksojknp6hx-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-3unnocydqksojknp6hx-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-3unnocydqksojknp6hx-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-3unnocydqksojknp6hx-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-3unnocydqksojknp6hx-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-3unnocydqksojknp6hx-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-hsgk8kt1lkbgzhncse8x-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-hsgk8kt1lkbgzhncse8x-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-hsgk8kt1lkbgzhncse8x-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-hsgk8kt1lkbgzhncse8x-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-np177hxllc1k6twgtws6-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:34:25.368+02-fqxj3xh7iavh0k447hu6-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-z0p3fdhkzlurpgietr8s-actividad	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r
ancoragoh-2010-06-21 10:34:25.368+02-fqxj3xh7iavh0k447hu6-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	660	750	t	ancoragoh-2010-06-21 10:34:25.368+02-z0p3fdhkzlurpgietr8s-actividad	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r
ancoragoh-2010-06-21 10:34:25.368+02-3eeb43c35kn00rxl84l1-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-dheqwmhkype1m2nmjghf-actividad	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb
ancoragoh-2010-06-21 10:34:25.368+02-3eeb43c35kn00rxl84l1-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	570	660	t	ancoragoh-2010-06-21 10:34:25.368+02-dheqwmhkype1m2nmjghf-actividad	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb
ancoragoh-2010-06-21 10:29:27.244+02-w900esri270f1mjb2b5n-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r
ancoragoh-2010-06-21 10:29:27.244+02-w900esri270f1mjb2b5n-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r
ancoragoh-2010-06-21 10:29:27.244+02-w900esri270f1mjb2b5n-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r
ancoragoh-2010-06-21 10:29:27.244+02-w900esri270f1mjb2b5n-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r
ancoragoh-2010-06-21 10:29:27.244+02-w900esri270f1mjb2b5n-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r
ancoragoh-2010-06-21 10:29:27.244+02-3unnocydqksojknp6hx-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-3unnocydqksojknp6hx-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-buv2o2ebrs92wdk17fx-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-w900esri270f1mjb2b5n-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r
ancoragoh-2010-06-21 10:34:25.368+02-lp6fjsakxd2916ux8p7v-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3898	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-pi7oqevd0muvj3oqnyyj-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:34:25.368+02-lp6fjsakxd2916ux8p7v-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3904	840	930	t	ancoragoh-2010-06-21 10:34:25.368+02-pi7oqevd0muvj3oqnyyj-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:34:25.368+02-1pb43cxst9vxsca4c4u2-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-8s77vuzj442o0pmbxsa-actividad	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw
ancoragoh-2010-06-21 10:34:25.368+02-1pb43cxst9vxsca4c4u2-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-8s77vuzj442o0pmbxsa-actividad	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw
ancoragoh-2010-06-21 10:34:25.368+02-bwbxr0zr0olprw0qmgu1-restriccion_de_profesor	periodicidad140410_0432221271277142ina5ug74awuz3xb6qs7f89tefcbthbwppxj04f352ntubvprdmxbkg	3897	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-0toqess6ildwgfm5g0x4-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:34:25.368+02-bwbxr0zr0olprw0qmgu1-restriccion_de_profesor	periodicidad140410_0432221271277142qih0pjw6yjeydjr8b5rv6dbnhnaiygoepve6511ta5hdeybfst2ow324g2	3903	750	840	t	ancoragoh-2010-06-21 10:34:25.368+02-0toqess6ildwgfm5g0x4-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-bmcyw9xfy4sborhzr7i-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw
ancoragoh-2010-06-21 10:29:27.244+02-bmcyw9xfy4sborhzr7i-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw
ancoragoh-2010-06-21 10:29:27.244+02-bmcyw9xfy4sborhzr7i-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw
ancoragoh-2010-06-21 10:29:27.244+02-w900esri270f1mjb2b5n-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r
ancoragoh-2010-06-21 10:29:27.244+02-w900esri270f1mjb2b5n-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	750	840	t	ancoragoh-2010-06-21 10:29:27.244+02-c3xv4m2o8zvixp2phozu-actividad	profesor150610_1103471276592627ndcxqfp74yfyph7n27742k7l8sd2hfhrckqlssvw092hqg2qnj2g15r
ancoragoh-2010-06-21 10:29:27.244+02-bmcyw9xfy4sborhzr7i-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw
ancoragoh-2010-06-21 10:29:27.244+02-bmcyw9xfy4sborhzr7i-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw
ancoragoh-2010-06-21 10:29:27.244+02-bmcyw9xfy4sborhzr7i-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw
ancoragoh-2010-06-21 10:29:27.244+02-bmcyw9xfy4sborhzr7i-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw
ancoragoh-2010-06-21 10:29:27.244+02-ddifk3ix3efbqglioenp-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3898	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-ddifk3ix3efbqglioenp-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3904	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-ddifk3ix3efbqglioenp-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3911	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-ddifk3ix3efbqglioenp-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3924	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-ddifk3ix3efbqglioenp-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3944	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-ddifk3ix3efbqglioenp-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3960	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-bmcyw9xfy4sborhzr7i-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	660	750	t	ancoragoh-2010-06-21 10:29:27.244+02-ddxdgmqweveio6d26it4-actividad	profesor190410_0342381271706158q5cc842pst90wh92au695f3h1hudeeku0nw8gony8lyutwluhhsbnlies4iw
ancoragoh-2010-06-21 10:29:27.244+02-ddifk3ix3efbqglioenp-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3987	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-ddifk3ix3efbqglioenp-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	4000	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-sd5ij70br609ujrrgd7-actividad	profesor190410_03433112717062118kb08lp4flkoux9va4v2f0nx5b07dcybmzakbpdh1owlbu7cor5tqhhkjghmj6nwvn7x4a5uoq6qb2spjm0zuha4oh
ancoragoh-2010-06-21 10:29:27.244+02-ijwsqjk7jlfpffkagub-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-ijwsqjk7jlfpffkagub-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-ijwsqjk7jlfpffkagub-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-ijwsqjk7jlfpffkagub-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-ijwsqjk7jlfpffkagub-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-ijwsqjk7jlfpffkagub-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-ijwsqjk7jlfpffkagub-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-ijwsqjk7jlfpffkagub-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-c8r76xgjtctcm3lwt3e6-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-x00b8rnolrdi1frw6bot-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-x00b8rnolrdi1frw6bot-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-x00b8rnolrdi1frw6bot-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-x00b8rnolrdi1frw6bot-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-x00b8rnolrdi1frw6bot-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-x00b8rnolrdi1frw6bot-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-x00b8rnolrdi1frw6bot-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-x00b8rnolrdi1frw6bot-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-xi8p7sge938eg4jpts24-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-yhm4k08t4xkot6np6rs-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3898	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-yhm4k08t4xkot6np6rs-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3904	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-yhm4k08t4xkot6np6rs-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3911	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-yhm4k08t4xkot6np6rs-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3924	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-yhm4k08t4xkot6np6rs-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3944	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-yhm4k08t4xkot6np6rs-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3960	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-yhm4k08t4xkot6np6rs-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3987	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-yhm4k08t4xkot6np6rs-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	4000	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-9tjonmeotp56oz9byzm-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-38wsvrx5e5z84k8x26i-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-38wsvrx5e5z84k8x26i-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-38wsvrx5e5z84k8x26i-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-38wsvrx5e5z84k8x26i-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-38wsvrx5e5z84k8x26i-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-38wsvrx5e5z84k8x26i-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-38wsvrx5e5z84k8x26i-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-38wsvrx5e5z84k8x26i-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-jpuyxgi96nvvajj3o0j-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-o9imgu9g82sxvpuwtpdv-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-o9imgu9g82sxvpuwtpdv-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-o9imgu9g82sxvpuwtpdv-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-o9imgu9g82sxvpuwtpdv-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-o9imgu9g82sxvpuwtpdv-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-o9imgu9g82sxvpuwtpdv-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-o9imgu9g82sxvpuwtpdv-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-o9imgu9g82sxvpuwtpdv-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-soq4kkyk1v2bhfnf965-actividad	profesor190410_0342281271706148gy9q8gcadbd36dp9ca1k1tbuvaeyf7clua2sg5sk6vc1zsz3sqcj0e4ve8kj4mvonm8u
ancoragoh-2010-06-21 10:29:27.244+02-w9b81400mj0pqg9bowkc-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb
ancoragoh-2010-06-21 10:29:27.244+02-w9b81400mj0pqg9bowkc-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb
ancoragoh-2010-06-21 10:29:27.244+02-w9b81400mj0pqg9bowkc-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb
ancoragoh-2010-06-21 10:29:27.244+02-w9b81400mj0pqg9bowkc-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb
ancoragoh-2010-06-21 10:29:27.244+02-w9b81400mj0pqg9bowkc-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb
ancoragoh-2010-06-21 10:29:27.244+02-w9b81400mj0pqg9bowkc-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb
ancoragoh-2010-06-21 10:29:27.244+02-w9b81400mj0pqg9bowkc-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb
ancoragoh-2010-06-21 10:29:27.244+02-w9b81400mj0pqg9bowkc-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	570	660	t	ancoragoh-2010-06-21 10:29:27.244+02-4tsyng3ogcmmr8x818k-actividad	profesor250410_11113812722514990gg21swp2qvj79w8sr6yoa7i9a43aphc62flp0jiatxbqo6qdb
ancoragoh-2010-06-21 10:29:27.244+02-y5jk4d7kty90leqe0gg-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:29:27.244+02-y5jk4d7kty90leqe0gg-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:29:27.244+02-y5jk4d7kty90leqe0gg-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:29:27.244+02-y5jk4d7kty90leqe0gg-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:29:27.244+02-y5jk4d7kty90leqe0gg-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:29:27.244+02-y5jk4d7kty90leqe0gg-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:29:27.244+02-y5jk4d7kty90leqe0gg-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:29:27.244+02-y5jk4d7kty90leqe0gg-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-8ctq4o4xhrk8jizbhzs-actividad	profesor150610_1105121276592712jtndc5cwqkvv57i6jkx3evdhpmjqxyno1gfmf75g6segobijxe71e5lq32h2ea4rcg50dmrdgesf4udb8l0ca
ancoragoh-2010-06-21 10:29:27.244+02-gfdml61xh62lb7einno3-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-gfdml61xh62lb7einno3-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-gfdml61xh62lb7einno3-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-gfdml61xh62lb7einno3-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-gfdml61xh62lb7einno3-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-gfdml61xh62lb7einno3-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-gfdml61xh62lb7einno3-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-gfdml61xh62lb7einno3-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	480	570	t	ancoragoh-2010-06-21 10:29:27.244+02-mnq5uh90bmw44pxmrph-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-elcwprioido1u9g2u88u-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-elcwprioido1u9g2u88u-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-elcwprioido1u9g2u88u-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-elcwprioido1u9g2u88u-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-elcwprioido1u9g2u88u-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-elcwprioido1u9g2u88u-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-elcwprioido1u9g2u88u-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-elcwprioido1u9g2u88u-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	840	930	t	ancoragoh-2010-06-21 10:29:27.244+02-b1xhhms83gnt52grjzxs-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-mi368ghaud7aljo2qtby-restriccion_de_profesor	periodicidad170610_0507171276787237nlylcaihl3xuv3fh6uqswi2l3n486c4i58qufca21bgfe9fvjozqwr0tg32hmoilg9fgdryybxuet	3897	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-mi368ghaud7aljo2qtby-restriccion_de_profesor	periodicidad170610_0507171276787237bntv32sdcfouv6zet2nmyg7kh0iw5q5n3syg3qjdbfs1k78h1vqdduvrwxk00gaxobzexs7q7s	3903	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-mi368ghaud7aljo2qtby-restriccion_de_profesor	periodicidad170610_0507171276787237k6lby34piolgb7zhz0kbk78wpn9ohvinhldas1oj1uqw0bea4629lkhnf033mhmerxj61tm0ww98	3910	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-mi368ghaud7aljo2qtby-restriccion_de_profesor	periodicidad170610_0507171276787237c8zkxv11t5bhq0b4y51g53vliyu7t4e90djfk4osx5ux4tgzazih8zgk3natmdha	3923	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-mi368ghaud7aljo2qtby-restriccion_de_profesor	periodicidad170610_0507171276787237xyn3p8xxj9rcrg8i8vw60xwiqmtazfqpu00gomgtaiyg3cult5pzxamt8u8tz9j96f9q1xdkfnjfr4ukt9voyip1	3943	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-mi368ghaud7aljo2qtby-restriccion_de_profesor	periodicidad170610_0507171276787237xy0dxqmthlvq4n7yohatq8e79e0dm739v4j5hawvfbvu5aoukt109y5x4bp6l11tqlp0x83a9frb2b25cim3m3q2	3959	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-mi368ghaud7aljo2qtby-restriccion_de_profesor	periodicidad170610_0507171276787237m2bg9trfhrr0i44wwj6tparzk50vk7iga9u7ti41romg81agr7dhhfsi1hu1fjdxbbrrr3thehvi0s	3986	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
ancoragoh-2010-06-21 10:29:27.244+02-mi368ghaud7aljo2qtby-restriccion_de_profesor	periodicidad170610_1006151276805175hxj6m5urls7korj6idcmehkk1fq4k2qrso4pw7shmv986nfh0tud58ghg8pxhmkaysjm	3999	930	1020	t	ancoragoh-2010-06-21 10:29:27.244+02-plkc3ikfz325gw6j4g6h-actividad	profesor190410_0343391271706219ydtkz7hmzhgjwxecy7vef9e6sl6lzdrngay677iweo71cb428o7dma95l5
\.


--
-- TOC entry 2758 (class 0 OID 26821)
-- Dependencies: 1986
-- Data for Name: zpriori; Type: TABLE DATA; Schema: public; Owner: rafa
--

COPY zpriori (clasificacion, periodo, dia, turno, valor) FROM stdin;
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	4	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	5	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	6	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	2	4	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	2	5	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	2	6	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	3	4	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	3	5	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	3	6	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	4	4	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	4	5	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	4	6	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	5	4	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	5	5	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	5	6	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	4	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	5	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	6	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	4	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	5	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	6	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	3	4	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	3	5	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	3	6	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	4	4	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	4	5	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	4	6	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	5	4	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	5	5	2
clasificacion_de_actividad120410_1114531271085293mcx8eqs1kik9j42sbrn2dgl7hwy8mtlzv9wzpfq1n1yxuqfv8snbzy97lw4xffm2d0rte	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	5	6	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	1	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	2	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	1	3	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	2	1	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	2	2	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	2	3	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	3	1	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	3	2	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	3	3	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	4	1	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	4	2	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	4	3	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	5	1	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	5	2	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_0456221270932983dol6z8uwvqvffqs1h66ach5jwp9gh9l0x4tnkqzw05oodq18pil9mygzz29	5	3	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	1	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	2	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	1	3	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	1	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	2	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	2	3	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	3	1	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	3	2	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	3	3	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	4	1	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	4	2	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	4	3	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	5	1	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	5	2	2
clasificacion_de_actividad170610_0428491276784929bhqn1vff5hryg0czdp8qqszvuxsnl67saw0is6njfde33i5t23x27zdf	periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q	5	3	2
\.


SET search_path = seguridad, pg_catalog;

--
-- TOC entry 2759 (class 0 OID 26829)
-- Dependencies: 1987
-- Data for Name: host; Type: TABLE DATA; Schema: seguridad; Owner: rafa
--

COPY host (ip, usuario) FROM stdin;
\.


--
-- TOC entry 2760 (class 0 OID 26836)
-- Dependencies: 1988
-- Data for Name: permiso; Type: TABLE DATA; Schema: seguridad; Owner: rafa
--

COPY permiso (permiso, rol) FROM stdin;
todos	superusuario
\.


--
-- TOC entry 2761 (class 0 OID 26842)
-- Dependencies: 1989
-- Data for Name: rol; Type: TABLE DATA; Schema: seguridad; Owner: rafa
--

COPY rol (nombre, descripcion) FROM stdin;
superusuario	Usuario con todos los privilegios
administrador	Administrador
planificador	Planificador
profesor	Profesor
invitado	Invitado
\.


--
-- TOC entry 2762 (class 0 OID 26848)
-- Dependencies: 1990
-- Data for Name: rol_de_usuario; Type: TABLE DATA; Schema: seguridad; Owner: rafa
--

COPY rol_de_usuario (usuario, rol) FROM stdin;
admin-superuser	superusuario
\.


--
-- TOC entry 2763 (class 0 OID 26854)
-- Dependencies: 1991
-- Data for Name: traza; Type: TABLE DATA; Schema: seguridad; Owner: rafa
--

COPY traza (fecha, usuario, accion, ip) FROM stdin;
\.


--
-- TOC entry 2764 (class 0 OID 26861)
-- Dependencies: 1992
-- Data for Name: usuario; Type: TABLE DATA; Schema: seguridad; Owner: rafa
--

COPY usuario (id, nombre, info, correo, momento_ingreso, nick, contrasena) FROM stdin;
admin-superuser	admin	administrador superusuario	admin@example.com	3230197.0099999998	admin	b91cd1a54781790beaa2baf741fa6789
\.


SET search_path = generador, pg_catalog;

--
-- TOC entry 2553 (class 2606 OID 36218)
-- Dependencies: 1993 1993 1993 1993
-- Name: cancelar_pkey; Type: CONSTRAINT; Schema: generador; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cancelar
    ADD CONSTRAINT cancelar_pkey PRIMARY KEY (especialidad, nivel, periodo);


SET search_path = public, pg_catalog;

--
-- TOC entry 2419 (class 2606 OID 26870)
-- Dependencies: 1937 1937 1937
-- Name: acceso_prohibido_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY acceso_prohibido
    ADD CONSTRAINT acceso_prohibido_pkey PRIMARY KEY (grupo, lugar);


--
-- TOC entry 2411 (class 2606 OID 26872)
-- Dependencies: 1934 1934
-- Name: actividad_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY actividad
    ADD CONSTRAINT actividad_pkey PRIMARY KEY (id);


--
-- TOC entry 2413 (class 2606 OID 26874)
-- Dependencies: 1934 1934 1934
-- Name: actividad_plantilla_key; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY actividad
    ADD CONSTRAINT actividad_plantilla_key UNIQUE (plantilla, grupo);


--
-- TOC entry 2421 (class 2606 OID 26876)
-- Dependencies: 1938 1938
-- Name: asignatura_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY asignatura
    ADD CONSTRAINT asignatura_pkey PRIMARY KEY (id);


--
-- TOC entry 2427 (class 2606 OID 26878)
-- Dependencies: 1941 1941 1941
-- Name: brigada_de_grupo_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY brigada_de_grupo
    ADD CONSTRAINT brigada_de_grupo_pkey PRIMARY KEY (brigada, grupo);


--
-- TOC entry 2405 (class 2606 OID 26880)
-- Dependencies: 1931 1931
-- Name: brigada_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY brigada
    ADD CONSTRAINT brigada_pkey PRIMARY KEY (id);


--
-- TOC entry 2429 (class 2606 OID 26882)
-- Dependencies: 1942 1942
-- Name: clasificacion_de_actividad_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY clasificacion_de_actividad
    ADD CONSTRAINT clasificacion_de_actividad_pkey PRIMARY KEY (id);


--
-- TOC entry 2431 (class 2606 OID 26884)
-- Dependencies: 1943 1943 1943
-- Name: costo_de_traslado_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY costo_de_traslado
    ADD CONSTRAINT costo_de_traslado_pkey PRIMARY KEY (origen, destino);


--
-- TOC entry 2423 (class 2606 OID 26886)
-- Dependencies: 1939 1939 1939
-- Name: desglose_asignatura_key; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY desglose
    ADD CONSTRAINT desglose_asignatura_key UNIQUE (asignatura, periodo);


--
-- TOC entry 2425 (class 2606 OID 26888)
-- Dependencies: 1939 1939
-- Name: desglose_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY desglose
    ADD CONSTRAINT desglose_pkey PRIMARY KEY (id);


--
-- TOC entry 2433 (class 2606 OID 26890)
-- Dependencies: 1944 1944
-- Name: especialidad_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY especialidad
    ADD CONSTRAINT especialidad_pkey PRIMARY KEY (id);


--
-- TOC entry 2435 (class 2606 OID 26892)
-- Dependencies: 1945 1945 1945 1945 1945
-- Name: excepcion_de_hrt_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY excepcion_de_hrt
    ADD CONSTRAINT excepcion_de_hrt_pkey PRIMARY KEY (herencia, periodo, dia, turno);


--
-- TOC entry 2407 (class 2606 OID 26894)
-- Dependencies: 1932 1932
-- Name: grupo_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY grupo
    ADD CONSTRAINT grupo_pkey PRIMARY KEY (id);


--
-- TOC entry 2437 (class 2606 OID 26896)
-- Dependencies: 1946 1946 1946 1946 1946 1946 1946 1946 1946
-- Name: herencia_de_restricciones_hijo_brigada_key; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY herencia_de_restricciones
    ADD CONSTRAINT herencia_de_restricciones_hijo_brigada_key UNIQUE (hijo_brigada, hijo_profesor, hijo_lugar, hijo_otro_recurso, padre_brigada, padre_profesor, padre_lugar, padre_otro_recurso);


--
-- TOC entry 2439 (class 2606 OID 26898)
-- Dependencies: 1946 1946
-- Name: herencia_de_restricciones_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY herencia_de_restricciones
    ADD CONSTRAINT herencia_de_restricciones_pkey PRIMARY KEY (id);


--
-- TOC entry 2441 (class 2606 OID 26900)
-- Dependencies: 1947 1947 1947 1947
-- Name: higiene_para_brigada_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY higiene_para_brigada
    ADD CONSTRAINT higiene_para_brigada_pkey PRIMARY KEY (periodo, brigada, gasto_maximo_diario);


--
-- TOC entry 2443 (class 2606 OID 26902)
-- Dependencies: 1948 1948 1948 1948
-- Name: higiene_para_profesor_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY higiene_para_profesor
    ADD CONSTRAINT higiene_para_profesor_pkey PRIMARY KEY (periodo, profesor, gasto_maximo_diario);


--
-- TOC entry 2445 (class 2606 OID 26904)
-- Dependencies: 1949 1949 1949
-- Name: lugar_fijo_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY lugar_fijo
    ADD CONSTRAINT lugar_fijo_pkey PRIMARY KEY (grupo, lugar);


--
-- TOC entry 2447 (class 2606 OID 26906)
-- Dependencies: 1950 1950 1950
-- Name: lugar_para_actividad_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY lugar_para_actividad
    ADD CONSTRAINT lugar_para_actividad_pkey PRIMARY KEY (plantilla, lugar);


--
-- TOC entry 2409 (class 2606 OID 26908)
-- Dependencies: 1933 1933
-- Name: lugar_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY lugar
    ADD CONSTRAINT lugar_pkey PRIMARY KEY (id);


--
-- TOC entry 2449 (class 2606 OID 26910)
-- Dependencies: 1951 1951
-- Name: nivel_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY nivel
    ADD CONSTRAINT nivel_pkey PRIMARY KEY (id);


--
-- TOC entry 2451 (class 2606 OID 26912)
-- Dependencies: 1952 1952
-- Name: otro_grupo_de_recursos_para_actividad_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY otro_grupo_de_recursos_para_actividad
    ADD CONSTRAINT otro_grupo_de_recursos_para_actividad_pkey PRIMARY KEY (id);


--
-- TOC entry 2455 (class 2606 OID 26914)
-- Dependencies: 1954 1954 1954
-- Name: otro_recurso_para_actividad_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY otro_recurso_para_actividad
    ADD CONSTRAINT otro_recurso_para_actividad_pkey PRIMARY KEY (grupo_de_recursos, otro_recurso);


--
-- TOC entry 2453 (class 2606 OID 26916)
-- Dependencies: 1953 1953
-- Name: otro_recurso_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY otro_recurso
    ADD CONSTRAINT otro_recurso_pkey PRIMARY KEY (id);


--
-- TOC entry 2457 (class 2606 OID 26918)
-- Dependencies: 1955 1955 1955
-- Name: otros_recursos_usados_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY otros_recursos_usados
    ADD CONSTRAINT otros_recursos_usados_pkey PRIMARY KEY (otro_recurso, actividad);


--
-- TOC entry 2459 (class 2606 OID 26920)
-- Dependencies: 1956 1956 1956
-- Name: periodicidad_periodo_key; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY periodicidad
    ADD CONSTRAINT periodicidad_periodo_key UNIQUE (periodo, dia);


--
-- TOC entry 2461 (class 2606 OID 26922)
-- Dependencies: 1956 1956
-- Name: periodicidad_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY periodicidad
    ADD CONSTRAINT periodicidad_pkey PRIMARY KEY (id);


--
-- TOC entry 2417 (class 2606 OID 26924)
-- Dependencies: 1936 1936
-- Name: periodo_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY periodo
    ADD CONSTRAINT periodo_pkey PRIMARY KEY (id);


--
-- TOC entry 2463 (class 2606 OID 26926)
-- Dependencies: 1957 1957
-- Name: planificacion_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY planificacion
    ADD CONSTRAINT planificacion_pkey PRIMARY KEY (id);


--
-- TOC entry 2465 (class 2606 OID 26928)
-- Dependencies: 1958 1958 1958
-- Name: plantilla_de_actividad_desglose_key; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY plantilla_de_actividad
    ADD CONSTRAINT plantilla_de_actividad_desglose_key UNIQUE (desglose, orden);


--
-- TOC entry 2467 (class 2606 OID 26930)
-- Dependencies: 1958 1958
-- Name: plantilla_de_actividad_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY plantilla_de_actividad
    ADD CONSTRAINT plantilla_de_actividad_pkey PRIMARY KEY (id);


--
-- TOC entry 2471 (class 2606 OID 26932)
-- Dependencies: 1960 1960
-- Name: profesor_para_actividad_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY profesor_para_actividad
    ADD CONSTRAINT profesor_para_actividad_pkey PRIMARY KEY (id);


--
-- TOC entry 2473 (class 2606 OID 26934)
-- Dependencies: 1960 1960 1960
-- Name: profesor_para_actividad_plantilla_key; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY profesor_para_actividad
    ADD CONSTRAINT profesor_para_actividad_plantilla_key UNIQUE (plantilla, profesor);


--
-- TOC entry 2475 (class 2606 OID 26936)
-- Dependencies: 1961 1961 1961
-- Name: profesor_para_grupo_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY profesor_para_grupo
    ADD CONSTRAINT profesor_para_grupo_pkey PRIMARY KEY (profesor_para_actividad, grupo);


--
-- TOC entry 2469 (class 2606 OID 26938)
-- Dependencies: 1959 1959
-- Name: profesor_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY profesor
    ADD CONSTRAINT profesor_pkey PRIMARY KEY (id);


--
-- TOC entry 2477 (class 2606 OID 26940)
-- Dependencies: 1962 1962 1962
-- Name: receso_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY receso
    ADD CONSTRAINT receso_pkey PRIMARY KEY (periodo, despues_del_turno);


--
-- TOC entry 2415 (class 2606 OID 26942)
-- Dependencies: 1935 1935
-- Name: restriccion_condicional_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_pkey PRIMARY KEY (id);


--
-- TOC entry 2479 (class 2606 OID 26944)
-- Dependencies: 1963 1963 1963 1963 1963
-- Name: restriccion_de_asignatura_asignatura_key; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_asignatura
    ADD CONSTRAINT restriccion_de_asignatura_asignatura_key UNIQUE (asignatura, periodo, dia, turno);


--
-- TOC entry 2481 (class 2606 OID 26946)
-- Dependencies: 1963 1963
-- Name: restriccion_de_asignatura_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_asignatura
    ADD CONSTRAINT restriccion_de_asignatura_pkey PRIMARY KEY (id);


--
-- TOC entry 2483 (class 2606 OID 26948)
-- Dependencies: 1964 1964 1964 1964 1964
-- Name: restriccion_de_brigada_brigada_key; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_brigada
    ADD CONSTRAINT restriccion_de_brigada_brigada_key UNIQUE (brigada, periodo, dia, turno);


--
-- TOC entry 2485 (class 2606 OID 26950)
-- Dependencies: 1964 1964
-- Name: restriccion_de_brigada_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_brigada
    ADD CONSTRAINT restriccion_de_brigada_pkey PRIMARY KEY (id);


--
-- TOC entry 2487 (class 2606 OID 26952)
-- Dependencies: 1965 1965 1965 1965 1965
-- Name: restriccion_de_clasif_act_clasificacion_key; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_clasif_act
    ADD CONSTRAINT restriccion_de_clasif_act_clasificacion_key UNIQUE (clasificacion, periodo, dia, turno);


--
-- TOC entry 2489 (class 2606 OID 26954)
-- Dependencies: 1965 1965
-- Name: restriccion_de_clasif_act_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_clasif_act
    ADD CONSTRAINT restriccion_de_clasif_act_pkey PRIMARY KEY (id);


--
-- TOC entry 2491 (class 2606 OID 26956)
-- Dependencies: 1966 1966 1966 1966 1966
-- Name: restriccion_de_especialidad_especialidad_key; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_especialidad
    ADD CONSTRAINT restriccion_de_especialidad_especialidad_key UNIQUE (especialidad, periodo, dia, turno);


--
-- TOC entry 2493 (class 2606 OID 26958)
-- Dependencies: 1966 1966
-- Name: restriccion_de_especialidad_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_especialidad
    ADD CONSTRAINT restriccion_de_especialidad_pkey PRIMARY KEY (id);


--
-- TOC entry 2495 (class 2606 OID 26960)
-- Dependencies: 1967 1967 1967 1967 1967
-- Name: restriccion_de_lugar_lugar_key; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_lugar
    ADD CONSTRAINT restriccion_de_lugar_lugar_key UNIQUE (lugar, periodo, dia, turno);


--
-- TOC entry 2497 (class 2606 OID 26962)
-- Dependencies: 1967 1967
-- Name: restriccion_de_lugar_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_lugar
    ADD CONSTRAINT restriccion_de_lugar_pkey PRIMARY KEY (id);


--
-- TOC entry 2499 (class 2606 OID 26964)
-- Dependencies: 1968 1968 1968 1968 1968
-- Name: restriccion_de_otro_recurso_otro_recurso_key; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_otro_recurso
    ADD CONSTRAINT restriccion_de_otro_recurso_otro_recurso_key UNIQUE (otro_recurso, periodo, dia, turno);


--
-- TOC entry 2501 (class 2606 OID 26966)
-- Dependencies: 1968 1968
-- Name: restriccion_de_otro_recurso_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_otro_recurso
    ADD CONSTRAINT restriccion_de_otro_recurso_pkey PRIMARY KEY (id);


--
-- TOC entry 2503 (class 2606 OID 26968)
-- Dependencies: 1969 1969 1969 1969
-- Name: restriccion_de_periodo_periodo_key; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_periodo
    ADD CONSTRAINT restriccion_de_periodo_periodo_key UNIQUE (periodo, dia, turno);


--
-- TOC entry 2505 (class 2606 OID 26970)
-- Dependencies: 1969 1969
-- Name: restriccion_de_periodo_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_periodo
    ADD CONSTRAINT restriccion_de_periodo_pkey PRIMARY KEY (id);


--
-- TOC entry 2507 (class 2606 OID 26972)
-- Dependencies: 1970 1970
-- Name: restriccion_de_profesor_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_profesor
    ADD CONSTRAINT restriccion_de_profesor_pkey PRIMARY KEY (id);


--
-- TOC entry 2509 (class 2606 OID 26974)
-- Dependencies: 1970 1970 1970 1970 1970
-- Name: restriccion_de_profesor_profesor_key; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_de_profesor
    ADD CONSTRAINT restriccion_de_profesor_profesor_key UNIQUE (profesor, periodo, dia, turno);


--
-- TOC entry 2513 (class 2606 OID 26976)
-- Dependencies: 1972 1972
-- Name: restriccion_por_actividad_de_brigada_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_por_actividad_de_brigada
    ADD CONSTRAINT restriccion_por_actividad_de_brigada_pkey PRIMARY KEY (restriccion_nominal);


--
-- TOC entry 2515 (class 2606 OID 26978)
-- Dependencies: 1973 1973
-- Name: restriccion_por_actividad_de_lugar_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_por_actividad_de_lugar
    ADD CONSTRAINT restriccion_por_actividad_de_lugar_pkey PRIMARY KEY (restriccion_nominal);


--
-- TOC entry 2517 (class 2606 OID 26980)
-- Dependencies: 1974 1974
-- Name: restriccion_por_actividad_de_otro_recurso_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_por_actividad_de_otro_recurso
    ADD CONSTRAINT restriccion_por_actividad_de_otro_recurso_pkey PRIMARY KEY (restriccion_nominal);


--
-- TOC entry 2519 (class 2606 OID 26982)
-- Dependencies: 1975 1975
-- Name: restriccion_por_actividad_de_profesor_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_por_actividad_de_profesor
    ADD CONSTRAINT restriccion_por_actividad_de_profesor_pkey PRIMARY KEY (restriccion_nominal);


--
-- TOC entry 2511 (class 2606 OID 26984)
-- Dependencies: 1971 1971 1971
-- Name: restriccion_por_actividad_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_por_actividad
    ADD CONSTRAINT restriccion_por_actividad_pkey PRIMARY KEY (restriccion_nominal, actividad);


--
-- TOC entry 2523 (class 2606 OID 26986)
-- Dependencies: 1977 1977 1977
-- Name: restriccion_real_de_asignatura_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_real_de_asignatura
    ADD CONSTRAINT restriccion_real_de_asignatura_pkey PRIMARY KEY (restriccion_nominal, periodicidad);


--
-- TOC entry 2525 (class 2606 OID 26988)
-- Dependencies: 1978 1978 1978
-- Name: restriccion_real_de_brigada_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_real_de_brigada
    ADD CONSTRAINT restriccion_real_de_brigada_pkey PRIMARY KEY (restriccion_nominal, periodicidad);


--
-- TOC entry 2527 (class 2606 OID 26990)
-- Dependencies: 1979 1979 1979
-- Name: restriccion_real_de_clasif_act_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_real_de_clasif_act
    ADD CONSTRAINT restriccion_real_de_clasif_act_pkey PRIMARY KEY (restriccion_nominal, periodicidad);


--
-- TOC entry 2529 (class 2606 OID 26992)
-- Dependencies: 1980 1980 1980
-- Name: restriccion_real_de_especialidad_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_real_de_especialidad
    ADD CONSTRAINT restriccion_real_de_especialidad_pkey PRIMARY KEY (restriccion_nominal, periodicidad);


--
-- TOC entry 2531 (class 2606 OID 26994)
-- Dependencies: 1981 1981 1981
-- Name: restriccion_real_de_lugar_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_real_de_lugar
    ADD CONSTRAINT restriccion_real_de_lugar_pkey PRIMARY KEY (restriccion_nominal, periodicidad);


--
-- TOC entry 2533 (class 2606 OID 26996)
-- Dependencies: 1982 1982 1982
-- Name: restriccion_real_de_otro_recurso_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_real_de_otro_recurso
    ADD CONSTRAINT restriccion_real_de_otro_recurso_pkey PRIMARY KEY (restriccion_nominal, periodicidad);


--
-- TOC entry 2535 (class 2606 OID 26998)
-- Dependencies: 1983 1983 1983
-- Name: restriccion_real_de_periodo_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_real_de_periodo
    ADD CONSTRAINT restriccion_real_de_periodo_pkey PRIMARY KEY (restriccion_nominal, periodicidad);


--
-- TOC entry 2537 (class 2606 OID 27000)
-- Dependencies: 1984 1984 1984
-- Name: restriccion_real_de_profesor_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_real_de_profesor
    ADD CONSTRAINT restriccion_real_de_profesor_pkey PRIMARY KEY (restriccion_nominal, periodicidad);


--
-- TOC entry 2521 (class 2606 OID 27002)
-- Dependencies: 1976 1976 1976
-- Name: restriccion_real_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY restriccion_real
    ADD CONSTRAINT restriccion_real_pkey PRIMARY KEY (restriccion_nominal, periodicidad);


--
-- TOC entry 2539 (class 2606 OID 27004)
-- Dependencies: 1986 1986 1986 1986 1986
-- Name: zpriori_pkey; Type: CONSTRAINT; Schema: public; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY zpriori
    ADD CONSTRAINT zpriori_pkey PRIMARY KEY (clasificacion, periodo, dia, turno);


SET search_path = seguridad, pg_catalog;

--
-- TOC entry 2541 (class 2606 OID 27006)
-- Dependencies: 1987 1987 1987
-- Name: host_pkey; Type: CONSTRAINT; Schema: seguridad; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY host
    ADD CONSTRAINT host_pkey PRIMARY KEY (ip, usuario);


--
-- TOC entry 2543 (class 2606 OID 27008)
-- Dependencies: 1988 1988 1988
-- Name: permiso_pkey; Type: CONSTRAINT; Schema: seguridad; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY permiso
    ADD CONSTRAINT permiso_pkey PRIMARY KEY (permiso, rol);


--
-- TOC entry 2547 (class 2606 OID 27010)
-- Dependencies: 1990 1990 1990
-- Name: rol_de_usuario_pkey; Type: CONSTRAINT; Schema: seguridad; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY rol_de_usuario
    ADD CONSTRAINT rol_de_usuario_pkey PRIMARY KEY (usuario, rol);


--
-- TOC entry 2545 (class 2606 OID 27012)
-- Dependencies: 1989 1989
-- Name: rol_pkey; Type: CONSTRAINT; Schema: seguridad; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY rol
    ADD CONSTRAINT rol_pkey PRIMARY KEY (nombre);


--
-- TOC entry 2549 (class 2606 OID 27014)
-- Dependencies: 1992 1992
-- Name: usuario_nick_key; Type: CONSTRAINT; Schema: seguridad; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY usuario
    ADD CONSTRAINT usuario_nick_key UNIQUE (nick);


--
-- TOC entry 2551 (class 2606 OID 27016)
-- Dependencies: 1992 1992
-- Name: usuario_pkey; Type: CONSTRAINT; Schema: seguridad; Owner: rafa; Tablespace: 
--

ALTER TABLE ONLY usuario
    ADD CONSTRAINT usuario_pkey PRIMARY KEY (id);


SET search_path = public, pg_catalog;

--
-- TOC entry 2082 (class 2618 OID 27017)
-- Dependencies: 1972 1964 1972 1972
-- Name: actualiza_por_actividad_en_nueva_restriccion_de_brigada; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_por_actividad_en_nueva_restriccion_de_brigada AS ON INSERT TO restriccion_por_actividad_de_brigada DO UPDATE restriccion_de_brigada SET por_actividad = true WHERE ((restriccion_de_brigada.id)::text = (new.restriccion_nominal)::text);


--
-- TOC entry 2083 (class 2618 OID 27018)
-- Dependencies: 1973 1973 1967 1973
-- Name: actualiza_por_actividad_en_nueva_restriccion_de_lugar; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_por_actividad_en_nueva_restriccion_de_lugar AS ON INSERT TO restriccion_por_actividad_de_lugar DO UPDATE restriccion_de_lugar SET por_actividad = true WHERE ((restriccion_de_lugar.id)::text = (new.restriccion_nominal)::text);


--
-- TOC entry 2084 (class 2618 OID 27019)
-- Dependencies: 1974 1974 1968 1974
-- Name: actualiza_por_actividad_en_nueva_restriccion_de_otro_recurso; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_por_actividad_en_nueva_restriccion_de_otro_recurso AS ON INSERT TO restriccion_por_actividad_de_otro_recurso DO UPDATE restriccion_de_otro_recurso SET por_actividad = true WHERE ((restriccion_de_otro_recurso.id)::text = (new.restriccion_nominal)::text);


--
-- TOC entry 2085 (class 2618 OID 27020)
-- Dependencies: 1975 1975 1975 1970
-- Name: actualiza_por_actividad_en_nueva_restriccion_de_profesor; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_por_actividad_en_nueva_restriccion_de_profesor AS ON INSERT TO restriccion_por_actividad_de_profesor DO UPDATE restriccion_de_profesor SET por_actividad = true WHERE ((restriccion_de_profesor.id)::text = (new.restriccion_nominal)::text);


--
-- TOC entry 2086 (class 2618 OID 27021)
-- Dependencies: 1972 1972 1972 1964
-- Name: actualiza_por_actividad_en_restriccion_de_brigada; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_por_actividad_en_restriccion_de_brigada AS ON UPDATE TO restriccion_por_actividad_de_brigada DO (UPDATE restriccion_de_brigada SET por_actividad = false WHERE ((restriccion_de_brigada.id)::text = (old.restriccion_nominal)::text); UPDATE restriccion_de_brigada SET por_actividad = true WHERE ((restriccion_de_brigada.id)::text = (new.restriccion_nominal)::text); );


--
-- TOC entry 2087 (class 2618 OID 27022)
-- Dependencies: 1973 1973 1973 1967
-- Name: actualiza_por_actividad_en_restriccion_de_lugar; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_por_actividad_en_restriccion_de_lugar AS ON UPDATE TO restriccion_por_actividad_de_lugar DO (UPDATE restriccion_de_lugar SET por_actividad = false WHERE ((restriccion_de_lugar.id)::text = (old.restriccion_nominal)::text); UPDATE restriccion_de_lugar SET por_actividad = true WHERE ((restriccion_de_lugar.id)::text = (new.restriccion_nominal)::text); );


--
-- TOC entry 2088 (class 2618 OID 27023)
-- Dependencies: 1974 1968 1974 1974
-- Name: actualiza_por_actividad_en_restriccion_de_otro_recurso; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_por_actividad_en_restriccion_de_otro_recurso AS ON UPDATE TO restriccion_por_actividad_de_otro_recurso DO (UPDATE restriccion_de_otro_recurso SET por_actividad = false WHERE ((restriccion_de_otro_recurso.id)::text = (old.restriccion_nominal)::text); UPDATE restriccion_de_otro_recurso SET por_actividad = true WHERE ((restriccion_de_otro_recurso.id)::text = (new.restriccion_nominal)::text); );


--
-- TOC entry 2089 (class 2618 OID 27024)
-- Dependencies: 1975 1970 1975 1975
-- Name: actualiza_por_actividad_en_restriccion_de_profesor; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_por_actividad_en_restriccion_de_profesor AS ON UPDATE TO restriccion_por_actividad_de_profesor DO (UPDATE restriccion_de_profesor SET por_actividad = false WHERE ((restriccion_de_profesor.id)::text = (old.restriccion_nominal)::text); UPDATE restriccion_de_profesor SET por_actividad = true WHERE ((restriccion_de_profesor.id)::text = (new.restriccion_nominal)::text); );


--
-- TOC entry 2090 (class 2618 OID 27025)
-- Dependencies: 1963 1963 1963 1963 222 1963 1963 1963
-- Name: actualiza_restriccion_real_de_asignatura; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_restriccion_real_de_asignatura AS ON UPDATE TO restriccion_de_asignatura DO SELECT actualiza_restriccion_real(new.asignatura, NULL::character varying, new.id, new.periodo, new.dia, new.turno, false, 'restriccion_real_de_asignatura'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2091 (class 2618 OID 27026)
-- Dependencies: 1964 1964 1964 222 1964 1964 1964 1964 1964 1964
-- Name: actualiza_restriccion_real_de_brigada; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_restriccion_real_de_brigada AS ON UPDATE TO restriccion_de_brigada DO SELECT actualiza_restriccion_real(new.brigada, new.actividad, new.id, new.periodo, new.dia, new.turno, new.por_actividad, 'restriccion_real_de_brigada'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2092 (class 2618 OID 27027)
-- Dependencies: 1965 1965 1965 1965 1965 1965 222 1965
-- Name: actualiza_restriccion_real_de_clasif_act; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_restriccion_real_de_clasif_act AS ON UPDATE TO restriccion_de_clasif_act DO SELECT actualiza_restriccion_real(new.clasificacion, NULL::character varying, new.id, new.periodo, new.dia, new.turno, false, 'restriccion_real_de_clasif_act'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2093 (class 2618 OID 27028)
-- Dependencies: 1966 1966 1966 222 1966 1966 1966 1966
-- Name: actualiza_restriccion_real_de_especialidad; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_restriccion_real_de_especialidad AS ON UPDATE TO restriccion_de_especialidad DO SELECT actualiza_restriccion_real(new.especialidad, NULL::character varying, new.id, new.periodo, new.dia, new.turno, false, 'restriccion_real_de_especialidad'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2094 (class 2618 OID 27029)
-- Dependencies: 1967 1967 1967 1967 1967 1967 1967 222 1967 1967
-- Name: actualiza_restriccion_real_de_lugar; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_restriccion_real_de_lugar AS ON UPDATE TO restriccion_de_lugar DO SELECT actualiza_restriccion_real(new.lugar, new.actividad, new.id, new.periodo, new.dia, new.turno, new.por_actividad, 'restriccion_real_de_lugar'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2095 (class 2618 OID 27030)
-- Dependencies: 1968 1968 1968 1968 1968 1968 1968 222 1968 1968
-- Name: actualiza_restriccion_real_de_otro_recurso; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_restriccion_real_de_otro_recurso AS ON UPDATE TO restriccion_de_otro_recurso DO SELECT actualiza_restriccion_real(new.otro_recurso, new.actividad, new.id, new.periodo, new.dia, new.turno, new.por_actividad, 'restriccion_real_de_otro_recurso'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2096 (class 2618 OID 27031)
-- Dependencies: 1969 1969 1969 1969 1969 1969 222
-- Name: actualiza_restriccion_real_de_periodo; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_restriccion_real_de_periodo AS ON UPDATE TO restriccion_de_periodo DO SELECT actualiza_restriccion_real(new.periodo, NULL::character varying, new.id, new.periodo, new.dia, new.turno, false, 'restriccion_real_de_periodo'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2097 (class 2618 OID 27032)
-- Dependencies: 1970 1970 1970 1970 1970 1970 1970 1970 222 1970
-- Name: actualiza_restriccion_real_de_profesor; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_restriccion_real_de_profesor AS ON UPDATE TO restriccion_de_profesor DO SELECT actualiza_restriccion_real(new.profesor, new.actividad, new.id, new.periodo, new.dia, new.turno, new.por_actividad, 'restriccion_real_de_profesor'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2098 (class 2618 OID 27033)
-- Dependencies: 1934 1934 1934 1934 1934 1934 1934 168 1934 1934
-- Name: actualiza_restricciones_por_actividad; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_restricciones_por_actividad AS ON INSERT TO actividad DO SELECT restringe_segun_actividad(new.id, new.plantilla, new.profesor, new.lugar, new.dia, new.turno, new.grupo) AS restringe_segun_actividad;


--
-- TOC entry 2099 (class 2618 OID 27034)
-- Dependencies: 1955 164 1955 1955 1955
-- Name: actualiza_restricciones_por_uso_de_otro_recurso; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE actualiza_restricciones_por_uso_de_otro_recurso AS ON INSERT TO otros_recursos_usados DO SELECT restringe_por_uso_de_otro_recurso(new.actividad, new.otro_recurso) AS restringe_por_uso_de_otro_recurso;


--
-- TOC entry 2100 (class 2618 OID 27035)
-- Dependencies: 1963 1963 1963 1963 1963 1963 222 1963
-- Name: crea_restriccion_real_de_asignatura; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE crea_restriccion_real_de_asignatura AS ON INSERT TO restriccion_de_asignatura DO SELECT actualiza_restriccion_real(new.asignatura, NULL::character varying, new.id, new.periodo, new.dia, new.turno, false, 'restriccion_real_de_asignatura'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2101 (class 2618 OID 27036)
-- Dependencies: 1964 1964 1964 1964 1964 1964 1964 1964 1964 222
-- Name: crea_restriccion_real_de_brigada; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE crea_restriccion_real_de_brigada AS ON INSERT TO restriccion_de_brigada DO SELECT actualiza_restriccion_real(new.brigada, new.actividad, new.id, new.periodo, new.dia, new.turno, new.por_actividad, 'restriccion_real_de_brigada'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2102 (class 2618 OID 27037)
-- Dependencies: 1965 222 1965 1965 1965 1965 1965 1965
-- Name: crea_restriccion_real_de_clasif_act; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE crea_restriccion_real_de_clasif_act AS ON INSERT TO restriccion_de_clasif_act DO SELECT actualiza_restriccion_real(new.clasificacion, NULL::character varying, new.id, new.periodo, new.dia, new.turno, false, 'restriccion_real_de_clasif_act'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2103 (class 2618 OID 27038)
-- Dependencies: 1966 1966 1966 1966 1966 1966 222 1966
-- Name: crea_restriccion_real_de_especialidad; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE crea_restriccion_real_de_especialidad AS ON INSERT TO restriccion_de_especialidad DO SELECT actualiza_restriccion_real(new.especialidad, NULL::character varying, new.id, new.periodo, new.dia, new.turno, false, 'restriccion_real_de_especialidad'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2104 (class 2618 OID 27039)
-- Dependencies: 1967 1967 222 1967 1967 1967 1967 1967 1967 1967
-- Name: crea_restriccion_real_de_lugar; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE crea_restriccion_real_de_lugar AS ON INSERT TO restriccion_de_lugar DO SELECT actualiza_restriccion_real(new.lugar, new.actividad, new.id, new.periodo, new.dia, new.turno, new.por_actividad, 'restriccion_real_de_lugar'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2105 (class 2618 OID 27040)
-- Dependencies: 1968 222 1968 1968 1968 1968 1968 1968 1968 1968
-- Name: crea_restriccion_real_de_otro_recurso; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE crea_restriccion_real_de_otro_recurso AS ON INSERT TO restriccion_de_otro_recurso DO SELECT actualiza_restriccion_real(new.otro_recurso, new.actividad, new.id, new.periodo, new.dia, new.turno, new.por_actividad, 'restriccion_real_de_otro_recurso'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2106 (class 2618 OID 27041)
-- Dependencies: 1969 1969 1969 222 1969 1969 1969
-- Name: crea_restriccion_real_de_periodo; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE crea_restriccion_real_de_periodo AS ON INSERT TO restriccion_de_periodo DO SELECT actualiza_restriccion_real(new.periodo, NULL::character varying, new.id, new.periodo, new.dia, new.turno, false, 'restriccion_real_de_periodo'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2107 (class 2618 OID 27042)
-- Dependencies: 1970 1970 1970 1970 1970 1970 1970 1970 222 1970
-- Name: crea_restriccion_real_de_profesor; Type: RULE; Schema: public; Owner: rafa
--

CREATE RULE crea_restriccion_real_de_profesor AS ON INSERT TO restriccion_de_profesor DO SELECT actualiza_restriccion_real(new.profesor, new.actividad, new.id, new.periodo, new.dia, new.turno, new.por_actividad, 'restriccion_real_de_profesor'::character varying) AS actualiza_restriccion_real;


--
-- TOC entry 2694 (class 2620 OID 27043)
-- Dependencies: 157 1938
-- Name: nuevo_desglose_si_nueva_asignatura; Type: TRIGGER; Schema: public; Owner: rafa
--

CREATE TRIGGER nuevo_desglose_si_nueva_asignatura
    AFTER INSERT ON asignatura
    FOR EACH ROW
    EXECUTE PROCEDURE nuevo_desglose_cuando_nueva_asignatura();


--
-- TOC entry 2693 (class 2620 OID 27044)
-- Dependencies: 1936 158
-- Name: nuevo_desglose_si_nuevo_periodo; Type: TRIGGER; Schema: public; Owner: rafa
--

CREATE TRIGGER nuevo_desglose_si_nuevo_periodo
    AFTER INSERT ON periodo
    FOR EACH ROW
    EXECUTE PROCEDURE nuevo_desglose_cuando_nuevo_periodo();


--
-- TOC entry 2699 (class 2620 OID 27045)
-- Dependencies: 44 1958
-- Name: trigger_actualiza_orden_act_after_delete; Type: TRIGGER; Schema: public; Owner: rafa
--

CREATE TRIGGER trigger_actualiza_orden_act_after_delete
    AFTER DELETE ON plantilla_de_actividad
    FOR EACH ROW
    EXECUTE PROCEDURE actualiza_orden_act_after_delete();


--
-- TOC entry 2700 (class 2620 OID 27046)
-- Dependencies: 1958 45
-- Name: trigger_actualiza_orden_act_after_insert; Type: TRIGGER; Schema: public; Owner: rafa
--

CREATE TRIGGER trigger_actualiza_orden_act_after_insert
    AFTER INSERT ON plantilla_de_actividad
    FOR EACH ROW
    EXECUTE PROCEDURE actualiza_orden_act_after_insert();


--
-- TOC entry 2701 (class 2620 OID 27047)
-- Dependencies: 46 1958
-- Name: trigger_actualiza_orden_act_after_update; Type: TRIGGER; Schema: public; Owner: rafa
--

CREATE TRIGGER trigger_actualiza_orden_act_after_update
    AFTER UPDATE ON plantilla_de_actividad
    FOR EACH ROW
    EXECUTE PROCEDURE actualiza_orden_act_after_update();


--
-- TOC entry 2702 (class 2620 OID 27048)
-- Dependencies: 1958 47
-- Name: trigger_actualiza_orden_act_before_insert; Type: TRIGGER; Schema: public; Owner: rafa
--

CREATE TRIGGER trigger_actualiza_orden_act_before_insert
    BEFORE INSERT ON plantilla_de_actividad
    FOR EACH ROW
    EXECUTE PROCEDURE actualiza_orden_act_before_insert();


--
-- TOC entry 2703 (class 2620 OID 27049)
-- Dependencies: 48 1958
-- Name: trigger_actualiza_orden_act_before_update; Type: TRIGGER; Schema: public; Owner: rafa
--

CREATE TRIGGER trigger_actualiza_orden_act_before_update
    BEFORE UPDATE ON plantilla_de_actividad
    FOR EACH ROW
    EXECUTE PROCEDURE actualiza_orden_act_before_update();


--
-- TOC entry 2704 (class 2620 OID 27050)
-- Dependencies: 51 1961
-- Name: trigger_actualiza_profesor_para_grupo; Type: TRIGGER; Schema: public; Owner: rafa
--

CREATE TRIGGER trigger_actualiza_profesor_para_grupo
    BEFORE UPDATE ON profesor_para_grupo
    FOR EACH ROW
    EXECUTE PROCEDURE actualiza_profesor_para_grupo();


--
-- TOC entry 2695 (class 2620 OID 34379)
-- Dependencies: 1942 203
-- Name: trigger_arregla_prioridades_clasif_after_delete; Type: TRIGGER; Schema: public; Owner: rafa
--

CREATE TRIGGER trigger_arregla_prioridades_clasif_after_delete
    AFTER DELETE ON clasificacion_de_actividad
    FOR EACH ROW
    EXECUTE PROCEDURE arregla_prioridades_clasif_after_delete();


--
-- TOC entry 2696 (class 2620 OID 27051)
-- Dependencies: 1950 154
-- Name: trigger_nivela_prioridad_lxact_after_delete; Type: TRIGGER; Schema: public; Owner: rafa
--

CREATE TRIGGER trigger_nivela_prioridad_lxact_after_delete
    AFTER DELETE ON lugar_para_actividad
    FOR EACH ROW
    EXECUTE PROCEDURE nivela_prioridad_lxact_after_delete();


--
-- TOC entry 2697 (class 2620 OID 27052)
-- Dependencies: 155 1950
-- Name: trigger_nivela_prioridad_lxact_after_update; Type: TRIGGER; Schema: public; Owner: rafa
--

CREATE TRIGGER trigger_nivela_prioridad_lxact_after_update
    AFTER UPDATE ON lugar_para_actividad
    FOR EACH ROW
    EXECUTE PROCEDURE nivela_prioridad_lxact_after_update();


--
-- TOC entry 2698 (class 2620 OID 27053)
-- Dependencies: 156 1952
-- Name: trigger_nivela_prioridad_rxact_after_delete; Type: TRIGGER; Schema: public; Owner: rafa
--

CREATE TRIGGER trigger_nivela_prioridad_rxact_after_delete
    AFTER DELETE ON otro_grupo_de_recursos_para_actividad
    FOR EACH ROW
    EXECUTE PROCEDURE nivela_prioridad_rxact_after_delete();


--
-- TOC entry 2583 (class 2606 OID 27054)
-- Dependencies: 1932 2406 1937
-- Name: acceso_prohibido_grupo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY acceso_prohibido
    ADD CONSTRAINT acceso_prohibido_grupo_fkey FOREIGN KEY (grupo) REFERENCES grupo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2584 (class 2606 OID 27059)
-- Dependencies: 1937 2408 1933
-- Name: acceso_prohibido_lugar_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY acceso_prohibido
    ADD CONSTRAINT acceso_prohibido_lugar_fkey FOREIGN KEY (lugar) REFERENCES lugar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2562 (class 2606 OID 27064)
-- Dependencies: 2550 1992 1934
-- Name: actividad_creador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY actividad
    ADD CONSTRAINT actividad_creador_fkey FOREIGN KEY (creador) REFERENCES seguridad.usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2563 (class 2606 OID 27069)
-- Dependencies: 1934 2406 1932
-- Name: actividad_grupo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY actividad
    ADD CONSTRAINT actividad_grupo_fkey FOREIGN KEY (grupo) REFERENCES grupo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2564 (class 2606 OID 27074)
-- Dependencies: 1933 1934 2408
-- Name: actividad_lugar_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY actividad
    ADD CONSTRAINT actividad_lugar_fkey FOREIGN KEY (lugar) REFERENCES lugar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2565 (class 2606 OID 27079)
-- Dependencies: 1958 2466 1934
-- Name: actividad_plantilla_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY actividad
    ADD CONSTRAINT actividad_plantilla_fkey FOREIGN KEY (plantilla) REFERENCES plantilla_de_actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2566 (class 2606 OID 27084)
-- Dependencies: 1934 2468 1959
-- Name: actividad_profesor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY actividad
    ADD CONSTRAINT actividad_profesor_fkey FOREIGN KEY (profesor) REFERENCES profesor(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2585 (class 2606 OID 27089)
-- Dependencies: 1992 2550 1938
-- Name: asignatura_creador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY asignatura
    ADD CONSTRAINT asignatura_creador_fkey FOREIGN KEY (creador) REFERENCES seguridad.usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2586 (class 2606 OID 27094)
-- Dependencies: 1938 2432 1944
-- Name: asignatura_especialidad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY asignatura
    ADD CONSTRAINT asignatura_especialidad_fkey FOREIGN KEY (especialidad) REFERENCES especialidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2587 (class 2606 OID 27099)
-- Dependencies: 1951 1938 2448
-- Name: asignatura_nivel_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY asignatura
    ADD CONSTRAINT asignatura_nivel_fkey FOREIGN KEY (nivel) REFERENCES nivel(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2554 (class 2606 OID 27104)
-- Dependencies: 2550 1992 1931
-- Name: brigada_creador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY brigada
    ADD CONSTRAINT brigada_creador_fkey FOREIGN KEY (creador) REFERENCES seguridad.usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2590 (class 2606 OID 27109)
-- Dependencies: 2404 1941 1931
-- Name: brigada_de_grupo_brigada_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY brigada_de_grupo
    ADD CONSTRAINT brigada_de_grupo_brigada_fkey FOREIGN KEY (brigada) REFERENCES brigada(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2591 (class 2606 OID 27114)
-- Dependencies: 1932 1941 2406
-- Name: brigada_de_grupo_grupo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY brigada_de_grupo
    ADD CONSTRAINT brigada_de_grupo_grupo_fkey FOREIGN KEY (grupo) REFERENCES grupo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2555 (class 2606 OID 27119)
-- Dependencies: 1931 1944 2432
-- Name: brigada_especialidad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY brigada
    ADD CONSTRAINT brigada_especialidad_fkey FOREIGN KEY (especialidad) REFERENCES especialidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2556 (class 2606 OID 27124)
-- Dependencies: 1931 1951 2448
-- Name: brigada_nivel_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY brigada
    ADD CONSTRAINT brigada_nivel_fkey FOREIGN KEY (nivel) REFERENCES nivel(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2592 (class 2606 OID 27129)
-- Dependencies: 1942 2550 1992
-- Name: clasificacion_de_actividad_creador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY clasificacion_de_actividad
    ADD CONSTRAINT clasificacion_de_actividad_creador_fkey FOREIGN KEY (creador) REFERENCES seguridad.usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2593 (class 2606 OID 27134)
-- Dependencies: 2408 1943 1933
-- Name: costo_de_traslado_destino_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY costo_de_traslado
    ADD CONSTRAINT costo_de_traslado_destino_fkey FOREIGN KEY (destino) REFERENCES lugar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2594 (class 2606 OID 27139)
-- Dependencies: 2408 1943 1933
-- Name: costo_de_traslado_origen_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY costo_de_traslado
    ADD CONSTRAINT costo_de_traslado_origen_fkey FOREIGN KEY (origen) REFERENCES lugar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2588 (class 2606 OID 27144)
-- Dependencies: 1938 1939 2420
-- Name: desglose_asignatura_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY desglose
    ADD CONSTRAINT desglose_asignatura_fkey FOREIGN KEY (asignatura) REFERENCES asignatura(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2589 (class 2606 OID 27149)
-- Dependencies: 1936 2416 1939
-- Name: desglose_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY desglose
    ADD CONSTRAINT desglose_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2595 (class 2606 OID 27154)
-- Dependencies: 1992 2550 1944
-- Name: especialidad_creador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY especialidad
    ADD CONSTRAINT especialidad_creador_fkey FOREIGN KEY (creador) REFERENCES seguridad.usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2596 (class 2606 OID 27159)
-- Dependencies: 1945 1946 2438
-- Name: excepcion_de_hrt_herencia_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY excepcion_de_hrt
    ADD CONSTRAINT excepcion_de_hrt_herencia_fkey FOREIGN KEY (herencia) REFERENCES herencia_de_restricciones(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2597 (class 2606 OID 27164)
-- Dependencies: 2416 1936 1945
-- Name: excepcion_de_hrt_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY excepcion_de_hrt
    ADD CONSTRAINT excepcion_de_hrt_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2557 (class 2606 OID 27169)
-- Dependencies: 1932 1942 2428
-- Name: grupo_clasificacion_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY grupo
    ADD CONSTRAINT grupo_clasificacion_fkey FOREIGN KEY (clasificacion) REFERENCES clasificacion_de_actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2558 (class 2606 OID 27174)
-- Dependencies: 1932 1944 2432
-- Name: grupo_especialidad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY grupo
    ADD CONSTRAINT grupo_especialidad_fkey FOREIGN KEY (especialidad) REFERENCES especialidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2559 (class 2606 OID 27179)
-- Dependencies: 1932 1951 2448
-- Name: grupo_nivel_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY grupo
    ADD CONSTRAINT grupo_nivel_fkey FOREIGN KEY (nivel) REFERENCES nivel(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2560 (class 2606 OID 27184)
-- Dependencies: 1957 1932 2462
-- Name: grupo_planificacion_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY grupo
    ADD CONSTRAINT grupo_planificacion_fkey FOREIGN KEY (planificacion) REFERENCES planificacion(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2598 (class 2606 OID 27189)
-- Dependencies: 1931 1946 2404
-- Name: herencia_de_restricciones_hijo_brigada_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY herencia_de_restricciones
    ADD CONSTRAINT herencia_de_restricciones_hijo_brigada_fkey FOREIGN KEY (hijo_brigada) REFERENCES brigada(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2599 (class 2606 OID 27194)
-- Dependencies: 2408 1933 1946
-- Name: herencia_de_restricciones_hijo_lugar_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY herencia_de_restricciones
    ADD CONSTRAINT herencia_de_restricciones_hijo_lugar_fkey FOREIGN KEY (hijo_lugar) REFERENCES lugar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2600 (class 2606 OID 27199)
-- Dependencies: 2452 1946 1953
-- Name: herencia_de_restricciones_hijo_otro_recurso_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY herencia_de_restricciones
    ADD CONSTRAINT herencia_de_restricciones_hijo_otro_recurso_fkey FOREIGN KEY (hijo_otro_recurso) REFERENCES otro_recurso(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2601 (class 2606 OID 27204)
-- Dependencies: 2468 1946 1959
-- Name: herencia_de_restricciones_hijo_profesor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY herencia_de_restricciones
    ADD CONSTRAINT herencia_de_restricciones_hijo_profesor_fkey FOREIGN KEY (hijo_profesor) REFERENCES profesor(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2602 (class 2606 OID 27209)
-- Dependencies: 1946 1931 2404
-- Name: herencia_de_restricciones_padre_brigada_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY herencia_de_restricciones
    ADD CONSTRAINT herencia_de_restricciones_padre_brigada_fkey FOREIGN KEY (padre_brigada) REFERENCES brigada(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2603 (class 2606 OID 27214)
-- Dependencies: 2408 1946 1933
-- Name: herencia_de_restricciones_padre_lugar_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY herencia_de_restricciones
    ADD CONSTRAINT herencia_de_restricciones_padre_lugar_fkey FOREIGN KEY (padre_lugar) REFERENCES lugar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2604 (class 2606 OID 27219)
-- Dependencies: 1946 1953 2452
-- Name: herencia_de_restricciones_padre_otro_recurso_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY herencia_de_restricciones
    ADD CONSTRAINT herencia_de_restricciones_padre_otro_recurso_fkey FOREIGN KEY (padre_otro_recurso) REFERENCES otro_recurso(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2605 (class 2606 OID 27224)
-- Dependencies: 1946 2468 1959
-- Name: herencia_de_restricciones_padre_profesor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY herencia_de_restricciones
    ADD CONSTRAINT herencia_de_restricciones_padre_profesor_fkey FOREIGN KEY (padre_profesor) REFERENCES profesor(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2606 (class 2606 OID 27229)
-- Dependencies: 1957 2462 1946
-- Name: herencia_de_restricciones_planificacion_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY herencia_de_restricciones
    ADD CONSTRAINT herencia_de_restricciones_planificacion_fkey FOREIGN KEY (planificacion) REFERENCES planificacion(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2607 (class 2606 OID 27234)
-- Dependencies: 2404 1947 1931
-- Name: higiene_para_brigada_brigada_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY higiene_para_brigada
    ADD CONSTRAINT higiene_para_brigada_brigada_fkey FOREIGN KEY (brigada) REFERENCES brigada(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2608 (class 2606 OID 27239)
-- Dependencies: 1947 2416 1936
-- Name: higiene_para_brigada_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY higiene_para_brigada
    ADD CONSTRAINT higiene_para_brigada_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2609 (class 2606 OID 27244)
-- Dependencies: 2416 1936 1948
-- Name: higiene_para_profesor_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY higiene_para_profesor
    ADD CONSTRAINT higiene_para_profesor_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2610 (class 2606 OID 27249)
-- Dependencies: 2468 1959 1948
-- Name: higiene_para_profesor_profesor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY higiene_para_profesor
    ADD CONSTRAINT higiene_para_profesor_profesor_fkey FOREIGN KEY (profesor) REFERENCES profesor(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2561 (class 2606 OID 27254)
-- Dependencies: 1992 1933 2550
-- Name: lugar_creador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY lugar
    ADD CONSTRAINT lugar_creador_fkey FOREIGN KEY (creador) REFERENCES seguridad.usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2611 (class 2606 OID 27259)
-- Dependencies: 1932 1949 2406
-- Name: lugar_fijo_grupo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY lugar_fijo
    ADD CONSTRAINT lugar_fijo_grupo_fkey FOREIGN KEY (grupo) REFERENCES grupo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2612 (class 2606 OID 27264)
-- Dependencies: 1933 2408 1949
-- Name: lugar_fijo_lugar_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY lugar_fijo
    ADD CONSTRAINT lugar_fijo_lugar_fkey FOREIGN KEY (lugar) REFERENCES lugar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2613 (class 2606 OID 27269)
-- Dependencies: 1933 2408 1950
-- Name: lugar_para_actividad_lugar_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY lugar_para_actividad
    ADD CONSTRAINT lugar_para_actividad_lugar_fkey FOREIGN KEY (lugar) REFERENCES lugar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2614 (class 2606 OID 27274)
-- Dependencies: 2466 1950 1958
-- Name: lugar_para_actividad_plantilla_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY lugar_para_actividad
    ADD CONSTRAINT lugar_para_actividad_plantilla_fkey FOREIGN KEY (plantilla) REFERENCES plantilla_de_actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2615 (class 2606 OID 27279)
-- Dependencies: 2550 1992 1951
-- Name: nivel_creador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY nivel
    ADD CONSTRAINT nivel_creador_fkey FOREIGN KEY (creador) REFERENCES seguridad.usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2616 (class 2606 OID 27284)
-- Dependencies: 1952 1992 2550
-- Name: otro_grupo_de_recursos_para_actividad_creador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY otro_grupo_de_recursos_para_actividad
    ADD CONSTRAINT otro_grupo_de_recursos_para_actividad_creador_fkey FOREIGN KEY (creador) REFERENCES seguridad.usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2617 (class 2606 OID 27289)
-- Dependencies: 2466 1952 1958
-- Name: otro_grupo_de_recursos_para_actividad_plantilla_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY otro_grupo_de_recursos_para_actividad
    ADD CONSTRAINT otro_grupo_de_recursos_para_actividad_plantilla_fkey FOREIGN KEY (plantilla) REFERENCES plantilla_de_actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2618 (class 2606 OID 27294)
-- Dependencies: 2550 1992 1953
-- Name: otro_recurso_creador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY otro_recurso
    ADD CONSTRAINT otro_recurso_creador_fkey FOREIGN KEY (creador) REFERENCES seguridad.usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2619 (class 2606 OID 27299)
-- Dependencies: 1952 2450 1954
-- Name: otro_recurso_para_actividad_grupo_de_recursos_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY otro_recurso_para_actividad
    ADD CONSTRAINT otro_recurso_para_actividad_grupo_de_recursos_fkey FOREIGN KEY (grupo_de_recursos) REFERENCES otro_grupo_de_recursos_para_actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2620 (class 2606 OID 27304)
-- Dependencies: 1953 2452 1954
-- Name: otro_recurso_para_actividad_otro_recurso_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY otro_recurso_para_actividad
    ADD CONSTRAINT otro_recurso_para_actividad_otro_recurso_fkey FOREIGN KEY (otro_recurso) REFERENCES otro_recurso(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2621 (class 2606 OID 27309)
-- Dependencies: 2410 1934 1955
-- Name: otros_recursos_usados_actividad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY otros_recursos_usados
    ADD CONSTRAINT otros_recursos_usados_actividad_fkey FOREIGN KEY (actividad) REFERENCES actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2622 (class 2606 OID 27314)
-- Dependencies: 1955 1992 2550
-- Name: otros_recursos_usados_creador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY otros_recursos_usados
    ADD CONSTRAINT otros_recursos_usados_creador_fkey FOREIGN KEY (creador) REFERENCES seguridad.usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2623 (class 2606 OID 27319)
-- Dependencies: 2452 1953 1955
-- Name: otros_recursos_usados_otro_recurso_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY otros_recursos_usados
    ADD CONSTRAINT otros_recursos_usados_otro_recurso_fkey FOREIGN KEY (otro_recurso) REFERENCES otro_recurso(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2624 (class 2606 OID 27324)
-- Dependencies: 1956 1936 2416
-- Name: periodicidad_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY periodicidad
    ADD CONSTRAINT periodicidad_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2582 (class 2606 OID 27329)
-- Dependencies: 2462 1936 1957
-- Name: periodo_planificacion_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY periodo
    ADD CONSTRAINT periodo_planificacion_fkey FOREIGN KEY (planificacion) REFERENCES planificacion(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2625 (class 2606 OID 27334)
-- Dependencies: 1992 1957 2550
-- Name: planificacion_creador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY planificacion
    ADD CONSTRAINT planificacion_creador_fkey FOREIGN KEY (creador) REFERENCES seguridad.usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2626 (class 2606 OID 27339)
-- Dependencies: 1942 1958 2428
-- Name: plantilla_de_actividad_clasificacion_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY plantilla_de_actividad
    ADD CONSTRAINT plantilla_de_actividad_clasificacion_fkey FOREIGN KEY (clasificacion) REFERENCES clasificacion_de_actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2627 (class 2606 OID 27344)
-- Dependencies: 2424 1958 1939
-- Name: plantilla_de_actividad_desglose_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY plantilla_de_actividad
    ADD CONSTRAINT plantilla_de_actividad_desglose_fkey FOREIGN KEY (desglose) REFERENCES desglose(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2628 (class 2606 OID 27349)
-- Dependencies: 2550 1992 1959
-- Name: profesor_creador_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY profesor
    ADD CONSTRAINT profesor_creador_fkey FOREIGN KEY (creador) REFERENCES seguridad.usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2629 (class 2606 OID 27354)
-- Dependencies: 1958 1960 2466
-- Name: profesor_para_actividad_plantilla_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY profesor_para_actividad
    ADD CONSTRAINT profesor_para_actividad_plantilla_fkey FOREIGN KEY (plantilla) REFERENCES plantilla_de_actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2630 (class 2606 OID 27359)
-- Dependencies: 1959 1960 2468
-- Name: profesor_para_actividad_profesor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY profesor_para_actividad
    ADD CONSTRAINT profesor_para_actividad_profesor_fkey FOREIGN KEY (profesor) REFERENCES profesor(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2631 (class 2606 OID 27364)
-- Dependencies: 1932 2406 1961
-- Name: profesor_para_grupo_grupo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY profesor_para_grupo
    ADD CONSTRAINT profesor_para_grupo_grupo_fkey FOREIGN KEY (grupo) REFERENCES grupo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2632 (class 2606 OID 27369)
-- Dependencies: 1960 2470 1961
-- Name: profesor_para_grupo_profesor_para_actividad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY profesor_para_grupo
    ADD CONSTRAINT profesor_para_grupo_profesor_para_actividad_fkey FOREIGN KEY (profesor_para_actividad) REFERENCES profesor_para_actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2633 (class 2606 OID 27374)
-- Dependencies: 1962 1936 2416
-- Name: receso_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY receso
    ADD CONSTRAINT receso_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2567 (class 2606 OID 27379)
-- Dependencies: 1935 1938 2420
-- Name: restriccion_condicional_asig_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_asig_fkey FOREIGN KEY (asig) REFERENCES asignatura(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2568 (class 2606 OID 27384)
-- Dependencies: 1931 1935 2404
-- Name: restriccion_condicional_brigada_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_brigada_fkey FOREIGN KEY (brigada) REFERENCES brigada(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2569 (class 2606 OID 27389)
-- Dependencies: 1935 1942 2428
-- Name: restriccion_condicional_clasif_act_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_clasif_act_fkey FOREIGN KEY (clasif_act) REFERENCES clasificacion_de_actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2570 (class 2606 OID 27394)
-- Dependencies: 1935 1931 2404
-- Name: restriccion_condicional_consec_brigada_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_consec_brigada_fkey FOREIGN KEY (consec_brigada) REFERENCES brigada(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2571 (class 2606 OID 27399)
-- Dependencies: 1935 1933 2408
-- Name: restriccion_condicional_consec_lugar_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_consec_lugar_fkey FOREIGN KEY (consec_lugar) REFERENCES lugar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2572 (class 2606 OID 27404)
-- Dependencies: 1935 1953 2452
-- Name: restriccion_condicional_consec_otro_recurso_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_consec_otro_recurso_fkey FOREIGN KEY (consec_otro_recurso) REFERENCES otro_recurso(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2573 (class 2606 OID 27409)
-- Dependencies: 1935 1936 2416
-- Name: restriccion_condicional_consec_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_consec_periodo_fkey FOREIGN KEY (consec_periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2574 (class 2606 OID 27414)
-- Dependencies: 1935 1959 2468
-- Name: restriccion_condicional_consec_profesor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_consec_profesor_fkey FOREIGN KEY (consec_profesor) REFERENCES profesor(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2575 (class 2606 OID 27419)
-- Dependencies: 1935 1944 2432
-- Name: restriccion_condicional_especialidad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_especialidad_fkey FOREIGN KEY (especialidad) REFERENCES especialidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2576 (class 2606 OID 27424)
-- Dependencies: 1933 1935 2408
-- Name: restriccion_condicional_lugar_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_lugar_fkey FOREIGN KEY (lugar) REFERENCES lugar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2577 (class 2606 OID 27429)
-- Dependencies: 1935 1951 2448
-- Name: restriccion_condicional_nivel_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_nivel_fkey FOREIGN KEY (nivel) REFERENCES nivel(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2578 (class 2606 OID 27434)
-- Dependencies: 1935 1953 2452
-- Name: restriccion_condicional_otro_recurso_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_otro_recurso_fkey FOREIGN KEY (otro_recurso) REFERENCES otro_recurso(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2579 (class 2606 OID 27439)
-- Dependencies: 1935 1936 2416
-- Name: restriccion_condicional_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2580 (class 2606 OID 27444)
-- Dependencies: 1935 1957 2462
-- Name: restriccion_condicional_planificacion_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_planificacion_fkey FOREIGN KEY (planificacion) REFERENCES planificacion(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2581 (class 2606 OID 27449)
-- Dependencies: 1935 1959 2468
-- Name: restriccion_condicional_profesor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_condicional
    ADD CONSTRAINT restriccion_condicional_profesor_fkey FOREIGN KEY (profesor) REFERENCES profesor(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2634 (class 2606 OID 27454)
-- Dependencies: 1963 1938 2420
-- Name: restriccion_de_asignatura_asignatura_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_asignatura
    ADD CONSTRAINT restriccion_de_asignatura_asignatura_fkey FOREIGN KEY (asignatura) REFERENCES asignatura(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2635 (class 2606 OID 27459)
-- Dependencies: 1963 1936 2416
-- Name: restriccion_de_asignatura_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_asignatura
    ADD CONSTRAINT restriccion_de_asignatura_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2636 (class 2606 OID 27464)
-- Dependencies: 1964 1934 2410
-- Name: restriccion_de_brigada_actividad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_brigada
    ADD CONSTRAINT restriccion_de_brigada_actividad_fkey FOREIGN KEY (actividad) REFERENCES actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2637 (class 2606 OID 27469)
-- Dependencies: 1964 1931 2404
-- Name: restriccion_de_brigada_brigada_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_brigada
    ADD CONSTRAINT restriccion_de_brigada_brigada_fkey FOREIGN KEY (brigada) REFERENCES brigada(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2638 (class 2606 OID 27474)
-- Dependencies: 1964 2416 1936
-- Name: restriccion_de_brigada_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_brigada
    ADD CONSTRAINT restriccion_de_brigada_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2639 (class 2606 OID 27479)
-- Dependencies: 1942 1965 2428
-- Name: restriccion_de_clasif_act_clasificacion_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_clasif_act
    ADD CONSTRAINT restriccion_de_clasif_act_clasificacion_fkey FOREIGN KEY (clasificacion) REFERENCES clasificacion_de_actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2640 (class 2606 OID 27484)
-- Dependencies: 1965 1936 2416
-- Name: restriccion_de_clasif_act_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_clasif_act
    ADD CONSTRAINT restriccion_de_clasif_act_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2641 (class 2606 OID 27489)
-- Dependencies: 2432 1966 1944
-- Name: restriccion_de_especialidad_especialidad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_especialidad
    ADD CONSTRAINT restriccion_de_especialidad_especialidad_fkey FOREIGN KEY (especialidad) REFERENCES especialidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2642 (class 2606 OID 27494)
-- Dependencies: 1936 1966 2416
-- Name: restriccion_de_especialidad_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_especialidad
    ADD CONSTRAINT restriccion_de_especialidad_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2643 (class 2606 OID 27499)
-- Dependencies: 1934 1967 2410
-- Name: restriccion_de_lugar_actividad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_lugar
    ADD CONSTRAINT restriccion_de_lugar_actividad_fkey FOREIGN KEY (actividad) REFERENCES actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2644 (class 2606 OID 27504)
-- Dependencies: 1933 1967 2408
-- Name: restriccion_de_lugar_lugar_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_lugar
    ADD CONSTRAINT restriccion_de_lugar_lugar_fkey FOREIGN KEY (lugar) REFERENCES lugar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2645 (class 2606 OID 27509)
-- Dependencies: 1936 1967 2416
-- Name: restriccion_de_lugar_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_lugar
    ADD CONSTRAINT restriccion_de_lugar_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2646 (class 2606 OID 27514)
-- Dependencies: 1934 1968 2410
-- Name: restriccion_de_otro_recurso_actividad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_otro_recurso
    ADD CONSTRAINT restriccion_de_otro_recurso_actividad_fkey FOREIGN KEY (actividad) REFERENCES actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2647 (class 2606 OID 27519)
-- Dependencies: 1953 2452 1968
-- Name: restriccion_de_otro_recurso_otro_recurso_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_otro_recurso
    ADD CONSTRAINT restriccion_de_otro_recurso_otro_recurso_fkey FOREIGN KEY (otro_recurso) REFERENCES otro_recurso(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2648 (class 2606 OID 27524)
-- Dependencies: 1968 1936 2416
-- Name: restriccion_de_otro_recurso_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_otro_recurso
    ADD CONSTRAINT restriccion_de_otro_recurso_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2649 (class 2606 OID 27529)
-- Dependencies: 2416 1969 1936
-- Name: restriccion_de_periodo_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_periodo
    ADD CONSTRAINT restriccion_de_periodo_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2650 (class 2606 OID 27534)
-- Dependencies: 1970 1934 2410
-- Name: restriccion_de_profesor_actividad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_profesor
    ADD CONSTRAINT restriccion_de_profesor_actividad_fkey FOREIGN KEY (actividad) REFERENCES actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2651 (class 2606 OID 27539)
-- Dependencies: 1936 1970 2416
-- Name: restriccion_de_profesor_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_profesor
    ADD CONSTRAINT restriccion_de_profesor_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2652 (class 2606 OID 27544)
-- Dependencies: 2468 1970 1959
-- Name: restriccion_de_profesor_profesor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_de_profesor
    ADD CONSTRAINT restriccion_de_profesor_profesor_fkey FOREIGN KEY (profesor) REFERENCES profesor(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2653 (class 2606 OID 27549)
-- Dependencies: 1934 1972 2410
-- Name: restriccion_por_actividad_de_brigada_actividad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_por_actividad_de_brigada
    ADD CONSTRAINT restriccion_por_actividad_de_brigada_actividad_fkey FOREIGN KEY (actividad) REFERENCES actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2654 (class 2606 OID 27554)
-- Dependencies: 2484 1964 1972
-- Name: restriccion_por_actividad_de_brigada_restriccion_nominal_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_por_actividad_de_brigada
    ADD CONSTRAINT restriccion_por_actividad_de_brigada_restriccion_nominal_fkey FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_brigada(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2655 (class 2606 OID 27559)
-- Dependencies: 2410 1934 1973
-- Name: restriccion_por_actividad_de_lugar_actividad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_por_actividad_de_lugar
    ADD CONSTRAINT restriccion_por_actividad_de_lugar_actividad_fkey FOREIGN KEY (actividad) REFERENCES actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2656 (class 2606 OID 27564)
-- Dependencies: 2496 1967 1973
-- Name: restriccion_por_actividad_de_lugar_restriccion_nominal_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_por_actividad_de_lugar
    ADD CONSTRAINT restriccion_por_actividad_de_lugar_restriccion_nominal_fkey FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_lugar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2657 (class 2606 OID 27569)
-- Dependencies: 1974 1968 2500
-- Name: restriccion_por_actividad_de_otro_recu_restriccion_nominal_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_por_actividad_de_otro_recurso
    ADD CONSTRAINT restriccion_por_actividad_de_otro_recu_restriccion_nominal_fkey FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_otro_recurso(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2658 (class 2606 OID 27574)
-- Dependencies: 1974 1934 2410
-- Name: restriccion_por_actividad_de_otro_recurso_actividad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_por_actividad_de_otro_recurso
    ADD CONSTRAINT restriccion_por_actividad_de_otro_recurso_actividad_fkey FOREIGN KEY (actividad) REFERENCES actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2659 (class 2606 OID 27579)
-- Dependencies: 2410 1975 1934
-- Name: restriccion_por_actividad_de_profesor_actividad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_por_actividad_de_profesor
    ADD CONSTRAINT restriccion_por_actividad_de_profesor_actividad_fkey FOREIGN KEY (actividad) REFERENCES actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2660 (class 2606 OID 27584)
-- Dependencies: 1970 1975 2506
-- Name: restriccion_por_actividad_de_profesor_restriccion_nominal_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_por_actividad_de_profesor
    ADD CONSTRAINT restriccion_por_actividad_de_profesor_restriccion_nominal_fkey FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_profesor(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2661 (class 2606 OID 27589)
-- Dependencies: 1938 1977 2420
-- Name: restriccion_real_de_asignatura_objeto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_asignatura
    ADD CONSTRAINT restriccion_real_de_asignatura_objeto_fkey FOREIGN KEY (objeto) REFERENCES asignatura(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2662 (class 2606 OID 27594)
-- Dependencies: 1956 1977 2460
-- Name: restriccion_real_de_asignatura_periodicidad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_asignatura
    ADD CONSTRAINT restriccion_real_de_asignatura_periodicidad_fkey FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2663 (class 2606 OID 27599)
-- Dependencies: 1963 1977 2480
-- Name: restriccion_real_de_asignatura_restriccion_nominal_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_asignatura
    ADD CONSTRAINT restriccion_real_de_asignatura_restriccion_nominal_fkey FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_asignatura(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2664 (class 2606 OID 27604)
-- Dependencies: 1934 1978 2410
-- Name: restriccion_real_de_brigada_actividad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_brigada
    ADD CONSTRAINT restriccion_real_de_brigada_actividad_fkey FOREIGN KEY (actividad) REFERENCES actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2665 (class 2606 OID 27609)
-- Dependencies: 1931 1978 2404
-- Name: restriccion_real_de_brigada_objeto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_brigada
    ADD CONSTRAINT restriccion_real_de_brigada_objeto_fkey FOREIGN KEY (objeto) REFERENCES brigada(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2666 (class 2606 OID 27614)
-- Dependencies: 2460 1956 1978
-- Name: restriccion_real_de_brigada_periodicidad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_brigada
    ADD CONSTRAINT restriccion_real_de_brigada_periodicidad_fkey FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2667 (class 2606 OID 27619)
-- Dependencies: 1978 1964 2484
-- Name: restriccion_real_de_brigada_restriccion_nominal_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_brigada
    ADD CONSTRAINT restriccion_real_de_brigada_restriccion_nominal_fkey FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_brigada(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2668 (class 2606 OID 27624)
-- Dependencies: 2428 1979 1942
-- Name: restriccion_real_de_clasif_act_objeto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_clasif_act
    ADD CONSTRAINT restriccion_real_de_clasif_act_objeto_fkey FOREIGN KEY (objeto) REFERENCES clasificacion_de_actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2669 (class 2606 OID 27629)
-- Dependencies: 2460 1979 1956
-- Name: restriccion_real_de_clasif_act_periodicidad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_clasif_act
    ADD CONSTRAINT restriccion_real_de_clasif_act_periodicidad_fkey FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2670 (class 2606 OID 27634)
-- Dependencies: 1965 1979 2488
-- Name: restriccion_real_de_clasif_act_restriccion_nominal_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_clasif_act
    ADD CONSTRAINT restriccion_real_de_clasif_act_restriccion_nominal_fkey FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_clasif_act(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2671 (class 2606 OID 27639)
-- Dependencies: 2432 1980 1944
-- Name: restriccion_real_de_especialidad_objeto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_especialidad
    ADD CONSTRAINT restriccion_real_de_especialidad_objeto_fkey FOREIGN KEY (objeto) REFERENCES especialidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2672 (class 2606 OID 27644)
-- Dependencies: 2460 1956 1980
-- Name: restriccion_real_de_especialidad_periodicidad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_especialidad
    ADD CONSTRAINT restriccion_real_de_especialidad_periodicidad_fkey FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2673 (class 2606 OID 27649)
-- Dependencies: 2492 1980 1966
-- Name: restriccion_real_de_especialidad_restriccion_nominal_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_especialidad
    ADD CONSTRAINT restriccion_real_de_especialidad_restriccion_nominal_fkey FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_especialidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2674 (class 2606 OID 27654)
-- Dependencies: 1981 1933 2408
-- Name: restriccion_real_de_lugar_objeto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_lugar
    ADD CONSTRAINT restriccion_real_de_lugar_objeto_fkey FOREIGN KEY (objeto) REFERENCES lugar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2675 (class 2606 OID 27659)
-- Dependencies: 1981 2460 1956
-- Name: restriccion_real_de_lugar_periodicidad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_lugar
    ADD CONSTRAINT restriccion_real_de_lugar_periodicidad_fkey FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2676 (class 2606 OID 27664)
-- Dependencies: 1981 1967 2496
-- Name: restriccion_real_de_lugar_restriccion_nominal_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_lugar
    ADD CONSTRAINT restriccion_real_de_lugar_restriccion_nominal_fkey FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_lugar(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2677 (class 2606 OID 27669)
-- Dependencies: 2452 1953 1982
-- Name: restriccion_real_de_otro_recurso_objeto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_otro_recurso
    ADD CONSTRAINT restriccion_real_de_otro_recurso_objeto_fkey FOREIGN KEY (objeto) REFERENCES otro_recurso(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2678 (class 2606 OID 27674)
-- Dependencies: 1982 1956 2460
-- Name: restriccion_real_de_otro_recurso_periodicidad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_otro_recurso
    ADD CONSTRAINT restriccion_real_de_otro_recurso_periodicidad_fkey FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2679 (class 2606 OID 27679)
-- Dependencies: 1982 2500 1968
-- Name: restriccion_real_de_otro_recurso_restriccion_nominal_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_otro_recurso
    ADD CONSTRAINT restriccion_real_de_otro_recurso_restriccion_nominal_fkey FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_otro_recurso(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2680 (class 2606 OID 27684)
-- Dependencies: 1983 2416 1936
-- Name: restriccion_real_de_periodo_objeto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_periodo
    ADD CONSTRAINT restriccion_real_de_periodo_objeto_fkey FOREIGN KEY (objeto) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2681 (class 2606 OID 27689)
-- Dependencies: 2460 1956 1983
-- Name: restriccion_real_de_periodo_periodicidad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_periodo
    ADD CONSTRAINT restriccion_real_de_periodo_periodicidad_fkey FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2682 (class 2606 OID 27694)
-- Dependencies: 1983 2504 1969
-- Name: restriccion_real_de_periodo_restriccion_nominal_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_periodo
    ADD CONSTRAINT restriccion_real_de_periodo_restriccion_nominal_fkey FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2683 (class 2606 OID 27699)
-- Dependencies: 2468 1959 1984
-- Name: restriccion_real_de_profesor_objeto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_profesor
    ADD CONSTRAINT restriccion_real_de_profesor_objeto_fkey FOREIGN KEY (objeto) REFERENCES profesor(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2684 (class 2606 OID 27704)
-- Dependencies: 1984 2460 1956
-- Name: restriccion_real_de_profesor_periodicidad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_profesor
    ADD CONSTRAINT restriccion_real_de_profesor_periodicidad_fkey FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2685 (class 2606 OID 27709)
-- Dependencies: 2506 1970 1984
-- Name: restriccion_real_de_profesor_restriccion_nominal_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY restriccion_real_de_profesor
    ADD CONSTRAINT restriccion_real_de_profesor_restriccion_nominal_fkey FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_profesor(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2686 (class 2606 OID 27714)
-- Dependencies: 2428 1942 1986
-- Name: zpriori_clasificacion_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY zpriori
    ADD CONSTRAINT zpriori_clasificacion_fkey FOREIGN KEY (clasificacion) REFERENCES clasificacion_de_actividad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2687 (class 2606 OID 27719)
-- Dependencies: 2416 1936 1986
-- Name: zpriori_periodo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rafa
--

ALTER TABLE ONLY zpriori
    ADD CONSTRAINT zpriori_periodo_fkey FOREIGN KEY (periodo) REFERENCES periodo(id) ON UPDATE CASCADE ON DELETE CASCADE;


SET search_path = seguridad, pg_catalog;

--
-- TOC entry 2688 (class 2606 OID 27724)
-- Dependencies: 2550 1992 1987
-- Name: host_usuario_fkey; Type: FK CONSTRAINT; Schema: seguridad; Owner: rafa
--

ALTER TABLE ONLY host
    ADD CONSTRAINT host_usuario_fkey FOREIGN KEY (usuario) REFERENCES usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2689 (class 2606 OID 27729)
-- Dependencies: 2544 1989 1988
-- Name: permiso_rol_fkey; Type: FK CONSTRAINT; Schema: seguridad; Owner: rafa
--

ALTER TABLE ONLY permiso
    ADD CONSTRAINT permiso_rol_fkey FOREIGN KEY (rol) REFERENCES rol(nombre) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2690 (class 2606 OID 27734)
-- Dependencies: 1989 2544 1990
-- Name: rol_de_usuario_rol_fkey; Type: FK CONSTRAINT; Schema: seguridad; Owner: rafa
--

ALTER TABLE ONLY rol_de_usuario
    ADD CONSTRAINT rol_de_usuario_rol_fkey FOREIGN KEY (rol) REFERENCES rol(nombre) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2691 (class 2606 OID 27739)
-- Dependencies: 2550 1990 1992
-- Name: rol_de_usuario_usuario_fkey; Type: FK CONSTRAINT; Schema: seguridad; Owner: rafa
--

ALTER TABLE ONLY rol_de_usuario
    ADD CONSTRAINT rol_de_usuario_usuario_fkey FOREIGN KEY (usuario) REFERENCES usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2692 (class 2606 OID 27744)
-- Dependencies: 1992 1991 2550
-- Name: traza_usuario_fkey; Type: FK CONSTRAINT; Schema: seguridad; Owner: rafa
--

ALTER TABLE ONLY traza
    ADD CONSTRAINT traza_usuario_fkey FOREIGN KEY (usuario) REFERENCES usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2770 (class 0 OID 0)
-- Dependencies: 10
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


-- Completed on 2010-10-06 09:19:07

--
-- PostgreSQL database dump complete
--

