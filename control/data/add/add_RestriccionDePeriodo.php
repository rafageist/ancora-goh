<?php 

/** 
  * @name: Receptor of RestriccionDePeriodo data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionDePeriodoModel"); 
import("model.data.RestriccionDePeriodo"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RestriccionDePeriodo_periodo.php")) include "../control/data/fdms/fdm_RestriccionDePeriodo_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDePeriodo_dia.php")) include "../control/data/fdms/fdm_RestriccionDePeriodo_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDePeriodo_turno.php")) include "../control/data/fdms/fdm_RestriccionDePeriodo_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDePeriodo_valor.php")) include "../control/data/fdms/fdm_RestriccionDePeriodo_valor.php"; 

// Getting data from POST 
   $periodo = $_POST["field_RestriccionDePeriodo_periodo"];
   $dia = $_POST["field_RestriccionDePeriodo_dia"];
   $turno = $_POST["field_RestriccionDePeriodo_turno"];
   $valor = $_POST["field_RestriccionDePeriodo_valor"];

 // Construyendo el objeto 
   $obj = new RestriccionDePeriodo($periodo,$dia,$turno,$valor,"",true, null, false);
?>