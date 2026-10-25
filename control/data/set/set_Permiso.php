<?php 

/** 
  * @name: Receptor of Permiso data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 

import("model.data.PermisoModel"); 
import("model.data.Permiso"); 
import("model.data.PermisoCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_Permiso_permiso.php")) include "../control/data/fdms/fdm_Permiso_permiso.php"; 
if (file_exists("../control/data/fdms/fdm_Permiso_rol.php")) include "../control/data/fdms/fdm_Permiso_rol.php"; 

// Getting data from POST 
   if (isset($_POST["field_Permiso_permiso"])) $permiso = $_POST["field_Permiso_permiso"]; else $permiso = null;
   if (isset($_POST["field_Permiso_rol"])) $rol = $_POST["field_Permiso_rol"]; else $rol = null;

 // Actualizando el objeto 
   $col = new PermisoCollection($dbconnection);
   $obj = $col->getItem($permiso,$rol);
if ($permiso !== null) $obj->setPermiso($permiso);
if ($rol !== null) $obj->setRol($rol);
?>