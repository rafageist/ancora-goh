<?php

	import("model.data.OtroGrupoDeRecursosParaActividadModel");
	import("model.data.OtroRecursoParaActividadModel");
	import("model.data.OtroRecursoModel");
	import("model.data.OtroRecurso");
	import("model.data.OtroRecursoCollection");
		
	$idplantilla = $_POST['plantilla'];
	
	$m = new OtroGrupoDeRecursosParaActividadModel($dbconnection);
	
	$rxact = $m->getAllBy('plantilla = $1 ORDER BY id', "*", false, array($idplantilla));

	$m = new OtroRecursoParaActividadModel($dbconnection);
	
	$recursos = array();
	if (is_array($rxact))
	foreach ($rxact as $r){
		$o = $m->getAllBy('grupo_de_recursos = $1', "*", false, array($r['id']));
		$recursos[$r['id']] = array();
		if (is_array($o)) foreach ($o as $or){
			$cr = new OtroRecursoCollection($dbconnection);
			$it = $cr->getItem($or['otro_recurso']);
			$it->prioridad = $or['prioridad'];
		 	$recursos[$r['id']][] = $it;
		}
	}
	
	include "../view/custom/otros_recursos_asignados.tpl";

?>