<?php

    import("model.data.BrigadaDeGrupoModel");
    import("model.data.BrigadaDeGrupo");
    import("model.data.BrigadaDeGrupoCollection");
    import("model.data.PublicFunctions");
    import("model.data.BrigadaModel");
    import("model.data.GrupoModel");
    import("model.data.Grupo");
    import("model.data.GrupoCollection");
    import("model.data.ClasificacionDeActividadModel");
    
    $idesp = $_POST['especialidad'];
    $idnivel = $_POST['nivel'];
    $idclasif = $_POST['clasificacion'];
    $idplanif = $_POST['planificacion'];
    
    $brgmodel = new BrigadaModel($dbconnection);
    $grupomodel = new GrupoModel($dbconnection);
                
    $grupos = $grupomodel->getAllBy('especialidad = $1 AND nivel = $2 AND clasificacion = $3 AND planificacion = $4 ORDER BY descripcion', "*", false, array($idesp, $idnivel, $idclasif, $idplanif));

    $m = new PublicFunctions($dbconnection);
    
    $brgs_sin_grupo = $m->DameBrigadasSinGrupo($idesp, $idnivel, $idclasif, $idplanif);
    
    include "../view/custom/browser_grupos_filtrados.tpl";
    
?>
