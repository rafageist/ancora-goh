<?php 

/** 
  * @name: Receptor of Nivel data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.NivelModel"); 
import("model.data.Nivel"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Nivel_descripcion.php")) include "../control/data/fdms/fdm_Nivel_descripcion.php"; 
   if (file_exists("../control/data/fdms/fdm_Nivel_creador.php")) include "../control/data/fdms/fdm_Nivel_creador.php"; 

// Getting data from POST 
   $descripcion = $_POST["field_Nivel_descripcion"];
   $creador = $_POST["field_Nivel_creador"];

 // Construyendo el objeto 
   $obj = new Nivel($descripcion,$creador,"",true, null, false);
?>