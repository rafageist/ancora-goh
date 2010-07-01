<?php 

/** 
  * @name: Receptor of ProfesorParaActividad identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.ProfesorParaActividadModel"); 
import("model.data.ProfesorParaActividad"); 
import("model.data.ProfesorParaActividadCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_ProfesorParaActividad_plantilla.php")) include "../control/data/fdms/fdm_ProfesorParaActividad_plantilla.php"; 
   if (file_exists("../control/data/fdms/fdm_ProfesorParaActividad_profesor.php")) include "../control/data/fdms/fdm_ProfesorParaActividad_profesor.php"; 
   if (file_exists("../control/data/fdms/fdm_ProfesorParaActividad_prioridad.php")) include "../control/data/fdms/fdm_ProfesorParaActividad_prioridad.php"; 
   if (isset($_POST["field_ProfesorParaActividad_id"])) $id = $_POST["field_ProfesorParaActividad_id"]; else $id = "null";
   if (isset($_POST["field_ProfesorParaActividad_plantilla"])) $plantilla = $_POST["field_ProfesorParaActividad_plantilla"]; else $plantilla = "null";
   if (isset($_POST["field_ProfesorParaActividad_profesor"])) $profesor = $_POST["field_ProfesorParaActividad_profesor"]; else $profesor = "null";
   if (isset($_POST["field_ProfesorParaActividad_prioridad"])) $prioridad = $_POST["field_ProfesorParaActividad_prioridad"]; else $prioridad = "null";

 // Eliminando el objeto 
   $col = new ProfesorParaActividadCollection($dbconnection);
   $col->removeItem($id);
?>