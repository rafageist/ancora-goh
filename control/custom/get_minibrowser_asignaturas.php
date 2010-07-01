<?php
	import("model.data.EspecialidadModel");
	import("model.data.NivelModel");
	import("model.data.AsignaturaModel");

	
	$m = new EspecialidadModel($dbconnection);
	$especs = $m->getAllBy("creador = '{$_SESSION['user']}' ORDER BY descripcion");
	
	$m = new NivelModel($dbconnection);
	$niveles = $m->getAllBy("creador = '{$_SESSION['user']}'  ORDER BY descripcion");

	$asigmodel = new AsignaturaModel($dbconnection);	
	
	include "../view/custom/minibrowser_asignaturas.tpl";
?>