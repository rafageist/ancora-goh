CREATE or replace function check_hrt(hijo_brigada VARCHAR, hijo_profesor VARCHAR, hijo_lugar VARCHAR, hijo_otro_recurso VARCHAR, 
                     padre_brigada VARCHAR, padre_profesor VARCHAR, padre_lugar VARCHAR, padre_otro_recurso VARCHAR 
                    ) RETURNS BOOLEAN as
$$
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
$$
LANGUAGE 'plpgsql';