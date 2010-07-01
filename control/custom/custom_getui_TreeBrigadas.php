<?php
	
	import("model.data.EspecialidadModel");
	import("model.data.NivelModel");
	import("model.data.BrigadaModel");
	
	$especs = new EspecialidadModel($dbconnection);
	$niveles = new NivelModel($dbconnection);
	$brgs = new BrigadaModel($dbconnection);
	
	include "../view/custom/tree_brigadas.tpl";
	
?>