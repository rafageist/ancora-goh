<?php 

/** 
  * @name: Receptor of RestriccionPorActividadDeLugar data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.RestriccionPorActividadDeLugarModel"); 
import("model.data.RestriccionPorActividadDeLugar"); 
import("model.data.RestriccionPorActividadDeLugarCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_RestriccionPorActividadDeLugar_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionPorActividadDeLugar_restriccion_nominal.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionPorActividadDeLugar_actividad.php")) include "../control/data/fdms/fdm_RestriccionPorActividadDeLugar_actividad.php"; 

// Getting data from POST 
   if (isset($_POST["field_RestriccionPorActividadDeLugar_restriccion_nominal"])) $restriccion_nominal = $_POST["field_RestriccionPorActividadDeLugar_restriccion_nominal"]; else $restriccion_nominal = null;
   if (isset($_POST["field_RestriccionPorActividadDeLugar_actividad"])) $actividad = $_POST["field_RestriccionPorActividadDeLugar_actividad"]; else $actividad = null;

 // Actualizando el objeto 
   $col = new RestriccionPorActividadDeLugarCollection($dbconnection);
   $obj = $col->getItem($restriccion_nominal);
if ($restriccion_nominal !== null) $obj->setRestriccionNominal($restriccion_nominal);
if ($actividad !== null) $obj->setActividad($actividad);
?>