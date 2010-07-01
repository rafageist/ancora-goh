<?php 

/** 
  * @name: Receptor of RestriccionRealDeLugar data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.RestriccionRealDeLugarModel"); 
import("model.data.RestriccionRealDeLugar"); 
import("model.data.RestriccionRealDeLugarCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeLugar_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionRealDeLugar_restriccion_nominal.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeLugar_periodicidad.php")) include "../control/data/fdms/fdm_RestriccionRealDeLugar_periodicidad.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeLugar_fecha.php")) include "../control/data/fdms/fdm_RestriccionRealDeLugar_fecha.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeLugar_hora_inicial.php")) include "../control/data/fdms/fdm_RestriccionRealDeLugar_hora_inicial.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeLugar_hora_final.php")) include "../control/data/fdms/fdm_RestriccionRealDeLugar_hora_final.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeLugar_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeLugar_por_actividad.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeLugar_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeLugar_actividad.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionRealDeLugar_objeto.php")) include "../control/data/fdms/fdm_RestriccionRealDeLugar_objeto.php"; 

// Getting data from POST 
   if (isset($_POST["field_RestriccionRealDeLugar_restriccion_nominal"])) $restriccion_nominal = $_POST["field_RestriccionRealDeLugar_restriccion_nominal"]; else $restriccion_nominal = "null";
   if (isset($_POST["field_RestriccionRealDeLugar_periodicidad"])) $periodicidad = $_POST["field_RestriccionRealDeLugar_periodicidad"]; else $periodicidad = "null";
   if (isset($_POST["field_RestriccionRealDeLugar_fecha"])) $fecha = $_POST["field_RestriccionRealDeLugar_fecha"]; else $fecha = "null";
   if (isset($_POST["field_RestriccionRealDeLugar_hora_inicial"])) $hora_inicial = $_POST["field_RestriccionRealDeLugar_hora_inicial"]; else $hora_inicial = "null";
   if (isset($_POST["field_RestriccionRealDeLugar_hora_final"])) $hora_final = $_POST["field_RestriccionRealDeLugar_hora_final"]; else $hora_final = "null";
   if (isset($_POST["field_RestriccionRealDeLugar_por_actividad"])) $por_actividad = $_POST["field_RestriccionRealDeLugar_por_actividad"]; else $por_actividad = "null";
   if (isset($_POST["field_RestriccionRealDeLugar_actividad"])) $actividad = $_POST["field_RestriccionRealDeLugar_actividad"]; else $actividad = "null";
   if (isset($_POST["field_RestriccionRealDeLugar_objeto"])) $objeto = $_POST["field_RestriccionRealDeLugar_objeto"]; else $objeto = "null";

 // Actualizando el objeto 
   $col = new RestriccionRealDeLugarCollection($dbconnection);
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