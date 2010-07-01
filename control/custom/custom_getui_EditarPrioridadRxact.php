<?php

	import("model.data.PublicFunctions");
			
	$idplantilla = $_POST['plantilla'];
	$idrxact = $_POST['rxact'];
	
	$m = new PublicFunctions($dbconnection);
	
	$r= $m->DameMaxPrioridadRxact($idplantilla);

	$max_priority = $r[0]['dame_max_prioridad_rxact'];
	
	$r= $m->DameCantGruposDeRecursosAsignados($idplantilla);
	
	$cant = $r[0]['dame_cant_grupos_de_recursos_asignados'];

	if ($max_priority == $cant) $max_priority--;
	
	include "../view/custom/editar_prioridades_rxact.tpl";
	
?>