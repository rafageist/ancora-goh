<?php

		import("model.data.ClasificacionDeActividadModel");
		import("model.data.ClasificacionDeActividad");
		import("model.data.ClasificacionDeActividadCollection");
		import("model.data.PublicFunctions");

		$clasifs = new ClasificacionDeActividadCollection($dbconnection);
		$clasifs->Load(null,null,"*","creador ='{$_SESSION['user']}'");

		$for_new = true;
		
		$desglose = $_POST['desglose'];

		$m = new PublicFunctions($dbconnection);
		
		$c = $m->DameCantActDesglose($desglose);
		
		$orden = $c[0]['dame_cant_act_desglose']  +1;
		
		// Obteniendo otras informaciones necesarias
		
		$r = $m->DameAsignaturaDesglose($desglose);
		
		$asignatura = $r[0]['dame_asignatura_desglose'];
		
		$r = $m->DamePeriodoDesglose($desglose);
		 
		$periodo = $r[0]['dame_periodo_desglose'];
		
		$r = $m->DamePlanificacionDePeriodo($periodo);
		
		$planificacion = $r[0]['dame_planificacion_de_periodo'];
	
		$clas = array();
		
		foreach ($clasifs->collection as $cla) $clas[$cla->id] = $cla->descripcion;
		
		include "../view/custom/form_plantilla_de_actividad.tpl";


?>
