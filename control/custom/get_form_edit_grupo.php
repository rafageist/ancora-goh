<?php

    import("model.data.GrupoModel");
    
    $m = new GrupoModel($dbconnection);
    
    $id  = $_POST['grupo'];
    $grupo = $m->get($id);
    $idclasif = $grupo->clasificacion;
    $descripcion = $grupo->descripcion;
        
    $for_new = false;
    include "../view/custom/form_grupo.tpl";

?>