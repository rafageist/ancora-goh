<?php 

/** 
  * @name: Receptor of RestriccionRealDeClasifAct data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.RestriccionRealDeClasifActModel"); 
import("model.data.RestriccionRealDeClasifAct"); 
import("model.data.RestriccionRealDeClasifActCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_restriccion_nominal.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_periodicidad.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_periodicidad.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_fecha.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_fecha.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_hora_inicial.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_hora_inicial.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_hora_final.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_hora_final.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_por_actividad.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_actividad.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_objeto.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_objeto.php"; 

// Getting data from POST 
   if (isset($_POST["field_RestriccionRealDeClasifAct_restriccion_nominal"])) $restriccion_nominal = $_POST["field_RestriccionRealDeClasifAct_restriccion_nominal"]; else $restriccion_nominal = "null";
   if (isset($_POST["field_RestriccionRealDeClasifAct_periodicidad"])) $periodicidad = $_POST["field_RestriccionRealDeClasifAct_periodicidad"]; else $periodicidad = "null";
   if (isset($_POST["field_RestriccionRealDeClasifAct_fecha"])) $fecha = $_POST["field_RestriccionRealDeClasifAct_fecha"]; else $fecha = "null";
   if (isset($_POST["field_RestriccionRealDeClasifAct_hora_inicial"])) $hora_inicial = $_POST["field_RestriccionRealDeClasifAct_hora_inicial"]; else $hora_inicial = "null";
   if (isset($_POST["field_RestriccionRealDeClasifAct_hora_final"])) $hora_final = $_POST["field_RestriccionRealDeClasifAct_hora_final"]; else $hora_final = "null";
   if (isset($_POST["field_RestriccionRealDeClasifAct_por_actividad"])) $por_actividad = $_POST["field_RestriccionRealDeClasifAct_por_actividad"]; else $por_actividad = "null";
   if (isset($_POST["field_RestriccionRealDeClasifAct_actividad"])) $actividad = $_POST["field_RestriccionRealDeClasifAct_actividad"]; else $actividad = "null";
   if (isset($_POST["field_RestriccionRealDeClasifAct_objeto"])) $objeto = $_POST["field_RestriccionRealDeClasifAct_objeto"]; else $objeto = "null";

 // Actualizando el objeto 
   $col = new RestriccionRealDeClasifActCollection($dbconnection);
   $obj = $col->getItem($restriccion_nominal,$periodicidad);
$obj->setRestriccionNominal($restriccion_nominal);
$obj->setPeriodicidad($periodicidad);
$obj->setFecha($fecha);
$obj->setHoraInicial($hora_inicial);
$obj->setHoraFinal($hora_final);
$obj->setPorActividad($por_actividad);
$obj->setActividad($actividad);
$obj->setObjeto($objeto);
?>