<?php 

/** 
  * @name: Receptor of Traza identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.TrazaModel"); 
import("model.data.Traza"); 
import("model.data.TrazaCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_Traza_fecha.php")) include "../control/data/fdms/fdm_Traza_fecha.php"; 
   if (file_exists("../control/data/fdms/fdm_Traza_usuario.php")) include "../control/data/fdms/fdm_Traza_usuario.php"; 
   if (file_exists("../control/data/fdms/fdm_Traza_accion.php")) include "../control/data/fdms/fdm_Traza_accion.php"; 
   if (file_exists("../control/data/fdms/fdm_Traza_ip.php")) include "../control/data/fdms/fdm_Traza_ip.php"; 
   if (isset($_POST["field_Traza_fecha"])) $fecha = $_POST["field_Traza_fecha"]; else $fecha = "null";
   if (isset($_POST["field_Traza_usuario"])) $usuario = $_POST["field_Traza_usuario"]; else $usuario = "null";
   if (isset($_POST["field_Traza_accion"])) $accion = $_POST["field_Traza_accion"]; else $accion = "null";
   if (isset($_POST["field_Traza_ip"])) $ip = $_POST["field_Traza_ip"]; else $ip = "null";

 // Eliminando el objeto 
   $col = new TrazaCollection($dbconnection);
   $col->removeItem();
?>