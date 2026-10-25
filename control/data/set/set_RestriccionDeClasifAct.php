<?php 

/** 
  * @name: Receptor of RestriccionDeClasifAct data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.RestriccionDeClasifActModel"); 
import("model.data.RestriccionDeClasifAct"); 
import("model.data.RestriccionDeClasifActCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_clasificacion.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_clasificacion.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_periodo.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_dia.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_dia.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_turno.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_turno.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_valor.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_valor.php"; 

// Getting data from POST 
   if (isset($_POST["field_RestriccionDeClasifAct_id"])) $id = $_POST["field_RestriccionDeClasifAct_id"]; else $id = null;
   if (isset($_POST["field_RestriccionDeClasifAct_clasificacion"])) $clasificacion = $_POST["field_RestriccionDeClasifAct_clasificacion"]; else $clasificacion = null;
   if (isset($_POST["field_RestriccionDeClasifAct_periodo"])) $periodo = $_POST["field_RestriccionDeClasifAct_periodo"]; else $periodo = null;
   if (isset($_POST["field_RestriccionDeClasifAct_dia"])) $dia = $_POST["field_RestriccionDeClasifAct_dia"]; else $dia = null;
   if (isset($_POST["field_RestriccionDeClasifAct_turno"])) $turno = $_POST["field_RestriccionDeClasifAct_turno"]; else $turno = null;
   if (isset($_POST["field_RestriccionDeClasifAct_valor"])) $valor = $_POST["field_RestriccionDeClasifAct_valor"]; else $valor = null;

 // Actualizando el objeto 
   $col = new RestriccionDeClasifActCollection($dbconnection);
   $obj = $col->getItem($id);
if ($clasificacion !== null) $obj->setClasificacion($clasificacion);
if ($periodo !== null) $obj->setPeriodo($periodo);
if ($dia !== null) $obj->setDia($dia);
if ($turno !== null) $obj->setTurno($turno);
if ($valor !== null) $obj->setValor($valor);
?>