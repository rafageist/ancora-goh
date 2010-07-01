<?php 

/** 
  * @name: Receptor of Desglose data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.DesgloseModel"); 
import("model.data.Desglose"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Desglose_asignatura.php")) include "../control/data/fdms/fdm_Desglose_asignatura.php"; 
   if (file_exists("../control/data/fdms/fdm_Desglose_periodo.php")) include "../control/data/fdms/fdm_Desglose_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_Desglose_min_dias.php")) include "../control/data/fdms/fdm_Desglose_min_dias.php"; 
   if (file_exists("../control/data/fdms/fdm_Desglose_max_dias.php")) include "../control/data/fdms/fdm_Desglose_max_dias.php"; 
   if (file_exists("../control/data/fdms/fdm_Desglose_respetar_orden.php")) include "../control/data/fdms/fdm_Desglose_respetar_orden.php"; 
   if (file_exists("../control/data/fdms/fdm_Desglose_mismo_dia.php")) include "../control/data/fdms/fdm_Desglose_mismo_dia.php"; 

// Getting data from POST 
   $asignatura = $_POST["field_Desglose_asignatura"];
   $periodo = $_POST["field_Desglose_periodo"];
   $min_dias = $_POST["field_Desglose_min_dias"];
   $max_dias = $_POST["field_Desglose_max_dias"];
   $respetar_orden = $_POST["field_Desglose_respetar_orden"];
   $mismo_dia = $_POST["field_Desglose_mismo_dia"];

 // Construyendo el objeto 
   $obj = new Desglose($asignatura,$periodo,$min_dias,$max_dias,$respetar_orden,$mismo_dia,"",true, null, false);
?>