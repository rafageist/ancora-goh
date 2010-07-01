<?php 

/** 
  * @name: Receptor of Usuario data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.UsuarioModel"); 
import("model.data.Usuario"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Usuario_nombre.php")) include "../control/data/fdms/fdm_Usuario_nombre.php"; 
   if (file_exists("../control/data/fdms/fdm_Usuario_info.php")) include "../control/data/fdms/fdm_Usuario_info.php"; 
   if (file_exists("../control/data/fdms/fdm_Usuario_correo.php")) include "../control/data/fdms/fdm_Usuario_correo.php"; 
   if (file_exists("../control/data/fdms/fdm_Usuario_momento_ingreso.php")) include "../control/data/fdms/fdm_Usuario_momento_ingreso.php"; 
   if (file_exists("../control/data/fdms/fdm_Usuario_nick.php")) include "../control/data/fdms/fdm_Usuario_nick.php"; 
   if (file_exists("../control/data/fdms/fdm_Usuario_contrasena.php")) include "../control/data/fdms/fdm_Usuario_contrasena.php"; 

// Getting data from POST 
   $nombre = $_POST["field_Usuario_nombre"];
   $info = $_POST["field_Usuario_info"];
   $correo = $_POST["field_Usuario_correo"];
   $momento_ingreso = $_POST["field_Usuario_momento_ingreso"];
   $nick = $_POST["field_Usuario_nick"];
   $contrasena = $_POST["field_Usuario_contrasena"];

 // Construyendo el objeto 
   $obj = new Usuario($nombre,$info,$correo,$momento_ingreso,$nick,$contrasena,"",true, null, false);
?>