<?php 

/** 
  * @name: Receptor of Receso data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RecesoModel"); 
import("model.data.Receso"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Receso_periodo.php")) include "../control/data/fdms/fdm_Receso_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_Receso_despues_del_turno.php")) include "../control/data/fdms/fdm_Receso_despues_del_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_Receso_duracion.php")) include "../control/data/fdms/fdm_Receso_duracion.php"; 

// Getting data from POST 
   $periodo = $_POST["field_Receso_periodo"];
   $despues_del_turno = $_POST["field_Receso_despues_del_turno"];
   $duracion = $_POST["field_Receso_duracion"];

 // Construyendo el objeto 
   $obj = new Receso($periodo,$despues_del_turno,$duracion,true, null, false);
?>