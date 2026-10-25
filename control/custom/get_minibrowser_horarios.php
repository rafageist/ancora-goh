<?php
	
	import("model.data.ActividadModel");
	import("model.data.Actividad");
	import("model.data.ActividadCollection");
	
	import("model.data.PeriodoModel");
	import("model.data.Periodo");
	import("model.data.PeriodoCollection");
	
	import("model.data.ClasificacionDeActividadModel");
	import("model.data.ClasificacionDeActividad");
	import("model.data.ClasificacionDeActividadCollection");
	
	import("model.data.LugarModel");
	import("model.data.Lugar");
	import("model.data.LugarCollection");
	
	import("model.data.ProfesorModel");
	import("model.data.Profesor");
	import("model.data.ProfesorCollection");
	
	import("model.data.AsignaturaModel");
	import("model.data.Asignatura");
	import("model.data.AsignaturaCollection");
	
	
	$idprofe = "";
	$idbrg = "";
	$idlugar = "";
	$idres = "";
	
	
	
	$idperiodo = $_POST['idperiodo'];
		
	$col = new ActividadModel($dbconnection);
	
	$actividaddes = false;
	
	if (isset($_POST['idbrg'])){
		$idbrg = $_POST['idbrg'];
		$actividades = $col->getAllBy('matrix.get_pos_of(dame_lista_id_brigadas_de_grupo(grupo), $1)>-1 AND dame_periodo_plantilla_de_actividad(plantilla) = $2',"*, dame_clasif_plantilla_de_actividad(plantilla) as clasificacion, dame_asignatura_desglose(dame_desglose_de_plantilla(plantilla)) as asignatura", true, array($idbrg, $idperiodo));
	}
	
	if (isset($_POST['idprofe'])){
		$idprofe = $_POST['idprofe'];
		$actividades = $col->getAllBy('profesor = $1 AND dame_periodo_plantilla_de_actividad(plantilla) = $2',"*, dame_clasif_plantilla_de_actividad(plantilla) as clasificacion, dame_asignatura_desglose(dame_desglose_de_plantilla(plantilla)) as asignatura", true, array($idprofe, $idperiodo));
	}
	
	if (isset($_POST['idlug'])){
		$idlug = $_POST['idlug'];
		$actividades = $col->getAllBy('lugar = $1 AND dame_periodo_plantilla_de_actividad(plantilla) = $2',"*, dame_clasif_plantilla_de_actividad(plantilla) as clasificacion, dame_asignatura_desglose(dame_desglose_de_plantilla(plantilla)) as asignatura", true, array($idlug, $idperiodo));
	}
	
	if (isset($_POST['idres'])){
		$idres = $_POST['idres'];
		$actividades = $col->getAllBy('matrix.get_pos_of(dame_lista_id_otros_recursos_usados(id), $1)>-1 AND dame_periodo_plantilla_de_actividad(plantilla) = $2',"*, dame_clasif_plantilla_de_actividad(plantilla) as clasificacion, dame_asignatura_desglose(dame_desglose_de_plantilla(plantilla)) as asignatura", true, array($idres, $idperiodo));
	}
	
	$col = new PeriodoCollection($dbconnection);
	
	$periodo = $col->getItem($idperiodo);
	
	$horario = array();
	
	if (is_array($actividades)){
		foreach($actividades as $actividad){
			$d = $actividad->dia;
			$t = $actividad->turno;
			
			$horario[$d][$t] = $actividad;
			
			$horario[$d][$t]->act = true;
			
			$col = new ClasificacionDeActividadCollection($dbconnection);
			$oclasif = $col->getItem($actividad->clasificacion);
			
			$col = new ProfesorCollection($dbconnection);
			$oprofe = $col->getItem($actividad->profesor);
			
			$col = new LugarCollection($dbconnection);
			$olugar = $col->getItem($actividad->lugar);
			
			$col = new AsignaturaCollection($dbconnection);
			$oasig = $col->getItem($actividad->asignatura);
			
			$horario[$d][$t]->oclasif = $oclasif;
			$horario[$d][$t]->oprofe = $oprofe;
			$horario[$d][$t]->olugar = $olugar;
			$horario[$d][$t]->oasig = $oasig;
		}
	}
	
	include "../view/custom/minibrowser_horarios.tpl";
	


?>