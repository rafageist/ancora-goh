<?php

	import("model.data.PublicFunctions");
	import("model.data.ProfesorModel");
	
	$idplantilla = $_POST['plantilla'];
	
	$m = new PublicFunctions($dbconnection);
	
	$idpfs = $m->DameProfesoresNoAsignadosA($idplantilla);
	
	$profesores_disponibles = array();
	
	if (is_array($idpfs)){
			
			$pmodel  = new ProfesorModel($dbconnection);
			
			foreach($idpfs as $idpf){
					$id = $idpf['dame_profesores_no_asignados_a'];
					$profesores_disponibles[$id] = $pmodel->get($id);
			}
			
	}

	include "../view/custom/nuevo_profesor_para_actividad.tpl";

?>