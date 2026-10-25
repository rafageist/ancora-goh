<?php 

/** 
  * @name: Receptor of OtroRecursoParaActividad data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.OtroRecursoParaActividadModel"); 
import("model.data.OtroRecursoParaActividad"); 
import("model.data.OtroRecursoParaActividadCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_OtroRecursoParaActividad_grupo_de_recursos.php")) include "../control/data/fdms/fdm_OtroRecursoParaActividad_grupo_de_recursos.php"; 
if (file_exists("../control/data/fdms/fdm_OtroRecursoParaActividad_prioridad.php")) include "../control/data/fdms/fdm_OtroRecursoParaActividad_prioridad.php"; 
if (file_exists("../control/data/fdms/fdm_OtroRecursoParaActividad_otro_recurso.php")) include "../control/data/fdms/fdm_OtroRecursoParaActividad_otro_recurso.php"; 

// Getting data from POST 
   if (isset($_POST["field_OtroRecursoParaActividad_grupo_de_recursos"])) $grupo_de_recursos = $_POST["field_OtroRecursoParaActividad_grupo_de_recursos"]; else $grupo_de_recursos = null;
   if (isset($_POST["field_OtroRecursoParaActividad_prioridad"])) $prioridad = $_POST["field_OtroRecursoParaActividad_prioridad"]; else $prioridad = null;
   if (isset($_POST["field_OtroRecursoParaActividad_otro_recurso"])) $otro_recurso = $_POST["field_OtroRecursoParaActividad_otro_recurso"]; else $otro_recurso = null;

 // Actualizando el objeto 
   $col = new OtroRecursoParaActividadCollection($dbconnection);
   $obj = $col->getItem($grupo_de_recursos,$otro_recurso);
if ($grupo_de_recursos !== null) $obj->setGrupoDeRecursos($grupo_de_recursos);
if ($prioridad !== null) $obj->setPrioridad($prioridad);
if ($otro_recurso !== null) $obj->setOtroRecurso($otro_recurso);
?>