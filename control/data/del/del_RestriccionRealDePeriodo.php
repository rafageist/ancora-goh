<?php 

/** 
  * @name: Receptor of RestriccionRealDePeriodo identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.RestriccionRealDePeriodoModel"); 
import("model.data.RestriccionRealDePeriodo"); 
import("model.data.RestriccionRealDePeriodoCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_restriccion_nominal.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_periodicidad.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_periodicidad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_fecha.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_fecha.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_hora_inicial.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_hora_inicial.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_hora_final.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_hora_final.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_por_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDePeriodo_objeto.php")) include "../control/data/fdms/fdm_RestriccionRealDePeriodo_objeto.php"; 
   if (isset($_POST["field_RestriccionRealDePeriodo_restriccion_nominal"])) $restriccion_nominal = $_POST["field_RestriccionRealDePeriodo_restriccion_nominal"]; else $restriccion_nominal = "null";
   if (isset($_POST["field_RestriccionRealDePeriodo_periodicidad"])) $periodicidad = $_POST["field_RestriccionRealDePeriodo_periodicidad"]; else $periodicidad = "null";
   if (isset($_POST["field_RestriccionRealDePeriodo_fecha"])) $fecha = $_POST["field_RestriccionRealDePeriodo_fecha"]; else $fecha = "null";
   if (isset($_POST["field_RestriccionRealDePeriodo_hora_inicial"])) $hora_inicial = $_POST["field_RestriccionRealDePeriodo_hora_inicial"]; else $hora_inicial = "null";
   if (isset($_POST["field_RestriccionRealDePeriodo_hora_final"])) $hora_final = $_POST["field_RestriccionRealDePeriodo_hora_final"]; else $hora_final = "null";
   if (isset($_POST["field_RestriccionRealDePeriodo_por_actividad"])) $por_actividad = $_POST["field_RestriccionRealDePeriodo_por_actividad"]; else $por_actividad = "null";
   if (isset($_POST["field_RestriccionRealDePeriodo_actividad"])) $actividad = $_POST["field_RestriccionRealDePeriodo_actividad"]; else $actividad = "null";
   if (isset($_POST["field_RestriccionRealDePeriodo_objeto"])) $objeto = $_POST["field_RestriccionRealDePeriodo_objeto"]; else $objeto = "null";

 // Eliminando el objeto 
   $col = new RestriccionRealDePeriodoCollection($dbconnection);
   $col->removeItem($restriccion_nominal,$periodicidad);
?>