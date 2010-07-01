<?php 

/** 
  * @name: Receptor of Zpriori data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 

import("model.data.ZprioriModel"); 
import("model.data.Zpriori"); 
import("model.data.ZprioriCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_Zpriori_clasificacion.php")) include "../control/data/fdms/fdm_Zpriori_clasificacion.php"; 
if (file_exists("../control/data/fdms/fdm_Zpriori_periodo.php")) include "../control/data/fdms/fdm_Zpriori_periodo.php"; 
if (file_exists("../control/data/fdms/fdm_Zpriori_dia.php")) include "../control/data/fdms/fdm_Zpriori_dia.php"; 
if (file_exists("../control/data/fdms/fdm_Zpriori_turno.php")) include "../control/data/fdms/fdm_Zpriori_turno.php"; 
if (file_exists("../control/data/fdms/fdm_Zpriori_valor.php")) include "../control/data/fdms/fdm_Zpriori_valor.php"; 

// Getting data from POST 
   if (isset($_POST["field_Zpriori_clasificacion"])) $clasificacion = $_POST["field_Zpriori_clasificacion"]; else $clasificacion = "null";
   if (isset($_POST["field_Zpriori_periodo"])) $periodo = $_POST["field_Zpriori_periodo"]; else $periodo = "null";
   if (isset($_POST["field_Zpriori_dia"])) $dia = $_POST["field_Zpriori_dia"]; else $dia = "null";
   if (isset($_POST["field_Zpriori_turno"])) $turno = $_POST["field_Zpriori_turno"]; else $turno = "null";
   if (isset($_POST["field_Zpriori_valor"])) $valor = $_POST["field_Zpriori_valor"]; else $valor = "null";

 // Actualizando el objeto 
   $col = new ZprioriCollection($dbconnection);
   $obj = $col->getItem($clasificacion,$periodo,$dia,$turno);
$obj->setClasificacion($clasificacion);
$obj->setPeriodo($periodo);
$obj->setDia($dia);
$obj->setTurno($turno);
$obj->setValor($valor);
?>