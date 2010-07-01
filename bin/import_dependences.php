<?php

	$ANTES = true;
	$DESPUES = false;
	
	$import_dependences = array();
	
	$import_dependences["model.data.Planificacion"] = array("antes" => array("model.data.PlanificacionModel"),
														    "despues" => array("model.data.PlanificacionCollection"));

?>