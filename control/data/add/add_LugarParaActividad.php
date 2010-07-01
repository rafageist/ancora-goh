<?php 

/** 
  * @name: Receptor of LugarParaActividad data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.LugarParaActividadModel"); 
import("model.data.LugarParaActividad"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_LugarParaActividad_plantilla.php")) include "../control/data/fdms/fdm_LugarParaActividad_plantilla.php"; 
   if (file_exists("../control/data/fdms/fdm_LugarParaActividad_lugar.php")) include "../control/data/fdms/fdm_LugarParaActividad_lugar.php"; 
   if (file_exists("../control/data/fdms/fdm_LugarParaActividad_prioridad.php")) include "../control/data/fdms/fdm_LugarParaActividad_prioridad.php"; 

// Getting data from POST 
   $plantilla = $_POST["field_LugarParaActividad_plantilla"];
   $lugar = $_POST["field_LugarParaActividad_lugar"];
   $prioridad = $_POST["field_LugarParaActividad_prioridad"];

 // Construyendo el objeto 
   $obj = new LugarParaActividad($plantilla,$lugar,$prioridad,true, null, false);
?>