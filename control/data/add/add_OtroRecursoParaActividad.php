<?php 

/** 
  * @name: Receptor of OtroRecursoParaActividad data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.OtroRecursoParaActividadModel"); 
import("model.data.OtroRecursoParaActividad"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_OtroRecursoParaActividad_grupo_de_recursos.php")) include "../control/data/fdms/fdm_OtroRecursoParaActividad_grupo_de_recursos.php"; 
   if (file_exists("../control/data/fdms/fdm_OtroRecursoParaActividad_prioridad.php")) include "../control/data/fdms/fdm_OtroRecursoParaActividad_prioridad.php"; 
   if (file_exists("../control/data/fdms/fdm_OtroRecursoParaActividad_otro_recurso.php")) include "../control/data/fdms/fdm_OtroRecursoParaActividad_otro_recurso.php"; 

// Getting data from POST 
   $grupo_de_recursos = $_POST["field_OtroRecursoParaActividad_grupo_de_recursos"];
   $prioridad = $_POST["field_OtroRecursoParaActividad_prioridad"];
   $otro_recurso = $_POST["field_OtroRecursoParaActividad_otro_recurso"];

 // Construyendo el objeto 
   $obj = new OtroRecursoParaActividad($grupo_de_recursos,$prioridad,$otro_recurso,true, null, false);
?>