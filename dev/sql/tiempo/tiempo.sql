-- ------------------------------------------
-- FUNCIONES PARA EL TRABAJO CON EL TIEMPO
-- ------------------------------------------

DROP schema IF EXISTS tiempo CASCADE;
CREATE schema tiempo;

-- SELECT tiempo.date_to_number(31,12,2000);
-- SELECT tiempo.day_number(31,12,2000);
-- SELECT 31+29+31+30+31+30+31+31+30+31+30+31;
-- SELECT tiempo.is_leap_year(2000);
-- SELECT tiempo.is_leap_year(2000) = FALSE
-- SELECT tiempo.moment_number(4000,1382.18);
-- SELECT tiempo.hour_to_number(23,59,59);
-- SELECT tiempo.number_to_date(4000);
-- SELECT now();
-- SELECT tiempo.date_to_number(30,9,2009);