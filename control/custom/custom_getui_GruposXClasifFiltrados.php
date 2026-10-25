<?php

    import("model.objetos.BrigadaDeGrupoModel");
    import("model.objetos.BrigadaDeGrupo");
    import("model.objetos.BrigadaDeGrupoCollection");
    import("model.PublicFunctions");
    import("model.objetos.BrigadaModel");
    import("model.objetos.GrupoModel");
    import("model.objetos.Grupo");
    import("model.objetos.GrupoCollection");
    
    import("modelo.actividades.ClasificacionDeActividadModel");
    
    $idesp = $_POST['especialidad'];
    $idnivel = $_POST['nivel'];
    $idclasif = $_POST['clasificacion'];
    $idplanif = $_POST['planificacion'];
    
    $brgmodel = new BrigadaModel($dbconnection);
    $grupomodel = new GrupoModel($dbconnection);
                
    $grupos = $grupomodel->getAllBy('especialidad = $1 AND nivel = $2 AND clasificacion = $3 AND planificacion = $4', "*", false, array($idesp, $idnivel, $idclasif, $idplanif));

    $m = new PublicFunctions($dbconnection);
    
    $brgs_sin_grupo = $m->DameBrigadasSinGrupo($idesp, $idnivel, $idclasif, $idplanif);
    
    include "../tpl/grupos_filtrados.tpl";
    
?>
