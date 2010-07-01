<?php

	import("model.data.OtroGrupoDeRecursosParaActividadModel");
	
	$idrxact =  $_POST['rxact'];
	$prioridad = $_POST['prioridad'];
	
	$m = new OtroGrupoDeRecursosParaActividadModel($dbconnection);
	
	$m->setBy("id='$idrxact'","prioridad=$prioridad");
?>