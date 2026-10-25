<?php 
	import("model.data: PlanificacionModel, Planificacion, PlanificacionCollection");
    import("lib.ancora.timeutils.TimeUtils");

    $planifs = new PlanificacionCollection($dbconnection);
    $planifs->Load(null, null, "*", 'creador = $1', array($_SESSION['user']));
    
	include "../view/custom/minibrowser_planificaciones.tpl";

?>