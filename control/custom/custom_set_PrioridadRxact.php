<?php

	import("model.data.OtroGrupoDeRecursosParaActividadModel");
	
	$idrxact =  $_POST['rxact'];
	$prioridad = $_POST['prioridad'];
	
	$m = new OtroGrupoDeRecursosParaActividadModel($dbconnection);
	
	$m->setBy('id = $1', 'prioridad = $2', false, array($idrxact, $prioridad));
?>