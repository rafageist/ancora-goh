<?php
    $for_new = true;
    
    import("lib.ancora.timeutils.TimeUtils");
    import("model.data.PlanificacionModel");
    import("model.data.Planificacion");
    import("model.data.PlanificacionCollection");
    
	$planificaciones  = new PlanificacionCollection($dbconnection);
	$planificaciones->Load(null, null, "*", 'creador = $1', array($_SESSION['user']));      
    
	if (isset($_POST['planificacion'])) $idplanif = $_POST['planificacion'];
	
    include "../view/custom/form_periodo.tpl";
?>