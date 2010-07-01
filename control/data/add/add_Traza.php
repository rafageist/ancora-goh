<?php 

/** 
  * @name: Receptor of Traza data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.TrazaModel"); 
import("model.data.Traza"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Traza_fecha.php")) include "../control/data/fdms/fdm_Traza_fecha.php"; 
   if (file_exists("../control/data/fdms/fdm_Traza_usuario.php")) include "../control/data/fdms/fdm_Traza_usuario.php"; 
   if (file_exists("../control/data/fdms/fdm_Traza_accion.php")) include "../control/data/fdms/fdm_Traza_accion.php"; 
   if (file_exists("../control/data/fdms/fdm_Traza_ip.php")) include "../control/data/fdms/fdm_Traza_ip.php"; 

// Getting data from POST 
   $fecha = $_POST["field_Traza_fecha"];
   $usuario = $_POST["field_Traza_usuario"];
   $accion = $_POST["field_Traza_accion"];
   $ip = $_POST["field_Traza_ip"];

 // Construyendo el objeto 
   $obj = new Traza($fecha,$usuario,$accion,$ip,true, null, false);
?>