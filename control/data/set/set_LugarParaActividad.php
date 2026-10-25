<?php 

/** 
  * @name: Receptor of LugarParaActividad data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.LugarParaActividadModel"); 
import("model.data.LugarParaActividad"); 
import("model.data.LugarParaActividadCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_LugarParaActividad_plantilla.php")) include "../control/data/fdms/fdm_LugarParaActividad_plantilla.php"; 
if (file_exists("../control/data/fdms/fdm_LugarParaActividad_lugar.php")) include "../control/data/fdms/fdm_LugarParaActividad_lugar.php"; 
if (file_exists("../control/data/fdms/fdm_LugarParaActividad_prioridad.php")) include "../control/data/fdms/fdm_LugarParaActividad_prioridad.php"; 

// Getting data from POST 
   if (isset($_POST["field_LugarParaActividad_plantilla"])) $plantilla = $_POST["field_LugarParaActividad_plantilla"]; else $plantilla = null;
   if (isset($_POST["field_LugarParaActividad_lugar"])) $lugar = $_POST["field_LugarParaActividad_lugar"]; else $lugar = null;
   if (isset($_POST["field_LugarParaActividad_prioridad"])) $prioridad = $_POST["field_LugarParaActividad_prioridad"]; else $prioridad = null;

 // Actualizando el objeto 
   $col = new LugarParaActividadCollection($dbconnection);
   $obj = $col->getItem($plantilla,$lugar);
if ($plantilla !== null) $obj->setPlantilla($plantilla);
if ($lugar !== null) $obj->setLugar($lugar);
if ($prioridad !== null) $obj->setPrioridad($prioridad);
?>