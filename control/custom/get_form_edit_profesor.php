<?php
	import("model.data.ProfesorModel");
	import("model.data.Profesor");
	import("model.data.ProfesorCollection");
	
	$id = $_POST['id'];
	
	$col = new ProfesorCollection($dbconnection);
	
	$p = $col->getItem($id);
	
	$for_new = false;
		
	$nombre = $p->nombre;
	
	include "../view/custom/form_profesor.tpl";
?>