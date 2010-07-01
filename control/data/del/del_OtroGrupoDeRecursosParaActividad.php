<?php 

/** 
  * @name: Receptor of OtroGrupoDeRecursosParaActividad identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.OtroGrupoDeRecursosParaActividadModel"); 
import("model.data.OtroGrupoDeRecursosParaActividad"); 
import("model.data.OtroGrupoDeRecursosParaActividadCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_plantilla.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_plantilla.php"; 
   if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_prioridad.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_prioridad.php"; 
   if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_necesarios.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_necesarios.php"; 
   if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_imprescindibles.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_imprescindibles.php"; 
   if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_creador.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_creador.php"; 
   if (isset($_POST["field_OtroGrupoDeRecursosParaActividad_id"])) $id = $_POST["field_OtroGrupoDeRecursosParaActividad_id"]; else $id = "null";
   if (isset($_POST["field_OtroGrupoDeRecursosParaActividad_plantilla"])) $plantilla = $_POST["field_OtroGrupoDeRecursosParaActividad_plantilla"]; else $plantilla = "null";
   if (isset($_POST["field_OtroGrupoDeRecursosParaActividad_prioridad"])) $prioridad = $_POST["field_OtroGrupoDeRecursosParaActividad_prioridad"]; else $prioridad = "null";
   if (isset($_POST["field_OtroGrupoDeRecursosParaActividad_necesarios"])) $necesarios = $_POST["field_OtroGrupoDeRecursosParaActividad_necesarios"]; else $necesarios = "null";
   if (isset($_POST["field_OtroGrupoDeRecursosParaActividad_imprescindibles"])) $imprescindibles = $_POST["field_OtroGrupoDeRecursosParaActividad_imprescindibles"]; else $imprescindibles = "null";
   if (isset($_POST["field_OtroGrupoDeRecursosParaActividad_creador"])) $creador = $_POST["field_OtroGrupoDeRecursosParaActividad_creador"]; else $creador = "null";

 // Eliminando el objeto 
   $col = new OtroGrupoDeRecursosParaActividadCollection($dbconnection);
   $col->removeItem($id);
?>