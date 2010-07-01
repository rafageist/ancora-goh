<?php

	import("model.data.PublicFunctions");
			
	$idplantilla = $_POST['plantilla'];
	$idlugar = $_POST['lugar'];
	
	$m = new PublicFunctions($dbconnection);
	
	$r= $m->DameMaxPrioridadLxact($idplantilla);

	$max_priority = $r[0]['dame_max_prioridad_lxact'];
	
	$r = $m->DameCantLugaresAsignados($idplantilla);
	
	$cant = $r[0]['dame_cant_lugares_asignados'];

	if ($max_priority == $cant) $max_priority--;
	
	include "../view/custom/editar_prioridades_lxact.tpl";
?>