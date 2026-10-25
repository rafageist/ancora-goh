<?php 

/** 
  * @name: Receptor of RestriccionRealDeClasifAct identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionRealDeClasifActModel"); 
import("model.data.RestriccionRealDeClasifAct"); 
import("model.data.RestriccionRealDeClasifActCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_restriccion_nominal.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_periodicidad.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_periodicidad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_fecha.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_fecha.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_hora_inicial.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_hora_inicial.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_hora_final.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_hora_final.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_por_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeClasifAct_objeto.php")) include "../control/data/fdms/fdm_RestriccionRealDeClasifAct_objeto.php"; 
   if (isset($_POST["field_RestriccionRealDeClasifAct_restriccion_nominal"])) $restriccion_nominal = $_POST["field_RestriccionRealDeClasifAct_restriccion_nominal"]; else $restriccion_nominal = null;
   if (isset($_POST["field_RestriccionRealDeClasifAct_periodicidad"])) $periodicidad = $_POST["field_RestriccionRealDeClasifAct_periodicidad"]; else $periodicidad = null;
   if (isset($_POST["field_RestriccionRealDeClasifAct_fecha"])) $fecha = $_POST["field_RestriccionRealDeClasifAct_fecha"]; else $fecha = null;
   if (isset($_POST["field_RestriccionRealDeClasifAct_hora_inicial"])) $hora_inicial = $_POST["field_RestriccionRealDeClasifAct_hora_inicial"]; else $hora_inicial = null;
   if (isset($_POST["field_RestriccionRealDeClasifAct_hora_final"])) $hora_final = $_POST["field_RestriccionRealDeClasifAct_hora_final"]; else $hora_final = null;
   if (isset($_POST["field_RestriccionRealDeClasifAct_por_actividad"])) $por_actividad = $_POST["field_RestriccionRealDeClasifAct_por_actividad"]; else $por_actividad = null;
   if (isset($_POST["field_RestriccionRealDeClasifAct_actividad"])) $actividad = $_POST["field_RestriccionRealDeClasifAct_actividad"]; else $actividad = null;
   if (isset($_POST["field_RestriccionRealDeClasifAct_objeto"])) $objeto = $_POST["field_RestriccionRealDeClasifAct_objeto"]; else $objeto = null;

 // Eliminando el objeto 
   $col = new RestriccionRealDeClasifActCollection($dbconnection);
   $col->removeItem($restriccion_nominal,$periodicidad);
?>