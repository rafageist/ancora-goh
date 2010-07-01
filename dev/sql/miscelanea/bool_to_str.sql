/**                                                                                                
    @name: bool_to_int                                                                             
    @type: function                                                                                
    @language: PL/PgSQL                                                                            
    @description: Convierte un valor BOOLEAN a un VARCHAR donde 'TRUE' = TRUE y 'FALSE' = FALSE    
    @param valor: valor booleano a analizar                                                        
    @example: SELECT bool_to_str(true);                                                            
                                                                                                  */

CREATE OR REPLACE FUNCTION bool_to_str(
    valor BOOLEAN
) 
RETURNS VARCHAR AS
$$
BEGIN 
    IF valor = TRUE THEN
        RETURN 'TRUE';
    END IF;
    RETURN 'FALSE';
END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION bool_to_str(BOOLEAN) IS
    'Convierte un valor BOOLEAN a un VARCHAR donde "TRUE" = TRUE y "FALSE" = FALSE';
