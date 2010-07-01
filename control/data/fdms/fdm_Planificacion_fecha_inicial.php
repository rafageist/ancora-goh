<?php

    import("lib.ancora.timeutils.TimeUtilsCommon");
    
    $arrexp = explode("/", $_POST['field_Planificacion_fecha_inicial']);
    
    $fecha_ini = TimeUtilsCommon::date_to_number(intval($arrexp[0]), intval($arrexp[1]), intval($arrexp[2]));
    
    $arrexp = explode("/", $_POST['edtFechaFinal']);
    
    $n = TimeUtilsCommon::date_to_number(intval($arrexp[0]), intval($arrexp[1]), intval($arrexp[2])) - $fecha_ini;
    
    $_POST['field_Planificacion_fecha_inicial'] = $fecha_ini;

    $_POST['field_Planificacion_cant_dias'] = $n + 1;
    
?>