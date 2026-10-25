<?php 

/** 
  * @name: Receptor of OtroGrupoDeRecursosParaActividad data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.OtroGrupoDeRecursosParaActividadModel"); 
import("model.data.OtroGrupoDeRecursosParaActividad"); 
import("model.data.OtroGrupoDeRecursosParaActividadCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_plantilla.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_plantilla.php"; 
if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_prioridad.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_prioridad.php"; 
if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_necesarios.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_necesarios.php"; 
if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_imprescindibles.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_imprescindibles.php"; 
if (file_exists("../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_creador.php")) include "../control/data/fdms/fdm_OtroGrupoDeRecursosParaActividad_creador.php"; 

// Getting data from POST 
   if (isset($_POST["field_OtroGrupoDeRecursosParaActividad_id"])) $id = $_POST["field_OtroGrupoDeRecursosParaActividad_id"]; else $id = null;
   if (isset($_POST["field_OtroGrupoDeRecursosParaActividad_plantilla"])) $plantilla = $_POST["field_OtroGrupoDeRecursosParaActividad_plantilla"]; else $plantilla = null;
   if (isset($_POST["field_OtroGrupoDeRecursosParaActividad_prioridad"])) $prioridad = $_POST["field_OtroGrupoDeRecursosParaActividad_prioridad"]; else $prioridad = null;
   if (isset($_POST["field_OtroGrupoDeRecursosParaActividad_necesarios"])) $necesarios = $_POST["field_OtroGrupoDeRecursosParaActividad_necesarios"]; else $necesarios = null;
   if (isset($_POST["field_OtroGrupoDeRecursosParaActividad_imprescindibles"])) $imprescindibles = $_POST["field_OtroGrupoDeRecursosParaActividad_imprescindibles"]; else $imprescindibles = null;
   if (isset($_POST["field_OtroGrupoDeRecursosParaActividad_creador"])) $creador = $_POST["field_OtroGrupoDeRecursosParaActividad_creador"]; else $creador = null;

 // Actualizando el objeto 
   $col = new OtroGrupoDeRecursosParaActividadCollection($dbconnection);
   $obj = $col->getItem($id);
if ($plantilla !== null) $obj->setPlantilla($plantilla);
if ($prioridad !== null) $obj->setPrioridad($prioridad);
if ($necesarios !== null) $obj->setNecesarios($necesarios);
if ($imprescindibles !== null) $obj->setImprescindibles($imprescindibles);
if ($creador !== null) $obj->setCreador($creador);
?>