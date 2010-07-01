<?php 

/** 
  * @name: Receptor of Especialidad data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.EspecialidadModel"); 
import("model.data.Especialidad"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Especialidad_descripcion.php")) include "../control/data/fdms/fdm_Especialidad_descripcion.php"; 
   if (file_exists("../control/data/fdms/fdm_Especialidad_creador.php")) include "../control/data/fdms/fdm_Especialidad_creador.php"; 

// Getting data from POST 
   $descripcion = $_POST["field_Especialidad_descripcion"];
   $creador = $_POST["field_Especialidad_creador"];

 // Construyendo el objeto 
   $obj = new Especialidad($descripcion,$creador,"",true, null, false);
?>