<?php 

/** 
  * @name: Receptor of ProfesorParaActividad data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.ProfesorParaActividadModel"); 
import("model.data.ProfesorParaActividad"); 
import("model.data.ProfesorParaActividadCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_ProfesorParaActividad_plantilla.php")) include "../control/data/fdms/fdm_ProfesorParaActividad_plantilla.php"; 
if (file_exists("../control/data/fdms/fdm_ProfesorParaActividad_profesor.php")) include "../control/data/fdms/fdm_ProfesorParaActividad_profesor.php"; 
if (file_exists("../control/data/fdms/fdm_ProfesorParaActividad_prioridad.php")) include "../control/data/fdms/fdm_ProfesorParaActividad_prioridad.php"; 

// Getting data from POST 
   if (isset($_POST["field_ProfesorParaActividad_id"])) $id = $_POST["field_ProfesorParaActividad_id"]; else $id = null;
   if (isset($_POST["field_ProfesorParaActividad_plantilla"])) $plantilla = $_POST["field_ProfesorParaActividad_plantilla"]; else $plantilla = null;
   if (isset($_POST["field_ProfesorParaActividad_profesor"])) $profesor = $_POST["field_ProfesorParaActividad_profesor"]; else $profesor = null;
   if (isset($_POST["field_ProfesorParaActividad_prioridad"])) $prioridad = $_POST["field_ProfesorParaActividad_prioridad"]; else $prioridad = null;

 // Actualizando el objeto 
   $col = new ProfesorParaActividadCollection($dbconnection);
   $obj = $col->getItem($id);
if ($plantilla !== null) $obj->setPlantilla($plantilla);
if ($profesor !== null) $obj->setProfesor($profesor);
if ($prioridad !== null) $obj->setPrioridad($prioridad);
?>