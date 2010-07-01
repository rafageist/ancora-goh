<?php

	import("model.data.NivelModel");
	import("model.data.Nivel");
	import("model.data.NivelCollection");

	$for_new = false;
	
	$id = $_POST['id'];
	
	$c = new NivelCollection($dbconnection);
	
	$r = $c->getItem($id);
	
	$descripcion = $r->descripcion;
	
	include "../view/custom/form_nivel.tpl";

?>