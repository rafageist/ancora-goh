/**                                                                                                
    @name: iif                                                                                     
    @type: function                                                                                
    @language: SQL                                                                                 
    @description: Devuelve el primer elemento si la condicion es true y el segundo si es falsa     
    @note: Es similar a la instruccion IIF de Visual Basic y aloperador de ? de C++                
    @param $1: expresion booleana a evaluar                                                        
    @param $2: dato (de cualquier tipo) a retornar si la expresion $1 es verdadera                 
    @param $3: dato (de cualquier tipo) a retornar si la expresion $2 es falsa                     
    @example: SELECT iif(a = b, "a y b son iguales", "a y b son diferentes");                      
                                                                                                  */

CREATE OR REPLACE FUNCTION iif(
    expresion BOOLEAN, 
    dato1 ANYELEMENT, 
    dato2 ANYELEMENT
) 
RETURNS ANYELEMENT AS
$$ 
    SELECT CASE $1 WHEN TRUE THEN $2 ELSE $3 END;
$$
LANGUAGE 'sql';

COMMENT ON FUNCTION iif(BOOLEAN, ANYELEMENT, ANYELEMENT) IS 
    'Devuelve el primer elemento si la condicion es true y el segundo si es falsa';
