<?php 

/** 
  * @name: Receptor of RestriccionDeAsignatura data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionDeAsignaturaModel"); 
import("model.data.RestriccionDeAsignatura"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeAsignatura_asignatura.php")) include "../control/data/fdms/fdm_RestriccionDeAsignatura_asignatura.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeAsignatura_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeAsignatura_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeAsignatura_dia.php")) include "../control/data/fdms/fdm_RestriccionDeAsignatura_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeAsignatura_turno.php")) include "../control/data/fdms/fdm_RestriccionDeAsignatura_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeAsignatura_valor.php")) include "../control/data/fdms/fdm_RestriccionDeAsignatura_valor.php"; 

// Getting data from POST 
   $asignatura = $_POST["field_RestriccionDeAsignatura_asignatura"];
   $periodo = $_POST["field_RestriccionDeAsignatura_periodo"];
   $dia = $_POST["field_RestriccionDeAsignatura_dia"];
   $turno = $_POST["field_RestriccionDeAsignatura_turno"];
   $valor = $_POST["field_RestriccionDeAsignatura_valor"];

 // Construyendo el objeto 
   $obj = new RestriccionDeAsignatura($asignatura,$periodo,$dia,$turno,$valor,"",true, null, false);
?>