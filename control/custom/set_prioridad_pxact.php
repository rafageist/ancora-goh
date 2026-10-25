<?php
	
	import("model.data.ProfesorParaActividadModel");
	
	$m = new ProfesorParaActividadModel($dbconnection);
		
	$idpxact = $_POST['pxact'];
	
	$prioridad = $_POST['prioridad'];

	$m->setBy('id = $1', 'prioridad = $2', false, array($idpxact, $prioridad));

?>