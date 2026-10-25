<?php 

/** 
  * @name: Receptor of Grupo data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 

import("model.data.GrupoModel"); 
import("model.data.Grupo"); 
import("model.data.GrupoCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_Grupo_clasificacion.php")) include "../control/data/fdms/fdm_Grupo_clasificacion.php"; 
if (file_exists("../control/data/fdms/fdm_Grupo_descripcion.php")) include "../control/data/fdms/fdm_Grupo_descripcion.php"; 
if (file_exists("../control/data/fdms/fdm_Grupo_especialidad.php")) include "../control/data/fdms/fdm_Grupo_especialidad.php"; 
if (file_exists("../control/data/fdms/fdm_Grupo_nivel.php")) include "../control/data/fdms/fdm_Grupo_nivel.php"; 
if (file_exists("../control/data/fdms/fdm_Grupo_planificacion.php")) include "../control/data/fdms/fdm_Grupo_planificacion.php"; 

// Getting data from POST 
   if (isset($_POST["field_Grupo_id"])) $id = $_POST["field_Grupo_id"]; else $id = null;
   if (isset($_POST["field_Grupo_clasificacion"])) $clasificacion = $_POST["field_Grupo_clasificacion"]; else $clasificacion = null;
   if (isset($_POST["field_Grupo_descripcion"])) $descripcion = $_POST["field_Grupo_descripcion"]; else $descripcion = null;
   if (isset($_POST["field_Grupo_especialidad"])) $especialidad = $_POST["field_Grupo_especialidad"]; else $especialidad = null;
   if (isset($_POST["field_Grupo_nivel"])) $nivel = $_POST["field_Grupo_nivel"]; else $nivel = null;
   if (isset($_POST["field_Grupo_planificacion"])) $planificacion = $_POST["field_Grupo_planificacion"]; else $planificacion = null;

 // Actualizando el objeto 
   $col = new GrupoCollection($dbconnection);
   $obj = $col->getItem($id);
if ($clasificacion !== null) $obj->setClasificacion($clasificacion);
if ($descripcion !== null) $obj->setDescripcion($descripcion);
if ($especialidad !== null) $obj->setEspecialidad($especialidad);
if ($nivel !== null) $obj->setNivel($nivel);
if ($planificacion !== null) $obj->setPlanificacion($planificacion);
?>