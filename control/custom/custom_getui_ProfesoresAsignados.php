<?php

	$idplantilla = $_POST['plantilla'];
		
	import("model.data.ProfesorParaActividadModel");
	import("model.data.ProfesorModel");
		
	$m = new ProfesorParaActividadModel($dbconnection);

	$pxact = $m->getAllBy("plantilla = '$idplantilla'");
	
	$profemodel = new ProfesorModel($dbconnection);
	
	$profesores = array();
	if (is_array($pxact)) 
		foreach ($pxact as $p){
				$pp = $profemodel->get($p['profesor']);
				$profesores[$p['id']] = $pp;
				$profesores[$p['id']]->prioridad = $p['prioridad'];
			}
	include '../view/custom/profesores_asignados.tpl';
	
?>