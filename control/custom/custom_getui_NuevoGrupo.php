<?php

    $clasificacion = $_POST['clasificacion'];
    $nivel = $_POST['nivel'];
    $especialidad = $_POST['especialidad'];
    $planificacion = $_POST['planificacion'];
    
    $nuevo_grupo = true;
    
    include "../tpl/form_grupo.tpl";

?>