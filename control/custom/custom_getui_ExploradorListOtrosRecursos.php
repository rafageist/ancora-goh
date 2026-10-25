<?php

	import("model.data.OtroRecursoModel");
	
	$m = new OtroRecursoModel($dbconnection);
	
	$recursos = $m->getAllBy('creador = $1', "*", false, array($_SESSION['user']));
	
	include "../view/custom/explorador_list_otros_recursos.tpl";

?>