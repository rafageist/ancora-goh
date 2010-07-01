<?php 

/** 
  * @name: Receptor of RestriccionRealDeAsignatura data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionRealDeAsignaturaModel"); 
import("model.data.RestriccionRealDeAsignatura"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_restriccion_nominal.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_periodicidad.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_periodicidad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_fecha.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_fecha.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_hora_inicial.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_hora_inicial.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_hora_final.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_hora_final.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_por_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeAsignatura_objeto.php")) include "../control/data/fdms/fdm_RestriccionRealDeAsignatura_objeto.php"; 

// Getting data from POST 
   $restriccion_nominal = $_POST["field_RestriccionRealDeAsignatura_restriccion_nominal"];
   $periodicidad = $_POST["field_RestriccionRealDeAsignatura_periodicidad"];
   $fecha = $_POST["field_RestriccionRealDeAsignatura_fecha"];
   $hora_inicial = $_POST["field_RestriccionRealDeAsignatura_hora_inicial"];
   $hora_final = $_POST["field_RestriccionRealDeAsignatura_hora_final"];
   $por_actividad = $_POST["field_RestriccionRealDeAsignatura_por_actividad"];
   $actividad = $_POST["field_RestriccionRealDeAsignatura_actividad"];
   $objeto = $_POST["field_RestriccionRealDeAsignatura_objeto"];

 // Construyendo el objeto 
   $obj = new RestriccionRealDeAsignatura($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,true, null, false);
?>