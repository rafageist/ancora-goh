<?php 

/** 
  * @name: Receptor of AccesoProhibido data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 

import("model.data.AccesoProhibidoModel"); 
import("model.data.AccesoProhibido"); 
import("model.data.AccesoProhibidoCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_AccesoProhibido_grupo.php")) include "../control/data/fdms/fdm_AccesoProhibido_grupo.php"; 
if (file_exists("../control/data/fdms/fdm_AccesoProhibido_lugar.php")) include "../control/data/fdms/fdm_AccesoProhibido_lugar.php"; 

// Getting data from POST 
   if (isset($_POST["field_AccesoProhibido_grupo"])) $grupo = $_POST["field_AccesoProhibido_grupo"]; else $grupo = "null";
   if (isset($_POST["field_AccesoProhibido_lugar"])) $lugar = $_POST["field_AccesoProhibido_lugar"]; else $lugar = "null";

 // Actualizando el objeto 
   $col = new AccesoProhibidoCollection($dbconnection);
   $obj = $col->getItem($grupo,$lugar);
$obj->setGrupo($grupo);
$obj->setLugar($lugar);
?>