<?php

    $idplanif = $_POST['planificacion'];
    $idclasif = $_POST['clasificacion'];
    $idnivel = $_POST['nivel'];
    $idesp = $_POST['especialidad'];
    $idbrg = $_POST['brigada'];
    
    import("model.data.BrigadaDeGrupoModel");
    
    $m = new BrigadaDeGrupoModel($dbconnection);
    
    $m->removeBy('brigada = $1 AND dame_planificacion_de_grupo(grupo) = $2 AND dame_clasificacion_de_grupo(grupo) = $3 AND dame_nivel_de_grupo(grupo) = $4 AND dame_especialidad_de_grupo(grupo) = $5', false, array($idbrg, $idplanif, $idclasif, $idnivel, $idesp));

?>