<?php 

/** 
  * @name: Receptor of AccesoProhibido data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.AccesoProhibidoModel"); 
import("model.data.AccesoProhibido"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_AccesoProhibido_grupo.php")) include "../control/data/fdms/fdm_AccesoProhibido_grupo.php"; 
   if (file_exists("../control/data/fdms/fdm_AccesoProhibido_lugar.php")) include "../control/data/fdms/fdm_AccesoProhibido_lugar.php"; 

// Getting data from POST 
   $grupo = $_POST["field_AccesoProhibido_grupo"];
   $lugar = $_POST["field_AccesoProhibido_lugar"];

 // Construyendo el objeto 
   $obj = new AccesoProhibido($grupo,$lugar,true, null, false);
?>