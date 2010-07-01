<?php
	import("model.data.ProfesorParaGrupoModel");
	
	$m = new ProfesorParaGrupoModel($dbconnection);
	
	$idpxact = $_POST['pxact'];
	
	$idgrupo = $_POST['grupo'];
	
	$r = $m->get($idpxact, $idgrupo);
	
	if ($r==false) $m->add($idpxact, $idgrupo);
	
?>