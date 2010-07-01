<?php 

/** 
  * @name: Receptor of Periodicidad data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.PeriodicidadModel"); 
import("model.data.Periodicidad"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Periodicidad_periodo.php")) include "../control/data/fdms/fdm_Periodicidad_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_Periodicidad_dia.php")) include "../control/data/fdms/fdm_Periodicidad_dia.php"; 

// Getting data from POST 
   $periodo = $_POST["field_Periodicidad_periodo"];
   $dia = $_POST["field_Periodicidad_dia"];

 // Construyendo el objeto 
   $obj = new Periodicidad($periodo,$dia,"",true, null, false);
?>