<?php

    import("modelo.objetos.BrigadaDeGrupoModel");
    import("modelo.actividades.ClasificacionDeActividadModel");
    
    $idesp = $_POST['especialidad'];
    $idnivel = $_POST['nivel'];
    
    $brggrupomodel = new BrigadaDeGrupoModel($dbconnection);
    $clasifmodel = new ClasificacionDeActividadModel($dbconnection);
    
    $clasifs = $clasifmodel->getAllBy("creador = '{$_SESSION['user']}'");
    
    include "../tpl/grupos.tpl";
    
?>