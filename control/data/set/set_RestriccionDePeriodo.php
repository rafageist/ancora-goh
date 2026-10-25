<?php 

/** 
  * @name: Receptor of RestriccionDePeriodo data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.RestriccionDePeriodoModel"); 
import("model.data.RestriccionDePeriodo"); 
import("model.data.RestriccionDePeriodoCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_RestriccionDePeriodo_periodo.php")) include "../control/data/fdms/fdm_RestriccionDePeriodo_periodo.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDePeriodo_dia.php")) include "../control/data/fdms/fdm_RestriccionDePeriodo_dia.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDePeriodo_turno.php")) include "../control/data/fdms/fdm_RestriccionDePeriodo_turno.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDePeriodo_valor.php")) include "../control/data/fdms/fdm_RestriccionDePeriodo_valor.php"; 

// Getting data from POST 
   if (isset($_POST["field_RestriccionDePeriodo_id"])) $id = $_POST["field_RestriccionDePeriodo_id"]; else $id = null;
   if (isset($_POST["field_RestriccionDePeriodo_periodo"])) $periodo = $_POST["field_RestriccionDePeriodo_periodo"]; else $periodo = null;
   if (isset($_POST["field_RestriccionDePeriodo_dia"])) $dia = $_POST["field_RestriccionDePeriodo_dia"]; else $dia = null;
   if (isset($_POST["field_RestriccionDePeriodo_turno"])) $turno = $_POST["field_RestriccionDePeriodo_turno"]; else $turno = null;
   if (isset($_POST["field_RestriccionDePeriodo_valor"])) $valor = $_POST["field_RestriccionDePeriodo_valor"]; else $valor = null;

 // Actualizando el objeto 
   $col = new RestriccionDePeriodoCollection($dbconnection);
   $obj = $col->getItem($id);
if ($periodo !== null) $obj->setPeriodo($periodo);
if ($dia !== null) $obj->setDia($dia);
if ($turno !== null) $obj->setTurno($turno);
if ($valor !== null) $obj->setValor($valor);
?>