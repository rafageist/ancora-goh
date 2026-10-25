<?php 

/** 
  * @name: Receptor of RestriccionRealDePeriodo data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 

import("model.data.RestriccionRealDePeriodoModel"); 
import("model.data.RestriccionRealDePeriodo"); 
import("model.data.RestriccionRealDePeriodoCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_restriccion_nominal.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_periodicidad.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_periodicidad.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_fecha.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_fecha.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_hora_inicial.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_hora_inicial.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_hora_final.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_hora_final.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_por_actividad.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_actividad.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_objeto.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_objeto.php"; 

// Getting data from POST 
   if (isset($_POST["field_RestriccionRealDePeriodo_restriccion_nominal"])) $restriccion_nominal = $_POST["field_RestriccionRealDePeriodo_restriccion_nominal"]; else $restriccion_nominal = null;
   if (isset($_POST["field_RestriccionRealDePeriodo_periodicidad"])) $periodicidad = $_POST["field_RestriccionRealDePeriodo_periodicidad"]; else $periodicidad = null;
   if (isset($_POST["field_RestriccionRealDePeriodo_fecha"])) $fecha = $_POST["field_RestriccionRealDePeriodo_fecha"]; else $fecha = null;
   if (isset($_POST["field_RestriccionRealDePeriodo_hora_inicial"])) $hora_inicial = $_POST["field_RestriccionRealDePeriodo_hora_inicial"]; else $hora_inicial = null;
   if (isset($_POST["field_RestriccionRealDePeriodo_hora_final"])) $hora_final = $_POST["field_RestriccionRealDePeriodo_hora_final"]; else $hora_final = null;
   if (isset($_POST["field_RestriccionRealDePeriodo_por_actividad"])) $por_actividad = $_POST["field_RestriccionRealDePeriodo_por_actividad"]; else $por_actividad = null;
   if (isset($_POST["field_RestriccionRealDePeriodo_actividad"])) $actividad = $_POST["field_RestriccionRealDePeriodo_actividad"]; else $actividad = null;
   if (isset($_POST["field_RestriccionRealDePeriodo_objeto"])) $objeto = $_POST["field_RestriccionRealDePeriodo_objeto"]; else $objeto = null;

 // Actualizando el objeto 
   $col = new RestriccionRealDePeriodoCollection($dbconnection);
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