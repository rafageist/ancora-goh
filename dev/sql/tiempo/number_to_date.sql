/**
  * Date of consecutive number
  * Esta funcion se equivocaba para una fecha superior al 31 de diciembre del 3452 
  * por la acumulacion de dias de los annos bisiestos.
  * Este error fue eliminado al cambiar la division entre 365 por 365.25
  * 0.25 se debe a dividir 1/4, es decir devidir el dia adicional de un anno bisiesto
  * en 4 y decir que todos los annos son de 365.25, 
  * para corregir el error que se introduce en el calculo del anno
  * cuando el d'ia, se hace 0, se cambia a 31 de diciembre del anno anterior.
  * Las comparaciones para n<366 se hacen para eliminar el error con la fecha 31/12/2000
  */

CREATE OR REPLACE FUNCTION tiempo.number_to_date(n BIGINT) RETURNS tiempo.ancora_date as
$$
    DECLARE f tiempo.ancora_date;
        cd INTEGER;
        b INTEGER;
    BEGIN
        IF (n<=366) THEN
            f.a = 2000;
        ELSE
            f.a := 2000 + trunc(n / 365.25);
        END IF;
        
        IF (n<=366) THEN
            cd := n;
        ELSE
            cd := n - tiempo.date_to_number(31, 12, f.a - 1);
        END IF;
        
        f.m := tiempo.month_from_day_number(cd, f.a);

        f.d := cd - tiempo.day_number(1, f.m, f.a) + 1;

        b:= tiempo.date_to_number(f.d,f.m,f.a) - n;

        IF f.d = 0 THEN
            f.a := f.a - 1;
            f.m := 12;
            f.d := 31;
        ELSE
            IF (b > 0) THEN
                -- retrasar un dia
                IF (f.d = 1 and f.m = 1) THEN
                    f.a := f.a - 1;
                    f.m := 12;
                    f.d := 31;
                ELSE
                    IF (f.d = 1) THEN
                        f.m := f.m - 1;
                        f.d := tiempo.countdays_of_month(f.m);
                    ELSE
                        f.d := f.d - 1;
                    END IF;
                END IF;
            END IF;
        END IF;    
        RETURN f;
    END;
$$
LANGUAGE 'plpgsql';

COMMENT ON FUNCTION tiempo.number_to_date(BIGINT) is 'RETURNS a date from consecutive number';
