<?php

    $idplanif = $_POST['planificacion'];
    $idclasif = $_POST['clasificacion'];
    $idnivel = $_POST['nivel'];
    $idesp = $_POST['especialidad'];
    $idbrg = $_POST['brigada'];
    
    import("model.data.BrigadaDeGrupoModel");
    
    $m = new BrigadaDeGrupoModel($dbconnection);
    
    $m->removeBy("brigada = '$idbrg' AND dame_planificacion_de_grupo(grupo) = '$idplanif' AND dame_clasificacion_de_grupo(grupo) = '$idclasif' AND dame_nivel_de_grupo(grupo) = '$idnivel' AND dame_especialidad_de_grupo(grupo) = '$idesp'");

?>