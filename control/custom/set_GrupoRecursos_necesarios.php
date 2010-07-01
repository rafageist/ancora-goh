<?php

	import("model.data.OtroGrupoDeRecursosParaActividadModel");
	import("model.data.OtroGrupoDeRecursosParaActividad");
	import("model.data.OtroGrupoDeRecursosParaActividadCollection");
	
	$id = $_POST['id'];
	$nec = $_POST['nec'];
	
	$col = new OtroGrupoDeRecursosParaActividadCollection($dbconnection);
	
	$gr = $col->getItem($id);
	
	$gr->setNecesarios($nec);
	
?>