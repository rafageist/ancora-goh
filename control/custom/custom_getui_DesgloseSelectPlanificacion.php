<?php 

	import("model.data.PlanificacionModel");
	
	$m = new PlanificacionModel($dbconnection);
	
	$planifs = $m->getAllBy('creador = $1', "*", false, array($_SESSION['user']));
	
	if (isset($_POST['idasig'])) {
		$idasig = $_POST['idasig'];
		include "../view/custom/desglose_select_planif.tpl";
	}

?>