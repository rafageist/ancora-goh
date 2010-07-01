<?php 

/** 
  * @name: Receptor of Permiso data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.PermisoModel"); 
import("model.data.Permiso"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Permiso_permiso.php")) include "../control/data/fdms/fdm_Permiso_permiso.php"; 
   if (file_exists("../control/data/fdms/fdm_Permiso_rol.php")) include "../control/data/fdms/fdm_Permiso_rol.php"; 

// Getting data from POST 
   $permiso = $_POST["field_Permiso_permiso"];
   $rol = $_POST["field_Permiso_rol"];

 // Construyendo el objeto 
   $obj = new Permiso($permiso,$rol,true, null, false);
?>