<?php 

/** 
  * @name: Receptor of Traza data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 

import("model.data.TrazaModel"); 
import("model.data.Traza"); 
import("model.data.TrazaCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_Traza_fecha.php")) include "../control/data/fdms/fdm_Traza_fecha.php"; 
if (file_exists("../control/data/fdms/fdm_Traza_usuario.php")) include "../control/data/fdms/fdm_Traza_usuario.php"; 
if (file_exists("../control/data/fdms/fdm_Traza_accion.php")) include "../control/data/fdms/fdm_Traza_accion.php"; 
if (file_exists("../control/data/fdms/fdm_Traza_ip.php")) include "../control/data/fdms/fdm_Traza_ip.php"; 

// Getting data from POST 
   if (isset($_POST["field_Traza_fecha"])) $fecha = $_POST["field_Traza_fecha"]; else $fecha = "null";
   if (isset($_POST["field_Traza_usuario"])) $usuario = $_POST["field_Traza_usuario"]; else $usuario = "null";
   if (isset($_POST["field_Traza_accion"])) $accion = $_POST["field_Traza_accion"]; else $accion = "null";
   if (isset($_POST["field_Traza_ip"])) $ip = $_POST["field_Traza_ip"]; else $ip = "null";

 // Actualizando el objeto 
   $col = new TrazaCollection($dbconnection);
   $obj = $col->getItem();
$obj->setFecha($fecha);
$obj->setUsuario($usuario);
$obj->setAccion($accion);
$obj->setIp($ip);
?>