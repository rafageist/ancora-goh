<?php
	import("model.data.LugarModel");
	import("model.data.Lugar");
	import("model.data.LugarCollection");
	
	$id = $_POST['id'];
	
	$col = new LugarCollection($dbconnection);
	
	$p = $col->getItem($id);
	
	$for_new = false;
		
	$nombre = $p->nombre;
	$capacidad = $p->capacidad;
	
	include "../view/custom/form_lugar.tpl";
?>