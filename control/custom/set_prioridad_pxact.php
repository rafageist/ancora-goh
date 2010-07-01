<?php
	
	import("model.data.ProfesorParaActividadModel");
	
	$m = new ProfesorParaActividadModel($dbconnection);
		
	$idpxact = $_POST['pxact'];
	
	$prioridad = $_POST['prioridad'];

	$m->setBy("id = '$idpxact'","prioridad = $prioridad");

?>