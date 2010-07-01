<?php
	import("model.data.OtroRecursoModel");
	import("model.data.OtroRecurso");
	import("model.data.OtroRecursoCollection");
	
	$id = $_POST['id'];
	
	$col = new OtroRecursoCollection($dbconnection);
	
	$p = $col->getItem($id);
	
	$for_new = false;
		
	$nombre = $p->nombre;
	
	include "../view/custom/form_otro_recurso.tpl";
?>