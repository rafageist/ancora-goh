<?php 

/** 
  * @name: Receptor of RestriccionRealDeProfesor data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.RestriccionRealDeProfesorModel"); 
import("model.data.RestriccionRealDeProfesor"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_restriccion_nominal.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_restriccion_nominal.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_periodicidad.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_periodicidad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_fecha.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_fecha.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_hora_inicial.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_hora_inicial.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_hora_final.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_hora_final.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_por_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_actividad.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionRealDeProfesor_objeto.php")) include "../control/data/fdms/fdm_RestriccionRealDeProfesor_objeto.php"; 

// Getting data from POST 
   $restriccion_nominal = $_POST["field_RestriccionRealDeProfesor_restriccion_nominal"];
   $periodicidad = $_POST["field_RestriccionRealDeProfesor_periodicidad"];
   $fecha = $_POST["field_RestriccionRealDeProfesor_fecha"];
   $hora_inicial = $_POST["field_RestriccionRealDeProfesor_hora_inicial"];
   $hora_final = $_POST["field_RestriccionRealDeProfesor_hora_final"];
   $por_actividad = $_POST["field_RestriccionRealDeProfesor_por_actividad"];
   $actividad = $_POST["field_RestriccionRealDeProfesor_actividad"];
   $objeto = $_POST["field_RestriccionRealDeProfesor_objeto"];

 // Construyendo el objeto 
   $obj = new RestriccionRealDeProfesor($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto,true, null, false);
?>