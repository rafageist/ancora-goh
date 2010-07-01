<?php 

/** 
  * @name: Receptor of OtroGrupoDeRecursosParaActividad data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.OtroGrupoDeRecursosParaActividadModel"); 
import("model.data.OtroGrupoDeRecursosParaActividad"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_plantilla.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_plantilla.php"; 
   if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_prioridad.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_prioridad.php"; 
   if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_necesarios.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_necesarios.php"; 
   if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_imprescindibles.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_imprescindibles.php"; 
   if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_creador.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_creador.php"; 

// Getting data from POST 
   $plantilla = $_POST["field_OtroGrupoDeRecursosParaActividad_plantilla"];
   $prioridad = $_POST["field_OtroGrupoDeRecursosParaActividad_prioridad"];
   $necesarios = $_POST["field_OtroGrupoDeRecursosParaActividad_necesarios"];
   $imprescindibles = $_POST["field_OtroGrupoDeRecursosParaActividad_imprescindibles"];
   $creador = $_POST["field_OtroGrupoDeRecursosParaActividad_creador"];

 // Construyendo el objeto 
   $obj = new OtroGrupoDeRecursosParaActividad($plantilla,$prioridad,$necesarios,$imprescindibles,$creador,"",true, null, false);
?>