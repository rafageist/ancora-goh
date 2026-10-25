<?php 

/** 
  * @name: Receptor of RestriccionRealDeProfesor identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.RestriccionRealDeProfesorModel"); 
import("model.data.RestriccionRealDeProfesor"); 
import("model.data.RestriccionRealDeProfesorCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_restriccion_nominal.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_periodicidad.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_periodicidad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_fecha.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_fecha.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_hora_inicial.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_hora_inicial.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_hora_final.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_hora_final.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_por_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_objeto.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_objeto.php"; 
   if (isset($_POST["field_RestriccionRealDeProfesor_restriccion_nominal"])) $restriccion_nominal = $_POST["field_RestriccionRealDeProfesor_restriccion_nominal"]; else $restriccion_nominal = null;
   if (isset($_POST["field_RestriccionRealDeProfesor_periodicidad"])) $periodicidad = $_POST["field_RestriccionRealDeProfesor_periodicidad"]; else $periodicidad = null;
   if (isset($_POST["field_RestriccionRealDeProfesor_fecha"])) $fecha = $_POST["field_RestriccionRealDeProfesor_fecha"]; else $fecha = null;
   if (isset($_POST["field_RestriccionRealDeProfesor_hora_inicial"])) $hora_inicial = $_POST["field_RestriccionRealDeProfesor_hora_inicial"]; else $hora_inicial = null;
   if (isset($_POST["field_RestriccionRealDeProfesor_hora_final"])) $hora_final = $_POST["field_RestriccionRealDeProfesor_hora_final"]; else $hora_final = null;
   if (isset($_POST["field_RestriccionRealDeProfesor_por_actividad"])) $por_actividad = $_POST["field_RestriccionRealDeProfesor_por_actividad"]; else $por_actividad = null;
   if (isset($_POST["field_RestriccionRealDeProfesor_actividad"])) $actividad = $_POST["field_RestriccionRealDeProfesor_actividad"]; else $actividad = null;
   if (isset($_POST["field_RestriccionRealDeProfesor_objeto"])) $objeto = $_POST["field_RestriccionRealDeProfesor_objeto"]; else $objeto = null;

 // Eliminando el objeto 
   $col = new RestriccionRealDeProfesorCollection($dbconnection);
   $col->removeItem($restriccion_nominal,$periodicidad);
?>