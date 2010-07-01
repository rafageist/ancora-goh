<?php
	import("model.data.ClasificacionDeActividadModel");
	import("model.data.ClasificacionDeActividad");
	import("model.data.ClasificacionDeActividadCollection");
	
	$col = new ClasificacionDeActividadCollection($dbconnection);

	$id = $_POST['id'];
	
	$c = $col->getItem($id);
	
	$for_new = false;
	
	$descripcion = $c->descripcion;
	$duracion = $c->duracion;
	$prioridad = $c->prioridad;
	$gasto = $c->gasto;
	
	include "../view/custom/form_clasif_act.tpl";
?>