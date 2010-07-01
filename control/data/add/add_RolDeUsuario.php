<?php 

/** 
  * @name: Receptor of RolDeUsuario data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.RolDeUsuarioModel"); 
import("model.data.RolDeUsuario"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RolDeUsuario_usuario.php")) include "../control/data/fdms/fdm_RolDeUsuario_usuario.php"; 
   if (file_exists("../control/data/fdms/fdm_RolDeUsuario_rol.php")) include "../control/data/fdms/fdm_RolDeUsuario_rol.php"; 

// Getting data from POST 
   $usuario = $_POST["field_RolDeUsuario_usuario"];
   $rol = $_POST["field_RolDeUsuario_rol"];

 // Construyendo el objeto 
   $obj = new RolDeUsuario($usuario,$rol,true, null, false);
?>