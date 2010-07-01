<?php 

	import("model.data.PlanificacionModel");
	
	$m = new PlanificacionModel($dbconnection);
	
	$planifs = $m->getAllBy("creador = '{$_SESSION['user']}'");
	
	if (isset($_POST['idasig'])) {
		$idasig = $_POST['idasig'];
		include "../view/custom/desglose_select_planif.tpl";
	}

?>