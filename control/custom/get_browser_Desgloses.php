<?php 

	import("model.data.EspecialidadModel");
	import("model.data.NivelModel");
	import("model.data.AsignaturaModel");
	import("model.data.PlanificacionModel");
	import("model.data.Planificacion");
	import("model.data.PlanificacionCollection");
	
	$m = new EspecialidadModel($dbconnection);
	$especs = $m->getAllBy("creador = '{$_SESSION['user']}' ORDER BY descripcion");
	
	$m = new NivelModel($dbconnection);
	$niveles = $m->getAllBy("creador = '{$_SESSION['user']}' ORDER BY descripcion");

	$asigmodel = new AsignaturaModel($dbconnection);	
	
	$coll = new PlanificacionCollection($dbconnection);
	
	$coll->Load(null,null,"*","creador = '{$_SESSION['user']}'");
		
	$planifs = $coll->collection;
	
	include "../view/custom/browser_desgloses.tpl";

?>