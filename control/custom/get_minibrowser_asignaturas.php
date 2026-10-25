<?php
	import("model.data.EspecialidadModel");
	import("model.data.NivelModel");
	import("model.data.AsignaturaModel");

	
	$m = new EspecialidadModel($dbconnection);
	$especs = $m->getAllBy('creador = $1 ORDER BY descripcion', "*", false, array($_SESSION['user']));
	
	$m = new NivelModel($dbconnection);
	$niveles = $m->getAllBy('creador = $1 ORDER BY descripcion', "*", false, array($_SESSION['user']));

	$asigmodel = new AsignaturaModel($dbconnection);	
	
	include "../view/custom/minibrowser_asignaturas.tpl";
?>