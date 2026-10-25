<?php 

/** 
  * @name: Receptor of Rol identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:16 
  * @version: 1.0 
  */ 
import("model.data.RolModel"); 
import("model.data.Rol"); 
import("model.data.RolCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_Rol_nombre.php")) include "../control/data/fdms/fdm_Rol_nombre.php"; 
   if (file_exists("../control/data/fdms/fdm_Rol_descripcion.php")) include "../control/data/fdms/fdm_Rol_descripcion.php"; 
   if (isset($_POST["field_Rol_nombre"])) $nombre = $_POST["field_Rol_nombre"]; else $nombre = null;
   if (isset($_POST["field_Rol_descripcion"])) $descripcion = $_POST["field_Rol_descripcion"]; else $descripcion = null;

 // Eliminando el objeto 
   $col = new RolCollection($dbconnection);
   $col->removeItem($nombre);
?>