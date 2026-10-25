<?php

	import("model.data.LugarParaActividadModel");
	import("model.data.LugarModel");

	
	$idplantilla = $_POST['plantilla'];
	
	$m = new LugarParaActividadModel($dbconnection);
	
	$lxact = $m->getAllBy('plantilla = $1 ORDER BY prioridad', "*", false, array($idplantilla));
	
	$lmodel = new LugarModel($dbconnection);
	
	$lugs = array();
    $prioridades = array();
    
	if (is_array($lxact)){
		foreach ($lxact as $lx){
			$id = $lx['lugar'];
			$lugs[$id] = $lmodel->get($id);
            $prioridades[$id] = $lx['prioridad'];
		}
	}
	
	
	
	include "../view/custom/lugares_asignados.tpl";

?>