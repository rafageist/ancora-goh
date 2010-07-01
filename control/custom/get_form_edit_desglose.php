<?php 
	
	import("model.data.DesgloseModel");
	import("model.data.PlanificacionModel");
	import("model.data.AsignaturaModel");
	import("model.data.PeriodoModel");
	
	$id = $_POST['id'];
	
	$m = new DesgloseModel($dbconnection);
	
	$desglose = $m->get($id);
	
	$m = new PeriodoModel($dbconnection);
	
	$periodo = $m->get($desglose->periodo);

	$m = new AsignaturaModel($dbconnection);
	
	$asignatura = $m->get($desglose->asignatura);
	
	$m = new PlanificacionModel($dbconnection);
	
	$planificacion = $m->get($periodo->planificacion);
	
	include "../view/custom/form_desglose.tpl";
	
?>