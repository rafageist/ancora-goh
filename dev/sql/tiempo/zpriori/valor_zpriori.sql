-- zpriori
    -- clasificacion de actividad
    CREATE or replace function valor_zpriori(clasif VARCHAR, periodo VARCHAR, dia INTEGER, turno INTEGER) RETURNS INTEGER as
    $$
        SELECT valor from zpriori where clasificacion = $1 and periodo = $2 and dia = $3 and turno = $4
    $$
    LANGUAGE 'sql';