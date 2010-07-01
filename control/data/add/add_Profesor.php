<?php 

/** 
  * @name: Receptor of Profesor data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.ProfesorModel"); 
import("model.data.Profesor"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Profesor_nombre.php")) include "../control/data/fdms/fdm_Profesor_nombre.php"; 
   if (file_exists("../control/data/fdms/fdm_Profesor_creador.php")) include "../control/data/fdms/fdm_Profesor_creador.php"; 

// Getting data from POST 
   $nombre = $_POST["field_Profesor_nombre"];
   $creador = $_POST["field_Profesor_creador"];

 // Construyendo el objeto 
   $obj = new Profesor($nombre,$creador,"",true, null, false);
?>