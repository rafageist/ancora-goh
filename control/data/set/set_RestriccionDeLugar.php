<?php 

/** 
  * @name: Receptor of RestriccionDeLugar data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.RestriccionDeLugarModel"); 
import("model.data.RestriccionDeLugar"); 
import("model.data.RestriccionDeLugarCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_RestriccionDeLugar_lugar.php")) include "../control/data/fdms/fdm_RestriccionDeLugar_lugar.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeLugar_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeLugar_periodo.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeLugar_dia.php")) include "../control/data/fdms/fdm_RestriccionDeLugar_dia.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeLugar_turno.php")) include "../control/data/fdms/fdm_RestriccionDeLugar_turno.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeLugar_valor.php")) include "../control/data/fdms/fdm_RestriccionDeLugar_valor.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeLugar_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeLugar_por_actividad.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeLugar_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeLugar_actividad.php"; 

// Getting data from POST 
   if (isset($_POST["field_RestriccionDeLugar_id"])) $id = $_POST["field_RestriccionDeLugar_id"]; else $id = null;
   if (isset($_POST["field_RestriccionDeLugar_lugar"])) $lugar = $_POST["field_RestriccionDeLugar_lugar"]; else $lugar = null;
   if (isset($_POST["field_RestriccionDeLugar_periodo"])) $periodo = $_POST["field_RestriccionDeLugar_periodo"]; else $periodo = null;
   if (isset($_POST["field_RestriccionDeLugar_dia"])) $dia = $_POST["field_RestriccionDeLugar_dia"]; else $dia = null;
   if (isset($_POST["field_RestriccionDeLugar_turno"])) $turno = $_POST["field_RestriccionDeLugar_turno"]; else $turno = null;
   if (isset($_POST["field_RestriccionDeLugar_valor"])) $valor = $_POST["field_RestriccionDeLugar_valor"]; else $valor = null;
   if (isset($_POST["field_RestriccionDeLugar_por_actividad"])) $por_actividad = $_POST["field_RestriccionDeLugar_por_actividad"]; else $por_actividad = null;
   if (isset($_POST["field_RestriccionDeLugar_actividad"])) $actividad = $_POST["field_RestriccionDeLugar_actividad"]; else $actividad = null;

 // Actualizando el objeto 
   $col = new RestriccionDeLugarCollection($dbconnection);
   $obj = $col->getItem($id);
if ($lugar !== null) $obj->setLugar($lugar);
if ($periodo !== null) $obj->setPeriodo($periodo);
if ($dia !== null) $obj->setDia($dia);
if ($turno !== null) $obj->setTurno($turno);
if ($valor !== null) $obj->setValor($valor);
if ($por_actividad !== null) $obj->setPorActividad($por_actividad);
if ($actividad !== null) $obj->setActividad($actividad);
?>