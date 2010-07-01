<?php 

/** 
  * @name: Receptor of LugarFijo identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.LugarFijoModel"); 
import("model.data.LugarFijo"); 
import("model.data.LugarFijoCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_LugarFijo_grupo.php")) include "../control/data/fdms/fdm_LugarFijo_grupo.php"; 
   if (file_exists("../control/data/fdms/fdm_LugarFijo_lugar.php")) include "../control/data/fdms/fdm_LugarFijo_lugar.php"; 
   if (isset($_POST["field_LugarFijo_grupo"])) $grupo = $_POST["field_LugarFijo_grupo"]; else $grupo = "null";
   if (isset($_POST["field_LugarFijo_lugar"])) $lugar = $_POST["field_LugarFijo_lugar"]; else $lugar = "null";

 // Eliminando el objeto 
   $col = new LugarFijoCollection($dbconnection);
   $col->removeItem($grupo,$lugar);
?>