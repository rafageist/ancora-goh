<?php 

/** 
  * @name: Receptor of CancelarGenerador data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.CancelarGeneradorModel"); 
import("model.data.CancelarGenerador"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_CancelarGenerador_especialidad.php")) include "../control/data/fdms/fdm_CancelarGenerador_especialidad.php"; 
   if (file_exists("../control/data/fdms/fdm_CancelarGenerador_nivel.php")) include "../control/data/fdms/fdm_CancelarGenerador_nivel.php"; 
   if (file_exists("../control/data/fdms/fdm_CancelarGenerador_periodo.php")) include "../control/data/fdms/fdm_CancelarGenerador_periodo.php"; 

// Getting data from POST 
   $especialidad = $_POST["field_CancelarGenerador_especialidad"];
   $nivel = $_POST["field_CancelarGenerador_nivel"];
   $periodo = $_POST["field_CancelarGenerador_periodo"];

 // Construyendo el objeto 
   $obj = new CancelarGenerador($especialidad,$nivel,$periodo,true, null, false);
?>