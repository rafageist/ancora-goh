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
	
	
	
	// #14: this handler is anonymous (Application::$anonymous_events), so the
	// request values are bound as query parameters instead of being
	// interpolated into the SQL text. Same queries and columns as before.
	$idperiodo = (isset($_POST['idperiodo']) && is_string($_POST['idperiodo'])) ? $_POST['idperiodo'] : "";

	$actividades = false;

	// #44: each query is constant SQL text; as before, when several ids are
	// posted the last one wins (idbrg, idprofe, idlug, idres)
	if (isset($_POST['idbrg']) && is_string($_POST['idbrg']))
		$actividades = $dbconnection->getAsObjectsParams('SELECT *, dame_clasif_plantilla_de_actividad(plantilla) as clasificacion, dame_asignatura_desglose(dame_desglose_de_plantilla(plantilla)) as asignatura FROM actividad WHERE matrix.get_pos_of(dame_lista_id_brigadas_de_grupo(grupo), $1)>-1 AND dame_periodo_plantilla_de_actividad(plantilla) = $2;', array($_POST['idbrg'], $idperiodo));
	if (isset($_POST['idprofe']) && is_string($_POST['idprofe']))
		$actividades = $dbconnection->getAsObjectsParams('SELECT *, dame_clasif_plantilla_de_actividad(plantilla) as clasificacion, dame_asignatura_desglose(dame_desglose_de_plantilla(plantilla)) as asignatura FROM actividad WHERE profesor = $1 AND dame_periodo_plantilla_de_actividad(plantilla) = $2;', array($_POST['idprofe'], $idperiodo));
	if (isset($_POST['idlug']) && is_string($_POST['idlug']))
		$actividades = $dbconnection->getAsObjectsParams('SELECT *, dame_clasif_plantilla_de_actividad(plantilla) as clasificacion, dame_asignatura_desglose(dame_desglose_de_plantilla(plantilla)) as asignatura FROM actividad WHERE lugar = $1 AND dame_periodo_plantilla_de_actividad(plantilla) = $2;', array($_POST['idlug'], $idperiodo));
	if (isset($_POST['idres']) && is_string($_POST['idres']))
		$actividades = $dbconnection->getAsObjectsParams('SELECT *, dame_clasif_plantilla_de_actividad(plantilla) as clasificacion, dame_asignatura_desglose(dame_desglose_de_plantilla(plantilla)) as asignatura FROM actividad WHERE matrix.get_pos_of(dame_lista_id_otros_recursos_usados(id), $1)>-1 AND dame_periodo_plantilla_de_actividad(plantilla) = $2;', array($_POST['idres'], $idperiodo));

	// #14: PeriodoCollection::getItem() interpolates the id into its SQL;
	// read the same row with a bound parameter. The template only uses
	// $periodo->cant_dias and $periodo->cant_turnos.
	$periodo = false;
	$filas_periodo = $dbconnection->getAsObjectsParams("SELECT * FROM periodo WHERE id = $1;", array($idperiodo));
	if (is_array($filas_periodo) && isset($filas_periodo[0])) $periodo = $filas_periodo[0];
	
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
	
	include "../view/custom/minibrowser_publico.tpl";
	


?>