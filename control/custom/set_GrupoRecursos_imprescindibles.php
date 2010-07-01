<?php

	import("model.data.OtroGrupoDeRecursosParaActividadModel");
	import("model.data.OtroGrupoDeRecursosParaActividad");
	import("model.data.OtroGrupoDeRecursosParaActividadCollection");
	
	$id = $_POST['id'];
	$imp = $_POST['imp'];
	
	$col = new OtroGrupoDeRecursosParaActividadCollection($dbconnection);
	
	$gr = $col->getItem($id);
	
	$gr->setImprescindibles($imp);
	
?>