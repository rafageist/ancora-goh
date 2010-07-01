/**                                                                                                
    @name: bool_to_int                                                                             
    @type: function                                                                                
    @language: PL/PgSQL                                                                            
    @description: Convierte un valor BOOLEAN a un INTEGER donde 1 = TRUE y 0 = FALSE               
    @param valor: valor booleano a analizar                                                        
    @example: SELECT bool_to_int(true);                                                            
                                                                                                  */
 
CREATE OR REPLACE FUNCTION bool_to_int(
    valor BOOLEAN
) 
RETURNS INTEGER AS
$$
BEGIN 
    IF valor = TRUE THEN
        RETURN 1;
    END IF;
    RETURN 0;
END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION bool_to_int(BOOLEAN) IS 
    'Convierte un valor BOOLEAN a un entero donde 1 = TRUE y 0 = FALSE';