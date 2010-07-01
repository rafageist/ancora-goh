<?php 

/** 
  * @name: Receptor of RolDeUsuario identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.RolDeUsuarioModel"); 
import("model.data.RolDeUsuario"); 
import("model.data.RolDeUsuarioCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_RolDeUsuario_usuario.php")) include "../control/data/fdms/fdm_RolDeUsuario_usuario.php"; 
   if (file_exists("../control/data/fdms/fdm_RolDeUsuario_rol.php")) include "../control/data/fdms/fdm_RolDeUsuario_rol.php"; 
   if (isset($_POST["field_RolDeUsuario_usuario"])) $usuario = $_POST["field_RolDeUsuario_usuario"]; else $usuario = "null";
   if (isset($_POST["field_RolDeUsuario_rol"])) $rol = $_POST["field_RolDeUsuario_rol"]; else $rol = "null";

 // Eliminando el objeto 
   $col = new RolDeUsuarioCollection($dbconnection);
   $col->removeItem($usuario,$rol);
?>