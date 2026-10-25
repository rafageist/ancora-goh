<?php 

/** 
  * @name: Receptor of Periodicidad data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.PeriodicidadModel"); 
import("model.data.Periodicidad"); 
import("model.data.PeriodicidadCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_Periodicidad_periodo.php")) include "../control/data/fdms/fdm_Periodicidad_periodo.php"; 
if (file_exists("../control/data/fdms/fdm_Periodicidad_dia.php")) include "../control/data/fdms/fdm_Periodicidad_dia.php"; 

// Getting data from POST 
   if (isset($_POST["field_Periodicidad_id"])) $id = $_POST["field_Periodicidad_id"]; else $id = null;
   if (isset($_POST["field_Periodicidad_periodo"])) $periodo = $_POST["field_Periodicidad_periodo"]; else $periodo = null;
   if (isset($_POST["field_Periodicidad_dia"])) $dia = $_POST["field_Periodicidad_dia"]; else $dia = null;

 // Actualizando el objeto 
   $col = new PeriodicidadCollection($dbconnection);
   $obj = $col->getItem($id);
if ($periodo !== null) $obj->setPeriodo($periodo);
if ($dia !== null) $obj->setDia($dia);
?>