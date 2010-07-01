CREATE OR REPLACE FUNCTION dame_matricula_total(brigadas VARCHAR[]) RETURNS BIGINT AS
$$
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
$$
LANGUAGE 'plpgsql';