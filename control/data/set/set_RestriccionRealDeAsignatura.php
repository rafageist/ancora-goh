<?php 

/** 
  * @name: Receptor of RestriccionRealDeAsignatura data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.RestriccionRealDeAsignaturaModel"); 
import("model.data.RestriccionRealDeAsignatura"); 
import("model.data.RestriccionRealDeAsignaturaCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_restriccion_nominal.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_periodicidad.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_periodicidad.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_fecha.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_fecha.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_hora_inicial.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_hora_inicial.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_hora_final.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_hora_final.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_por_actividad.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_actividad.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_objeto.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_objeto.php"; 

// Getting data from POST 
   if (isset($_POST["field_RestriccionRealDeAsignatura_restriccion_nominal"])) $restriccion_nominal = $_POST["field_RestriccionRealDeAsignatura_restriccion_nominal"]; else $restriccion_nominal = null;
   if (isset($_POST["field_RestriccionRealDeAsignatura_periodicidad"])) $periodicidad = $_POST["field_RestriccionRealDeAsignatura_periodicidad"]; else $periodicidad = null;
   if (isset($_POST["field_RestriccionRealDeAsignatura_fecha"])) $fecha = $_POST["field_RestriccionRealDeAsignatura_fecha"]; else $fecha = null;
   if (isset($_POST["field_RestriccionRealDeAsignatura_hora_inicial"])) $hora_inicial = $_POST["field_RestriccionRealDeAsignatura_hora_inicial"]; else $hora_inicial = null;
   if (isset($_POST["field_RestriccionRealDeAsignatura_hora_final"])) $hora_final = $_POST["field_RestriccionRealDeAsignatura_hora_final"]; else $hora_final = null;
   if (isset($_POST["field_RestriccionRealDeAsignatura_por_actividad"])) $por_actividad = $_POST["field_RestriccionRealDeAsignatura_por_actividad"]; else $por_actividad = null;
   if (isset($_POST["field_RestriccionRealDeAsignatura_actividad"])) $actividad = $_POST["field_RestriccionRealDeAsignatura_actividad"]; else $actividad = null;
   if (isset($_POST["field_RestriccionRealDeAsignatura_objeto"])) $objeto = $_POST["field_RestriccionRealDeAsignatura_objeto"]; else $objeto = null;

 // Actualizando el objeto 
   $col = new RestriccionRealDeAsignaturaCollection($dbconnection);
   $obj = $col->getItem($restriccion_nominal,$periodicidad);
if ($restriccion_nominal !== null) $obj->setRestriccionNominal($restriccion_nominal);
if ($periodicidad !== null) $obj->setPeriodicidad($periodicidad);
if ($fecha !== null) $obj->setFecha($fecha);
if ($hora_inicial !== null) $obj->setHoraInicial($hora_inicial);
if ($hora_final !== null) $obj->setHoraFinal($hora_final);
if ($por_actividad !== null) $obj->setPorActividad($por_actividad);
if ($actividad !== null) $obj->setActividad($actividad);
if ($objeto !== null) $obj->setObjeto($objeto);
?>