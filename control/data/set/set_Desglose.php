<?php 

/** 
  * @name: Receptor of Desglose data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 

import("model.data.DesgloseModel"); 
import("model.data.Desglose"); 
import("model.data.DesgloseCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_Desglose_asignatura.php")) include "../control/data/fdms/fdm_Desglose_asignatura.php"; 
if (file_exists("../control/data/fdms/fdm_Desglose_periodo.php")) include "../control/data/fdms/fdm_Desglose_periodo.php"; 
if (file_exists("../control/data/fdms/fdm_Desglose_min_dias.php")) include "../control/data/fdms/fdm_Desglose_min_dias.php"; 
if (file_exists("../control/data/fdms/fdm_Desglose_max_dias.php")) include "../control/data/fdms/fdm_Desglose_max_dias.php"; 
if (file_exists("../control/data/fdms/fdm_Desglose_respetar_orden.php")) include "../control/data/fdms/fdm_Desglose_respetar_orden.php"; 
if (file_exists("../control/data/fdms/fdm_Desglose_mismo_dia.php")) include "../control/data/fdms/fdm_Desglose_mismo_dia.php"; 

// Getting data from POST 
   if (isset($_POST["field_Desglose_id"])) $id = $_POST["field_Desglose_id"]; else $id = null;
   if (isset($_POST["field_Desglose_asignatura"])) $asignatura = $_POST["field_Desglose_asignatura"]; else $asignatura = null;
   if (isset($_POST["field_Desglose_periodo"])) $periodo = $_POST["field_Desglose_periodo"]; else $periodo = null;
   if (isset($_POST["field_Desglose_min_dias"])) $min_dias = $_POST["field_Desglose_min_dias"]; else $min_dias = null;
   if (isset($_POST["field_Desglose_max_dias"])) $max_dias = $_POST["field_Desglose_max_dias"]; else $max_dias = null;
   if (isset($_POST["field_Desglose_respetar_orden"])) $respetar_orden = $_POST["field_Desglose_respetar_orden"]; else $respetar_orden = null;
   if (isset($_POST["field_Desglose_mismo_dia"])) $mismo_dia = $_POST["field_Desglose_mismo_dia"]; else $mismo_dia = null;

 // Actualizando el objeto 
   $col = new DesgloseCollection($dbconnection);
   $obj = $col->getItem($id);
if ($asignatura !== null) $obj->setAsignatura($asignatura);
if ($periodo !== null) $obj->setPeriodo($periodo);
if ($min_dias !== null) $obj->setMinDias($min_dias);
if ($max_dias !== null) $obj->setMaxDias($max_dias);
if ($respetar_orden !== null) $obj->setRespetarOrden($respetar_orden);
if ($mismo_dia !== null) $obj->setMismoDia($mismo_dia);
?>