<?php 

/** 
  * @name: Receptor of Zpriori data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.ZprioriModel"); 
import("model.data.Zpriori"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Zpriori_clasificacion.php")) include "../control/data/fdms/fdm_Zpriori_clasificacion.php"; 
   if (file_exists("../control/data/fdms/fdm_Zpriori_periodo.php")) include "../control/data/fdms/fdm_Zpriori_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_Zpriori_dia.php")) include "../control/data/fdms/fdm_Zpriori_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_Zpriori_turno.php")) include "../control/data/fdms/fdm_Zpriori_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_Zpriori_valor.php")) include "../control/data/fdms/fdm_Zpriori_valor.php"; 

// Getting data from POST 
   $clasificacion = $_POST["field_Zpriori_clasificacion"];
   $periodo = $_POST["field_Zpriori_periodo"];
   $dia = $_POST["field_Zpriori_dia"];
   $turno = $_POST["field_Zpriori_turno"];
   $valor = $_POST["field_Zpriori_valor"];

 // Construyendo el objeto 
   $obj = new Zpriori($clasificacion,$periodo,$dia,$turno,$valor,true, null, false);
?>