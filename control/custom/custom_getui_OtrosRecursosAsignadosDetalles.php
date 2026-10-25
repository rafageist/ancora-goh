<?php

    import("model.data.OtroGrupoDeRecursosParaActividadModel");
    import("model.data.OtroRecursoParaActividadModel");
	import("model.data.OtroRecursoModel");
    
	$idrxact = $_POST['rxact'];
	
    $m = new OtroGrupoDeRecursosParaActividadModel($dbconnection);
    
    $rxact = $m->get($idrxact);
    
	$m = new OtroRecursoParaActividadModel($dbconnection);
	
	$recs = $m->getAllBy('grupo_de_recursos = $1 ORDER BY prioridad', "*", false, array($idrxact));
	
    $m = new OtroRecursoModel($dbconnection);
    
    $orecs = array();
    
    if (is_array($recs))
        foreach ($recs as $rec){
            $r = $m->get($rec['otro_recurso']);
            $orecs[$r->id] = $r;
        }
        
	include "../view/custom/otros_recursos_asignados_detalles.tpl";

?>