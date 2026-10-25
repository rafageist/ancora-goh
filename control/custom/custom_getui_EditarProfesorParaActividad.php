<?php
    
    import("model.data.ProfesorParaActividadModel");
    import("model.data.ProfesorParaGrupoModel");
    import("model.data.GrupoModel");
    import("model.data.PublicFunctions");
    
    $idpxact = $_POST['pxact'];
    
    $m = new ProfesorParaActividadModel($dbconnection);
    $pxact = $m->get($idpxact);
    
    $m = new ProfesorParaGrupoModel($dbconnection);
    $pxgrp = $m->getAllBy('profesor_para_actividad = $1', "*", false, array($idpxact));
    
    $m = new PublicFunctions($dbconnection);
    
    $r = $m->DameMaxPrioridadDePxact($pxact->plantilla);

    $max_priority = $r[0]['dame_max_prioridad_de_pxact'];
    
    $r = $m->DameGruposDePlantilla($pxact->plantilla);
    
    $grupos = array();
    
    if (is_array($r)){
    	foreach ($r as $k=>$item){
    		$grupos[$item['id']] = $item;		
    		$grupos[$item['id']]['checked'] = false;
    	}
    }
    
    $brigadas = array();
    if (is_array($grupos)){
    	foreach($grupos as $gp)
    		$brigadas[$gp['id']] = $m->DameBrigadasDeGrupo($gp['id']);
    }
    
    
    if (is_array($pxgrp)){
    	foreach($pxgrp as $px){
    		$grupos[$px['grupo']]['checked'] = true;
    	}
    }
    
       
    include "../view/custom/form_edit_pxact.tpl";

?>