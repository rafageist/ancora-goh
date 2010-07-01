<?php 

/** 
  * @name: Receptor of RestriccionPorActividadDeBrigada data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionPorActividadDeBrigadaModel"); 
import("model.data.RestriccionPorActividadDeBrigada"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RestriccionPorActividadDeBrigada_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionPorActividadDeBrigada_restriccion_nominal.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionPorActividadDeBrigada_actividad.php")) include "../control/data/fdms/fdm_RestriccionPorActividadDeBrigada_actividad.php"; 

// Getting data from POST 
   $restriccion_nominal = $_POST["field_RestriccionPorActividadDeBrigada_restriccion_nominal"];
   $actividad = $_POST["field_RestriccionPorActividadDeBrigada_actividad"];

 // Construyendo el objeto 
   $obj = new RestriccionPorActividadDeBrigada($restriccion_nominal,$actividad,true, null, false);
?>