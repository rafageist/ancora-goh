<?php 

/** 
  * @name: Receptor of ProfesorParaActividad data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.ProfesorParaActividadModel"); 
import("model.data.ProfesorParaActividad"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_ProfesorParaActividad_plantilla.php")) include "../control/data/fdms/fdm_ProfesorParaActividad_plantilla.php"; 
   if (file_exists("../control/data/fdms/fdm_ProfesorParaActividad_profesor.php")) include "../control/data/fdms/fdm_ProfesorParaActividad_profesor.php"; 
   if (file_exists("../control/data/fdms/fdm_ProfesorParaActividad_prioridad.php")) include "../control/data/fdms/fdm_ProfesorParaActividad_prioridad.php"; 

// Getting data from POST 
   $plantilla = $_POST["field_ProfesorParaActividad_plantilla"];
   $profesor = $_POST["field_ProfesorParaActividad_profesor"];
   $prioridad = $_POST["field_ProfesorParaActividad_prioridad"];

 // Construyendo el objeto 
   $obj = new ProfesorParaActividad($plantilla,$profesor,$prioridad,"",true, null, false);
?>