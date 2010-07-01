<?php 

/** 
  * @name: Receptor of Host data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.HostModel"); 
import("model.data.Host"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Host_ip.php")) include "../control/data/fdms/fdm_Host_ip.php"; 
   if (file_exists("../control/data/fdms/fdm_Host_usuario.php")) include "../control/data/fdms/fdm_Host_usuario.php"; 

// Getting data from POST 
   $ip = $_POST["field_Host_ip"];
   $usuario = $_POST["field_Host_usuario"];

 // Construyendo el objeto 
   $obj = new Host($ip,$usuario,true, null, false);
?>