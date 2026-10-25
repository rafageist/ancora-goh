<?php
/**
  * Class Common Time Utils
  */

class TimeUtilsCommon {

     /**
       * @desc Return true if is a leap year
       */
    public static function is_leap_year($pyear){
        return ($pyear % 4 == 0 && $pyear % 100 != 0 || $pyear % 400 == 0);
    }
    
    /**
      * @desc Convert hour to number
      */ 
    public static function hour_to_number($h, $m, $s){
            return $h * 60 + $m + $s * 0.01;
      }
      
    /**
      * @desc Return count of month
      */
    public static function countdays_of_month($m){
        $d = 31;
        if ($m == 4 || $m == 6 || $m == 9 || $m == 11) $d = 30;
        if ($m == 2) $d = 28;
        return $d;
    }
    
    /**
      * @desc Consecutive number of day
      * Returns the consecutive number of day
      */
    public static function day_number($d, $m, $a){
    
        $day_began = array(0 => 0,
            1 => 1,
            2 => 31 + 1,
            3 => 31 + 28 +1,
            4 => 31 + 28 + 31 +1,
            5 => 31 + 28 + 31 + 30 + 1,
            6 => 31 + 28 + 31 + 30 + 31 + 1,
            7 => 31 + 28 + 31 + 30 + 31 + 30 + 1,
            8 => 31 + 28 + 31 + 30 + 31 + 30 + 31 + 1,
            9 => 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 1,
           10 => 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 1,
           11 => 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31 + 1,
           12 => 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31 + 30 + 1
           );
           
        // cantidad de d�as antes del mes a partir del 1ero de enero
        $cd = $day_began[$m] + $d-1;
              
        //si es bisiesto y la fecha es despues de febrero se le aumenta un dia
        if (self::is_leap_year($a) == true && $m > 2) $cd++;
        
        return $cd;
    }
    
    /**
      * Month from consecutive number of date
      * Returns month from consecutive number of date
      */
      
    public static function month_from_day_number($n, $a){
        
        $x = (self::is_leap_year($a)?1:0);
        
        $cd = 1 + ($n > 31?1:0) + ($n > 59 + $x?1:0) + ($n > 90 + $x?1:0) + ($n > 120 + $x?1:0);
        $cd += ($n > 151 + $x?1:0) + ($n > 181 + $x?1:0) + ($n > 212 + $x?1:0) + ($n > 243 + $x?1:0);
        $cd += ($n > 273 + $x?1:0) + ($n > 304 + $x?1:0) + ($n > 334 + $x?1:0);

        return $cd;
    }
        
    /**
      * Consecutive number of date
      * Returns consecutive number of date
      */
    public static function date_to_number($d, $m, $a){
        // cantidad de d�as antes del mes a partir del 1ero de enero
        $cd = self::day_number($d,$m,$a);

        // cantidad de dias a partir del 1ero enero del 2000
        return intval(($a - 2000) * 365 + $cd  + ($a - 2000)/4 + (self::is_leap_year($a)==false?1:0));
    }
    
    /**
      * Return number of the moment
      */
    public static function moment_number($date_number, $hour_number){
        return ($date_number - 1) * 1439.60 + $hour_number;
    }
}
?>