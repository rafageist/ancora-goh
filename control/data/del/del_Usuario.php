<?php 

/** 
  * @name: Receptor of Usuario identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.UsuarioModel"); 
import("model.data.Usuario"); 
import("model.data.UsuarioCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_Usuario_nombre.php")) include "../control/data/fdms/fdm_Usuario_nombre.php"; 
   if (file_exists("../control/data/fdms/fdm_Usuario_info.php")) include "../control/data/fdms/fdm_Usuario_info.php"; 
   if (file_exists("../control/data/fdms/fdm_Usuario_correo.php")) include "../control/data/fdms/fdm_Usuario_correo.php"; 
   if (file_exists("../control/data/fdms/fdm_Usuario_momento_ingreso.php")) include "../control/data/fdms/fdm_Usuario_momento_ingreso.php"; 
   if (file_exists("../control/data/fdms/fdm_Usuario_nick.php")) include "../control/data/fdms/fdm_Usuario_nick.php"; 
   if (file_exists("../control/data/fdms/fdm_Usuario_contrasena.php")) include "../control/data/fdms/fdm_Usuario_contrasena.php"; 
   if (isset($_POST["field_Usuario_id"])) $id = $_POST["field_Usuario_id"]; else $id = "null";
   if (isset($_POST["field_Usuario_nombre"])) $nombre = $_POST["field_Usuario_nombre"]; else $nombre = "null";
   if (isset($_POST["field_Usuario_info"])) $info = $_POST["field_Usuario_info"]; else $info = "null";
   if (isset($_POST["field_Usuario_correo"])) $correo = $_POST["field_Usuario_correo"]; else $correo = "null";
   if (isset($_POST["field_Usuario_momento_ingreso"])) $momento_ingreso = $_POST["field_Usuario_momento_ingreso"]; else $momento_ingreso = "null";
   if (isset($_POST["field_Usuario_nick"])) $nick = $_POST["field_Usuario_nick"]; else $nick = "null";
   if (isset($_POST["field_Usuario_contrasena"])) $contrasena = $_POST["field_Usuario_contrasena"]; else $contrasena = "null";

 // Eliminando el objeto 
   $col = new UsuarioCollection($dbconnection);
   $col->removeItem($id);
?>