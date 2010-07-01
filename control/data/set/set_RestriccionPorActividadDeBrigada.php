<?php 

/** 
  * @name: Receptor of RestriccionPorActividadDeBrigada data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.RestriccionPorActividadDeBrigadaModel"); 
import("model.data.RestriccionPorActividadDeBrigada"); 
import("model.data.RestriccionPorActividadDeBrigadaCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_RestriccionPorActividadDeBrigada_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionPorActividadDeBrigada_restriccion_nominal.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionPorActividadDeBrigada_actividad.php")) include "../control/data/fdms/fdm_RestriccionPorActividadDeBrigada_actividad.php"; 

// Getting data from POST 
   if (isset($_POST["field_RestriccionPorActividadDeBrigada_restriccion_nominal"])) $restriccion_nominal = $_POST["field_RestriccionPorActividadDeBrigada_restriccion_nominal"]; else $restriccion_nominal = "null";
   if (isset($_POST["field_RestriccionPorActividadDeBrigada_actividad"])) $actividad = $_POST["field_RestriccionPorActividadDeBrigada_actividad"]; else $actividad = "null";

 // Actualizando el objeto 
   $col = new RestriccionPorActividadDeBrigadaCollection($dbconnection);
   $obj = $col->getItem($restriccion_nominal);
$obj->setRestriccionNominal($restriccion_nominal);
$obj->setActividad($actividad);
?>