<?php 

	import("model.data.DesgloseModel");
	import("model.data.PlantillaDeActividadModel");
	import("model.data.PeriodoModel");
	import("model.data.ClasificacionDeActividadModel");
	
	$idasig = $_POST['idasig'];
	$idplanif = $_POST['idplanificacion'];
	
	$m = new DesgloseModel($dbconnection);
	
	$desgs = $m->getAllBy("asignatura = '{$idasig}' AND dame_planificacion_de_periodo(periodo) = '{$idplanif}';");
	
	$plantillasModel = new PlantillaDeActividadModel($dbconnection);
	$periodosModel = new PeriodoModel($dbconnection);
	$clasifsModel = new ClasificacionDeActividadModel($dbconnection);
    
    include "../view/custom/desglose_detalles.tpl";
?>