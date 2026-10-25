<?php 

/** 
  * @name: Receptor of RestriccionRealDeAsignatura identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionRealDeAsignaturaModel"); 
import("model.data.RestriccionRealDeAsignatura"); 
import("model.data.RestriccionRealDeAsignaturaCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_restriccion_nominal.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_periodicidad.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_periodicidad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_fecha.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_fecha.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_hora_inicial.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_hora_inicial.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_hora_final.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_hora_final.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_por_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_objeto.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_objeto.php"; 
   if (isset($_POST["field_RestriccionRealDeAsignatura_restriccion_nominal"])) $restriccion_nominal = $_POST["field_RestriccionRealDeAsignatura_restriccion_nominal"]; else $restriccion_nominal = null;
   if (isset($_POST["field_RestriccionRealDeAsignatura_periodicidad"])) $periodicidad = $_POST["field_RestriccionRealDeAsignatura_periodicidad"]; else $periodicidad = null;
   if (isset($_POST["field_RestriccionRealDeAsignatura_fecha"])) $fecha = $_POST["field_RestriccionRealDeAsignatura_fecha"]; else $fecha = null;
   if (isset($_POST["field_RestriccionRealDeAsignatura_hora_inicial"])) $hora_inicial = $_POST["field_RestriccionRealDeAsignatura_hora_inicial"]; else $hora_inicial = null;
   if (isset($_POST["field_RestriccionRealDeAsignatura_hora_final"])) $hora_final = $_POST["field_RestriccionRealDeAsignatura_hora_final"]; else $hora_final = null;
   if (isset($_POST["field_RestriccionRealDeAsignatura_por_actividad"])) $por_actividad = $_POST["field_RestriccionRealDeAsignatura_por_actividad"]; else $por_actividad = null;
   if (isset($_POST["field_RestriccionRealDeAsignatura_actividad"])) $actividad = $_POST["field_RestriccionRealDeAsignatura_actividad"]; else $actividad = null;
   if (isset($_POST["field_RestriccionRealDeAsignatura_objeto"])) $objeto = $_POST["field_RestriccionRealDeAsignatura_objeto"]; else $objeto = null;

 // Eliminando el objeto 
   $col = new RestriccionRealDeAsignaturaCollection($dbconnection);
   $col->removeItem($restriccion_nominal,$periodicidad);
?>