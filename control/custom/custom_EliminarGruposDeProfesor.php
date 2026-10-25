<?php
	import ("modelo.objetos.ProfesorParaGrupoModel");
		
	$m = new ProfesorParaGrupoModel($dbconnection);
	
	$idpxact = $_POST['pxact'];
	
	$m->removeBy('profesor_para_actividad = $1', false, array($idpxact));
	
?>