<?php 

/** 
  * @name: Receptor of LugarFijo data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.LugarFijoModel"); 
import("model.data.LugarFijo"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_LugarFijo_grupo.php")) include "../control/data/fdms/fdm_LugarFijo_grupo.php"; 
   if (file_exists("../control/data/fdms/fdm_LugarFijo_lugar.php")) include "../control/data/fdms/fdm_LugarFijo_lugar.php"; 

// Getting data from POST 
   $grupo = $_POST["field_LugarFijo_grupo"];
   $lugar = $_POST["field_LugarFijo_lugar"];

 // Construyendo el objeto 
   $obj = new LugarFijo($grupo,$lugar,true, null, false);
?>