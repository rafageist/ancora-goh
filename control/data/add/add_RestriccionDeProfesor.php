<?php 

/** 
  * @name: Receptor of RestriccionDeProfesor data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionDeProfesorModel"); 
import("model.data.RestriccionDeProfesor"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeProfesor_profesor.php")) include "../control/data/fdms/fdm_RestriccionDeProfesor_profesor.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeProfesor_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeProfesor_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeProfesor_dia.php")) include "../control/data/fdms/fdm_RestriccionDeProfesor_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeProfesor_turno.php")) include "../control/data/fdms/fdm_RestriccionDeProfesor_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeProfesor_valor.php")) include "../control/data/fdms/fdm_RestriccionDeProfesor_valor.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeProfesor_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeProfesor_por_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeProfesor_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeProfesor_actividad.php"; 

// Getting data from POST 
   $profesor = $_POST["field_RestriccionDeProfesor_profesor"];
   $periodo = $_POST["field_RestriccionDeProfesor_periodo"];
   $dia = $_POST["field_RestriccionDeProfesor_dia"];
   $turno = $_POST["field_RestriccionDeProfesor_turno"];
   $valor = $_POST["field_RestriccionDeProfesor_valor"];
   $por_actividad = $_POST["field_RestriccionDeProfesor_por_actividad"];
   $actividad = $_POST["field_RestriccionDeProfesor_actividad"];

 // Construyendo el objeto 
   $obj = new RestriccionDeProfesor($profesor,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true, null, false);
?>