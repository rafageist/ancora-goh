<?php
	
	import("model.data.PublicFunctions");
	
	$m = new PublicFunctions($dbconnection);
	
	$idgr = $_POST['grupo'];
	
	$r = $m->DamePlantillaDeGrupoDeRecursos($idgr);
	
	$idplantilla = $r[0]['dame_plantilla_de_grupo_de_recursos'];

	$recursos = $m->DameOtrosRecursosSinAsignar($idplantilla);
	
	$r = $m->DameMaxPrioriOtroRecurso($idgr);
	
	$max_prioridad = $r[0]['dame_max_priori_otro_recurso'];

	include "../view/custom/form_rxact.tpl";
?>