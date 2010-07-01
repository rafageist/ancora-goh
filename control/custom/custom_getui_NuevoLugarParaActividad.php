<?php

	import("model.data.PublicFunctions");
	
	$idplantilla = $_POST['plantilla'];
	
	$m = new PublicFunctions($dbconnection);
	 
	$lugares_disponibles = $m->DameLugaresNoAsignadosA($idplantilla, true);
	
	include "../view/custom/nuevo_lugar_para_actividad.tpl";

?>