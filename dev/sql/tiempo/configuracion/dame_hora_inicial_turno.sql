/*

    hi := (turno-1) * rperiodo.duracion_del_turno;
    hf := hi + rperiodo.duracion_del_turno;

    ti := (hi - per1.hora_inicial) / per1.duracion + 1;
    tf := (hf - per1.hora_inicial) / per1.duracion + 1;
*/
-- devuelve la hora inicial de un turno teniendo en cuenta tambien los recesos
CREATE or replace function dame_hora_inicial_turno(idperiodo VARCHAR, pturno INTEGER) RETURNS DOUBLE PRECISION as
$$
    DECLARE r record;
        h DOUBLE PRECISION;
    BEGIN
        SELECT * into r from periodo where id = idperiodo;
        h := r.hora_inicial + (pturno - 1) * r.duracion_del_turno;

        FOR r IN SELECT * from receso where periodo = idperiodo and despues_del_turno < pturno LOOP
            h := h + r.duracion;
        END LOOP;
        
        RETURN h;

    END;
$$
LANGUAGE 'plpgsql';
