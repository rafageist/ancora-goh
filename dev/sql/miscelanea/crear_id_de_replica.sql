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
    'Crea una cadena de caracteres ?nica para una columna de una tabla';