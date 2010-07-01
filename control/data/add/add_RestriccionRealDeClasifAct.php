<?php 

/** 
  * @name: Receptor of RestriccionRealDeClasifAct data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionRealDeClasifActModel"); 
import("model.data.RestriccionRealDeClasifAct"); 

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
   $restriccion_nominal = $_POST["field_RestriccionRealDeClasifAct_restriccion_nominal"];
   $periodicidad = $_POST["field_RestriccionRealDeClasifAct_periodicidad"];
   $fecha = $_POST["field_RestriccionRealDeClasifAct_fecha"];
   $hora_inicial = $_POST["field_RestriccionRealDeClasifAct_hora_inicial"];
   $hora_final = $_POST["field_RestriccionRealDeClasifAct_hora_final"];
   $por_actividad = $_POST["field_RestriccionRealDeClasifAct_por_actividad"];
   $actividad = $_POST["field_RestriccionRealDeClasifAct_actividad"];
   $objeto = $_POST["field_RestriccionRealDeClasifAct_objeto"];

 // Construyendo el objeto 
   $obj = new RestriccionRealDeClasifAct($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,true, null, false);
?>