<?php 

/** 
  * @name: Receptor of RestriccionDeEspecialidad data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionDeEspecialidadModel"); 
import("model.data.RestriccionDeEspecialidad"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeEspecialidad_especialidad.php")) include "../control/data/fdms/fdm_RestriccionDeEspecialidad_especialidad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeEspecialidad_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeEspecialidad_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeEspecialidad_dia.php")) include "../control/data/fdms/fdm_RestriccionDeEspecialidad_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeEspecialidad_turno.php")) include "../control/data/fdms/fdm_RestriccionDeEspecialidad_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeEspecialidad_valor.php")) include "../control/data/fdms/fdm_RestriccionDeEspecialidad_valor.php"; 

// Getting data from POST 
   $especialidad = $_POST["field_RestriccionDeEspecialidad_especialidad"];
   $periodo = $_POST["field_RestriccionDeEspecialidad_periodo"];
   $dia = $_POST["field_RestriccionDeEspecialidad_dia"];
   $turno = $_POST["field_RestriccionDeEspecialidad_turno"];
   $valor = $_POST["field_RestriccionDeEspecialidad_valor"];

 // Construyendo el objeto 
   $obj = new RestriccionDeEspecialidad($especialidad,$periodo,$dia,$turno,$valor,"",true, null, false);
?>