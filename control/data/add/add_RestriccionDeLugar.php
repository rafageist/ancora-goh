<?php 

/** 
  * @name: Receptor of RestriccionDeLugar data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionDeLugarModel"); 
import("model.data.RestriccionDeLugar"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeLugar_lugar.php")) include "../control/data/fdms/fdm_RestriccionDeLugar_lugar.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeLugar_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeLugar_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeLugar_dia.php")) include "../control/data/fdms/fdm_RestriccionDeLugar_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeLugar_turno.php")) include "../control/data/fdms/fdm_RestriccionDeLugar_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeLugar_valor.php")) include "../control/data/fdms/fdm_RestriccionDeLugar_valor.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeLugar_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeLugar_por_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeLugar_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeLugar_actividad.php"; 

// Getting data from POST 
   $lugar = $_POST["field_RestriccionDeLugar_lugar"];
   $periodo = $_POST["field_RestriccionDeLugar_periodo"];
   $dia = $_POST["field_RestriccionDeLugar_dia"];
   $turno = $_POST["field_RestriccionDeLugar_turno"];
   $valor = $_POST["field_RestriccionDeLugar_valor"];
   $por_actividad = $_POST["field_RestriccionDeLugar_por_actividad"];
   $actividad = $_POST["field_RestriccionDeLugar_actividad"];

 // Construyendo el objeto 
   $obj = new RestriccionDeLugar($lugar,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true, null, false);
?>