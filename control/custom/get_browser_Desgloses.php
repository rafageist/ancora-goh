<?php 

	import("model.data.EspecialidadModel");
	import("model.data.NivelModel");
	import("model.data.AsignaturaModel");
	import("model.data.PlanificacionModel");
	import("model.data.Planificacion");
	import("model.data.PlanificacionCollection");
	
	$m = new EspecialidadModel($dbconnection);
	$especs = $m->getAllBy('creador = $1 ORDER BY descripcion', "*", false, array($_SESSION['user']));
	
	$m = new NivelModel($dbconnection);
	$niveles = $m->getAllBy('creador = $1 ORDER BY descripcion', "*", false, array($_SESSION['user']));

	$asigmodel = new AsignaturaModel($dbconnection);	
	
	$coll = new PlanificacionCollection($dbconnection);
	
	$coll->Load(null, null, "*", 'creador = $1', array($_SESSION['user']));
		
	$planifs = $coll->collection;
	
	include "../view/custom/browser_desgloses.tpl";

?>