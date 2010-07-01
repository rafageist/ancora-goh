<?php
   
    /*
     $adminpath = "../";
    include "../lib/console.lib";

    $console = new Console();

    print_r($console->arguments);
*/
//$a =50;
//echo ($a > 122);

$adminpath = "../";
include "../lib/timeutils.lib";

//echo TimeUtilsCommon::is_leap_year(2000);   echo "\n";
//echo TimeUtilsCommon::hour_to_number(2,2,34); echo "\n";
//echo TimeUtilsCommon::countdays_of_month(1);  echo "\n";
//echo TimeUtilsCommon::day_number(31,12,2000);   echo "\n";
//$d = new TDate(31,12,2000);
//echo $d->toNumber();

//$h = new THour(23,59,59);
//echo $h->toNumber();

//echo TimeUtilsCommon::month_from_day_number(31+29,2001);   echo "\n";


//echo TimeUtilsCommon::moment_number(1,0);   echo "\n";

//print_r(TimeUtils::number_to_hour(323.02));   echo "\n";
/*
for ($i=1;$i<30;$i++){
    $d = TimeUtils::number_to_date($i);
    echo $i." - ".$d->d."/".$d->m."/".$d->a."\n";
}*/
/*
for ($a = 2000; $a <= 2100; $a++){
    for ($m = 1; $m <= 12; $m++)
        for ($d = 1; $d <= TimeUtilsCommon::countdays_of_month($m); $d++){
            $n = TimeUtilsCommon::date_to_number($d,$m,$a);
            $dt = TimeUtils::number_to_date($n);
            if ($dt->d<>$d || $dt->m<>$m || $dt->a<>$a){
                echo "$d/$m/$a = $n ".$dt->d."/".$dt->m."/".$dt->a." \n"; 
                //print_r($dt);
            }
        }
        echo $a."\n";
}*/
        /*
for ($a=2000; $a<2020; $a++)
    echo "$a = del ".TimeUtilsCommon::date_to_number(1,1,$a)." al ".TimeUtilsCommon::date_to_number(31,12,$a)."\n"; 
*/
//echo TimeUtilsCommon::day_number(31,12,2000);

print_r(TimeUtils::number_to_hour(101.22));
?>