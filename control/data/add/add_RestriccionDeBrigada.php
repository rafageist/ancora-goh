<?php 

/** 
  * @name: Receptor of RestriccionDeBrigada data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionDeBrigadaModel"); 
import("model.data.RestriccionDeBrigada"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeBrigada_brigada.php")) include "../control/data/fdms/fdm_RestriccionDeBrigada_brigada.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeBrigada_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeBrigada_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeBrigada_dia.php")) include "../control/data/fdms/fdm_RestriccionDeBrigada_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeBrigada_turno.php")) include "../control/data/fdms/fdm_RestriccionDeBrigada_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeBrigada_valor.php")) include "../control/data/fdms/fdm_RestriccionDeBrigada_valor.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeBrigada_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeBrigada_por_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeBrigada_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeBrigada_actividad.php"; 

// Getting data from POST 
   $brigada = $_POST["field_RestriccionDeBrigada_brigada"];
   $periodo = $_POST["field_RestriccionDeBrigada_periodo"];
   $dia = $_POST["field_RestriccionDeBrigada_dia"];
   $turno = $_POST["field_RestriccionDeBrigada_turno"];
   $valor = $_POST["field_RestriccionDeBrigada_valor"];
   $por_actividad = $_POST["field_RestriccionDeBrigada_por_actividad"];
   $actividad = $_POST["field_RestriccionDeBrigada_actividad"];

 // Construyendo el objeto 
   $obj = new RestriccionDeBrigada($brigada,$periodo,$dia,$turno,$valor,$por_actividad,$actividad,"",true, null, false);
?>