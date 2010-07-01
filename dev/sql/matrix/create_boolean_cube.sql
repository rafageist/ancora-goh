-- EXAMPLE SELECT matrix.create_boolean_cube (3,3,3);
CREATE OR REPLACE FUNCTION matrix.create_boolean_cube(x BIGINT, y BIGINT, z BIGINT) RETURNS BOOLEAN[][][] as
$$
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
$$
LANGUAGE 'plpgsql';