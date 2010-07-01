<?php 

/** 
  * @name: Receptor of RestriccionDeOtroRecurso data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionDeOtroRecursoModel"); 
import("model.data.RestriccionDeOtroRecurso"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeOtroRecurso_otro_recurso.php")) include "../control/data/fdms/fdm_RestriccionDeOtroRecurso_otro_recurso.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeOtroRecurso_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeOtroRecurso_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeOtroRecurso_dia.php")) include "../control/data/fdms/fdm_RestriccionDeOtroRecurso_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeOtroRecurso_turno.php")) include "../control/data/fdms/fdm_RestriccionDeOtroRecurso_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeOtroRecurso_valor.php")) include "../control/data/fdms/fdm_RestriccionDeOtroRecurso_valor.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeOtroRecurso_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeOtroRecurso_por_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeOtroRecurso_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeOtroRecurso_actividad.php"; 

// Getting data from POST 
   $otro_recurso = $_POST["field_RestriccionDeOtroRecurso_otro_recurso"];
   $periodo = $_POST["field_RestriccionDeOtroRecurso_periodo"];
   $dia = $_POST["field_RestriccionDeOtroRecurso_dia"];
   $turno = $_POST["field_RestriccionDeOtroRecurso_turno"];
   $valor = $_POST["field_RestriccionDeOtroRecurso_valor"];
   $por_actividad = $_POST["field_RestriccionDeOtroRecurso_por_actividad"];
   $actividad = $_POST["field_RestriccionDeOtroRecurso_actividad"];

 // Construyendo el objeto 
   $obj = new RestriccionDeOtroRecurso($otro_recurso,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true, null, false);
?>