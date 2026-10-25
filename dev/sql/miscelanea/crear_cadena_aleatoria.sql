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
    RETURN cadena;
END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION crear_cadena_aleatoria (VARCHAR, INTEGER) IS 
    'Devuelve una cadena de caracteres alfanum?ricos aleatorios';
