<?php 

/** 
  * @name: Receptor of RestriccionPorActividad data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionPorActividadModel"); 
import("model.data.RestriccionPorActividad"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RestriccionPorActividad_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionPorActividad_restriccion_nominal.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionPorActividad_actividad.php")) include "../control/data/fdms/fdm_RestriccionPorActividad_actividad.php"; 

// Getting data from POST 
   $restriccion_nominal = $_POST["field_RestriccionPorActividad_restriccion_nominal"];
   $actividad = $_POST["field_RestriccionPorActividad_actividad"];

 // Construyendo el objeto 
   $obj = new RestriccionPorActividad($restriccion_nominal,$actividad,true, null, false);
?>