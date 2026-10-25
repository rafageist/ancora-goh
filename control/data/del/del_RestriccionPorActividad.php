<?php 

/** 
  * @name: Receptor of RestriccionPorActividad identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionPorActividadModel"); 
import("model.data.RestriccionPorActividad"); 
import("model.data.RestriccionPorActividadCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_RestriccionPorActividad_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionPorActividad_restriccion_nominal.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionPorActividad_actividad.php")) include "../control/data/fdms/fdm_RestriccionPorActividad_actividad.php"; 
   if (isset($_POST["field_RestriccionPorActividad_restriccion_nominal"])) $restriccion_nominal = $_POST["field_RestriccionPorActividad_restriccion_nominal"]; else $restriccion_nominal = null;
   if (isset($_POST["field_RestriccionPorActividad_actividad"])) $actividad = $_POST["field_RestriccionPorActividad_actividad"]; else $actividad = null;

 // Eliminando el objeto 
   $col = new RestriccionPorActividadCollection($dbconnection);
   $col->removeItem($restriccion_nominal,$actividad);
?>