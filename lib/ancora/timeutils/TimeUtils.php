<?php
  
  import("lib.ancora.timeutils.TimeUtilsCommon");
  import("lib.ancora.timeutils.THour");
  import("lib.ancora.timeutils.TDate");
  
  /**
  * @desc Class Time Utility
  */
  class TimeUtils extends TimeUtilsCommon{

    /**
      * @desc Convert number to hour
      */ 
    public static function number_to_hour($n){
        
        $t = new THour(intval(intval($n)/60), intval($n)-intval(intval($n)/60)*60, intval(($n-intval($n))*100));
	
		if ($t->toNumber() != $n){
			$t->s++;
		}
		return $t;
	}

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
      * Esta funcion fue hecha el 13 de Diciembre del 2009, durante una lluvia de estrellas
      * Curioso saber que date_to_number(13,12,2009) = 4000
      */
    public static function number_to_date($n){

        if ($n <= 366) $a = 2000; else $a = intval(2000 + ($n / 365.25));
        
        if ($n <= 366) $cd = $n; else $cd = $n - parent::date_to_number(31, 12, $a - 1);
        
		$m = parent::month_from_day_number($cd, $a);
		
        $d = $cd - parent::day_number(1, $m, $a) + 1;
        
		$b = parent::date_to_number($d, $m, $a) - $n;
        
        if ($d==0) {$a--; $m = 12; $d = 31;}
        else
		if ($b > 0){
			// retrasar un dia
			if ($d == 1 && $m == 1) {
				$a--;
				$m = 12;
				$d = 31;
			} else {
				if ($d == 1) {
					$m--;
					$d = parent::countdays_of_month($m);
                } else 
					$d--;
            }
        }		

        $f = new TDate($d, $m, $a);
        
		return $f;
	}

}
?>