
-- ----------------------------------------------------------------------------------------------
-- Devuelve si en un (dia,turno) de un per?o, est?estringido en una plantilla del per?o
-- ----------------------------------------------------------------------------------------------
-- El problema se da puesto que ambos periodos pueden tener configuraciones de tiempo distintas
-- y por lo tanto no se puede no corresponder el turno de un periodo con el turno de su plantilla
-- La solucion consiste partiendo del tiempo nominal del per1, llevarlo a su tiempo real, y luego
-- este llevarlo al tiempo nominal de per2 y ver si est? no restringido.
/*
-- Por ejemplo:
-- Periodo 1: 5 dias x 6 turnos, hora inicial 08:00:00 duraci??1:30:00

    Turno   Hora inicio del turno
    -----------------------------
    1    08:00:00 hrs
    2    09:30:00 hrs
    3    11:00:00 hrs
    4    13:30:00 hrs
    5    15:00:00 hrs
    6    16:30:00 hrs
    -----------------------------
    fin    17:59:59 hrs
    
-- Periodo 2(plantilla): 7 dias x 7 turnos, hora inicial 09:00:00 duraci??0:45:00

    Turno   Hora inicio del turno
    -----------------------------
    1    09:00:00 hrs
    2    09:45:00 hrs
    3    10:30:00 hrs
    4    11:15:00 hrs
    5    12:00:00 hrs
    6    12:45:00 hrs
    7    13:30:00 hrs
    -----------------------------
    fin    14:14:59 hrs

-- Encuestar dia 2 turno 2

-- El turno 2 del d?2 del Per?o 1 es de 11:00:00 hrs hasta 13:29:59 hrs
-- Entonces llevando a tiempo nominal del Per?o 2 ser? los turnos 3,4,5,6 del d?2
   pues las 11:00:00 hrs est?n el turno 3 del Per?o 2 y 13:29:59 hrs est?n el turno 6

   Para saber en que turno cae una hora se utiliza la formula

   turno := (hora - hora_inicial) / duracion + 1;

   donde hora_inicial es la hora de comienzo de la jornada en el per?o y duracion es 
   la duraci??el turno.
     

*/

CREATE OR REPLACE FUNCTION valor_rest_entre_config_distintas(rests INTEGER[][], per1 periodo, per2 periodo, dia INTEGER, turno INTEGER) RETURNS INTEGER as
$$
    DECLARE hi DOUBLE PRECISION; hf DOUBLE PRECISION;
        ti INTEGER; tf INTEGER;
        i INTEGER;
    BEGIN
        hi := (turno-1) * per1.duracion_del_turno;
        hf := hi + per1.duracion_del_turno;

        ti := (hi - per2.hora_inicial) / per2.duracion_del_turno + 1;
        tf := (hf - per2.hora_inicial) / per2.duracion_del_turno + 1;

        i := ti;
        LOOP
            IF rests[dia][i] < 1 THEN
                RETURN rests[dia][i];
            END IF;
            EXIT WHEN i = tf;
            i := i + 1;
        END LOOP;
        RETURN 0;
    END;
$$
LANGUAGE 'plpgsql';