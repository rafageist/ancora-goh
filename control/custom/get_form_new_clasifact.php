<?php

	import("model.data.ClasificacionDeActividadModel");
	import("model.data.ClasificacionDeActividad");
	import("model.data.ClasificacionDeActividadCollection");

	$for_new = true;
	
	$col = new ClasificacionDeActividadCollection($dbconnection);
	
	$col->Load(null,null,"*","creador = '{$_SESSION['user']}'");
		
	$prioridad = count($col->collection) + 1;

	include "../view/custom/form_clasif_act.tpl";
?>