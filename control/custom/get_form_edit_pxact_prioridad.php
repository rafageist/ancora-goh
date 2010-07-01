<?php
	import("model.data.PublicFunctions");
			
	$idplantilla = $_POST['plantilla'];
	$idprofe = $_POST['profe'];
	$idpxact = $_POST['pxact'];
	
	$m = new PublicFunctions($dbconnection);
	
	$r = $m->DameMaxPrioridadDePxact($idplantilla);
	
	$max_priority = $r[0]['dame_max_prioridad_de_pxact'];
	
	$r = $m->DameCantProfesAsignados($idplantilla);
	
	$cant = $r[0]['dame_cant_profes_asignados'];
	
	if ($max_priority == $cant) $max_priority--;
		
	include "../view/custom/editar_prioridades_pxact.tpl";
?>