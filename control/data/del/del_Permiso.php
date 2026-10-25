<?php 

/** 
  * @name: Receptor of Permiso identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.PermisoModel"); 
import("model.data.Permiso"); 
import("model.data.PermisoCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_Permiso_permiso.php")) include "../control/data/fdms/fdm_Permiso_permiso.php"; 
   if (file_exists("../control/data/fdms/fdm_Permiso_rol.php")) include "../control/data/fdms/fdm_Permiso_rol.php"; 
   if (isset($_POST["field_Permiso_permiso"])) $permiso = $_POST["field_Permiso_permiso"]; else $permiso = null;
   if (isset($_POST["field_Permiso_rol"])) $rol = $_POST["field_Permiso_rol"]; else $rol = null;

 // Eliminando el objeto 
   $col = new PermisoCollection($dbconnection);
   $col->removeItem($permiso,$rol);
?>