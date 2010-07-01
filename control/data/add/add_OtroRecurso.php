<?php 

/** 
  * @name: Receptor of OtroRecurso data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.OtroRecursoModel"); 
import("model.data.OtroRecurso"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_OtroRecurso_nombre.php")) include "../control/data/fdms/fdm_OtroRecurso_nombre.php"; 
   if (file_exists("../control/data/fdms/fdm_OtroRecurso_creador.php")) include "../control/data/fdms/fdm_OtroRecurso_creador.php"; 

// Getting data from POST 
   $nombre = $_POST["field_OtroRecurso_nombre"];
   $creador = $_POST["field_OtroRecurso_creador"];

 // Construyendo el objeto 
   $obj = new OtroRecurso($nombre,$creador,"",true, null, false);
?>