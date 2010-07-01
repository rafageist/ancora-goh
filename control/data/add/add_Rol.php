<?php 

/** 
  * @name: Receptor of Rol data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:16 
  * @version: 1.0 
  */ 
import("model.data.RolModel"); 
import("model.data.Rol"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Rol_nombre.php")) include "../control/data/fdms/fdm_Rol_nombre.php"; 
   if (file_exists("../control/data/fdms/fdm_Rol_descripcion.php")) include "../control/data/fdms/fdm_Rol_descripcion.php"; 

// Getting data from POST 
   $nombre = $_POST["field_Rol_nombre"];
   $descripcion = $_POST["field_Rol_descripcion"];

 // Construyendo el objeto 
   $obj = new Rol($nombre,$descripcion,true, null, false);
?>