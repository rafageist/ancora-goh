<?php 

/** 
  * @name: Receptor of LugarFijo data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 

import("model.data.LugarFijoModel"); 
import("model.data.LugarFijo"); 
import("model.data.LugarFijoCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_LugarFijo_grupo.php")) include "../control/data/fdms/fdm_LugarFijo_grupo.php"; 
if (file_exists("../control/data/fdms/fdm_LugarFijo_lugar.php")) include "../control/data/fdms/fdm_LugarFijo_lugar.php"; 

// Getting data from POST 
   if (isset($_POST["field_LugarFijo_grupo"])) $grupo = $_POST["field_LugarFijo_grupo"]; else $grupo = "null";
   if (isset($_POST["field_LugarFijo_lugar"])) $lugar = $_POST["field_LugarFijo_lugar"]; else $lugar = "null";

 // Actualizando el objeto 
   $col = new LugarFijoCollection($dbconnection);
   $obj = $col->getItem($grupo,$lugar);
$obj->setGrupo($grupo);
$obj->setLugar($lugar);
?>