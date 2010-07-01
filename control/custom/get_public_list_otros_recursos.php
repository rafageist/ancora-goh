<?php

	import("model.data.OtroRecursoModel");
	
	$m = new OtroRecursoModel($dbconnection);
	
	$recursos = $m->getAllBy("creador = '{$_SESSION['user']}'");
	
	include "../view/custom/public_list_otros_recursos.tpl";

?>