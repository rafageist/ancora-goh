<?php
	
	import("model.data.PlanificacionModel");
	import("model.data.Planificacion");
	import("model.data.PlanificacionCollection");
	
	import("model.data.PeriodoModel");
	import("model.data.Periodo");
	import("model.data.PeriodoCollection");
	
	$planifs = array();
	
	$col = new PlanificacionCollection($dbconnection);
	
	$col->Load();
	
	if (is_array($col->collection)){
		
		$i = 0;
		foreach($col->collection as $item){	
			$planifs[$i] = $item;
			$planifs[$i]->periodos = $item->getPeriodo();
			$i++;
		}
		
	} 

	include "../view/custom/browser_publico.tpl";
?>