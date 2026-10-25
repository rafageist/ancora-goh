<?php

	import("model.data.PlanificacionModel");
	import("model.data.Planificacion");
	import("model.data.PlanificacionCollection");
	
	$coll = new PlanificacionCollection($dbconnection);
	
	$coll->Load(null, null, "*", 'creador = $1', array($_SESSION['user']));
	
	$planifs = $coll->collection;
	
	include "../view/custom/minibrowser_desgloses_planificaciones.tpl";

?>