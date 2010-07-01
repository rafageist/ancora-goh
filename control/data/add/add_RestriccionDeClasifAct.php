<?php 

/** 
  * @name: Receptor of RestriccionDeClasifAct data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionDeClasifActModel"); 
import("model.data.RestriccionDeClasifAct"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_clasificacion.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_clasificacion.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_dia.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_turno.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_valor.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_valor.php"; 

// Getting data from POST 
   $clasificacion = $_POST["field_RestriccionDeClasifAct_clasificacion"];
   $periodo = $_POST["field_RestriccionDeClasifAct_periodo"];
   $dia = $_POST["field_RestriccionDeClasifAct_dia"];
   $turno = $_POST["field_RestriccionDeClasifAct_turno"];
   $valor = $_POST["field_RestriccionDeClasifAct_valor"];

 // Construyendo el objeto 
   $obj = new RestriccionDeClasifAct($clasificacion,$periodo,$dia,$turno,$valor,"",true, null, false);
?>