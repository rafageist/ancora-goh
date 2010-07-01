<?php 

	$for_new = false;
	
	import("model.data.BrigadaModel");
	import("model.data.Brigada");
	import("model.data.BrigadaCollection");
	
	$id = $_POST['id'];
	
	$c = new BrigadaCollection($dbconnection);
	
	$r = $c->getItem($id);
	
	$descripcion = $r->descripcion;
	$matricula = $r->matricula;
	
	include "../view/custom/form_brigada.tpl";

?>