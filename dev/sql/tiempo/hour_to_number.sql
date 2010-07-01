-- Convert hour to number
DROP function IF EXISTS tiempo.hour_to_number(INTEGER,INTEGER,INTEGER) CASCADE; 
CREATE OR REPLACE FUNCTION tiempo.hour_to_number(h INTEGER, m INTEGER, s INTEGER) RETURNS DOUBLE PRECISION as
$$
    BEGIN
        RETURN h * 36 + m * 0.6 + s * 0.01;
    END;
$$
LANGUAGE 'plpgsql';

-- SELECT * from tiempo.hour_to_number(23,59,59);

COMMENT ON FUNCTION tiempo.hour_to_number(INTEGER,INTEGER,INTEGER) is 'Convert hour to number';