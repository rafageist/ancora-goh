<?php 

/** 
  * @name: Receptor of Host data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 

import("model.data.HostModel"); 
import("model.data.Host"); 
import("model.data.HostCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_Host_ip.php")) include "../control/data/fdms/fdm_Host_ip.php"; 
if (file_exists("../control/data/fdms/fdm_Host_usuario.php")) include "../control/data/fdms/fdm_Host_usuario.php"; 

// Getting data from POST 
   if (isset($_POST["field_Host_ip"])) $ip = $_POST["field_Host_ip"]; else $ip = null;
   if (isset($_POST["field_Host_usuario"])) $usuario = $_POST["field_Host_usuario"]; else $usuario = null;

 // Actualizando el objeto 
   $col = new HostCollection($dbconnection);
   $obj = $col->getItem($usuario,$ip);
if ($ip !== null) $obj->setIp($ip);
if ($usuario !== null) $obj->setUsuario($usuario);
?>