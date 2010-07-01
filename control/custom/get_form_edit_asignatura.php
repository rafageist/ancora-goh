<?php

	import("model.data.AsignaturaModel");
	import("model.data.Asignatura");
	import("model.data.AsignaturaCollection");
	
	$for_new = false;
	
	$col = new AsignaturaCollection($dbconnection);
	
	$id = $_POST['id'];
	
	$r = $col->getItem($id);
	
	$descripcion = $r->descripcion;
	
	$especialidad = $r->especialidad;
	$nivel = $r->nivel;
	
	include "../view/custom/form_asignatura.tpl";
	
?>