<?php 

/** 
  * @name: Receptor of RestriccionReal data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionRealModel"); 
import("model.data.RestriccionReal"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RestriccionReal_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionReal_restriccion_nominal.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionReal_periodicidad.php")) include "../control/data/fdms/fdm_RestriccionReal_periodicidad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionReal_fecha.php")) include "../control/data/fdms/fdm_RestriccionReal_fecha.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionReal_hora_inicial.php")) include "../control/data/fdms/fdm_RestriccionReal_hora_inicial.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionReal_hora_final.php")) include "../control/data/fdms/fdm_RestriccionReal_hora_final.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionReal_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionReal_por_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionReal_actividad.php")) include "../control/data/fdms/fdm_RestriccionReal_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionReal_objeto.php")) include "../control/data/fdms/fdm_RestriccionReal_objeto.php"; 

// Getting data from POST 
   $restriccion_nominal = $_POST["field_RestriccionReal_restriccion_nominal"];
   $periodicidad = $_POST["field_RestriccionReal_periodicidad"];
   $fecha = $_POST["field_RestriccionReal_fecha"];
   $hora_inicial = $_POST["field_RestriccionReal_hora_inicial"];
   $hora_final = $_POST["field_RestriccionReal_hora_final"];
   $por_actividad = $_POST["field_RestriccionReal_por_actividad"];
   $actividad = $_POST["field_RestriccionReal_actividad"];
   $objeto = $_POST["field_RestriccionReal_objeto"];

 // Construyendo el objeto 
   $obj = new RestriccionReal($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,true, null, false);
?>