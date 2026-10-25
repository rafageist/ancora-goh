<?php

		import("model.data.ClasificacionDeActividadModel");
		import("model.data.ClasificacionDeActividad");
		import("model.data.ClasificacionDeActividadCollection");
		import("model.data.PlantillaDeActividadModel");
		
		import("model.data.PublicFunctions");
		
		$id = $_POST['id'];
		
		$clasifs = new ClasificacionDeActividadCollection($dbconnection);
		$clasifs->Load(null, null, "*", 'creador = $1', array($_SESSION['user']));
		 
	    $title = "Editando plantilla de actividad";
		
		$nueva_plantilla = false;
		
		$m = new PlantillaDeActividadModel($dbconnection);
		
		$plantilla = $m->get($id);
		
		$orden = $plantilla->orden;
		
		// Obteniendo otras informaciones necesarias
		
		$m = new PublicFunctions($dbconnection);
		
		$r = $m->DameDesgloseDePlantilla($id);
		
		$desglose = $r[0]['dame_desglose_de_plantilla'];
		
		$r = $m->DameAsignaturaPlantillaDeActividad($id);
		
		$asignatura = $r[0]['dame_asignatura_plantilla_de_actividad'];
		
		$r = $m->DamePeriodoPlantillaDeActividad($id);
		
		$periodo = $r[0]['dame_periodo_plantilla_de_actividad'];
		
		$r = $m->DamePlanificacionDePeriodo($periodo);
		
		$planificacion = $r[0]['dame_planificacion_de_periodo'];
		
		$clas = array();
		
		foreach ($clasifs->collection as $cla) $clas[$cla->id] = $cla->descripcion;
		
		
		include "../view/custom/form_plantilla_de_actividad.tpl";


?>
