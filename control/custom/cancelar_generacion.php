<?php

	import("model.generator.CancelarGeneradorModel");
	import("model.generator.CancelarGenerador");
		
	$esp = $_POST['esp'];
	$nivel = $_POST['nivel'];
	$periodo = $_POST['periodo'];
	
	$obj =  new CancelarGenerador($esp, $nivel, $periodo, true, false, false);
	
?>