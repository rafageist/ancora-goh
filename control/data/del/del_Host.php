<?php 

/** 
  * @name: Receptor of Host identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.HostModel"); 
import("model.data.Host"); 
import("model.data.HostCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_Host_ip.php")) include "../control/data/fdms/fdm_Host_ip.php"; 
   if (file_exists("../control/data/fdms/fdm_Host_usuario.php")) include "../control/data/fdms/fdm_Host_usuario.php"; 
   if (isset($_POST["field_Host_ip"])) $ip = $_POST["field_Host_ip"]; else $ip = null;
   if (isset($_POST["field_Host_usuario"])) $usuario = $_POST["field_Host_usuario"]; else $usuario = null;

 // Eliminando el objeto 
   $col = new HostCollection($dbconnection);
   $col->removeItem($usuario,$ip);
?>