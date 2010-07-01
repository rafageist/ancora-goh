<?php 

/** 
  * @name: Receptor of RestriccionPorActividadDeProfesor identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionPorActividadDeProfesorModel"); 
import("model.data.RestriccionPorActividadDeProfesor"); 
import("model.data.RestriccionPorActividadDeProfesorCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_RestriccionPorActividadDeProfesor_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionPorActividadDeProfesor_restriccion_nominal.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionPorActividadDeProfesor_actividad.php")) include "../control/data/fdms/fdm_RestriccionPorActividadDeProfesor_actividad.php"; 
   if (isset($_POST["field_RestriccionPorActividadDeProfesor_restriccion_nominal"])) $restriccion_nominal = $_POST["field_RestriccionPorActividadDeProfesor_restriccion_nominal"]; else $restriccion_nominal = "null";
   if (isset($_POST["field_RestriccionPorActividadDeProfesor_actividad"])) $actividad = $_POST["field_RestriccionPorActividadDeProfesor_actividad"]; else $actividad = "null";

 // Eliminando el objeto 
   $col = new RestriccionPorActividadDeProfesorCollection($dbconnection);
   $col->removeItem($restriccion_nominal);
?>