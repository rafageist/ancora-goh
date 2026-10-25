<?php 

/** 
  * @name: Receptor of Asignatura data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.AsignaturaModel"); 
import("model.data.Asignatura"); 
import("model.data.AsignaturaCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_Asignatura_descripcion.php")) include "../control/data/fdms/fdm_Asignatura_descripcion.php"; 
if (file_exists("../control/data/fdms/fdm_Asignatura_especialidad.php")) include "../control/data/fdms/fdm_Asignatura_especialidad.php"; 
if (file_exists("../control/data/fdms/fdm_Asignatura_nivel.php")) include "../control/data/fdms/fdm_Asignatura_nivel.php"; 
if (file_exists("../control/data/fdms/fdm_Asignatura_creador.php")) include "../control/data/fdms/fdm_Asignatura_creador.php"; 

// Getting data from POST 
   if (isset($_POST["field_Asignatura_id"])) $id = $_POST["field_Asignatura_id"]; else $id = null;
   if (isset($_POST["field_Asignatura_descripcion"])) $descripcion = $_POST["field_Asignatura_descripcion"]; else $descripcion = null;
   if (isset($_POST["field_Asignatura_especialidad"])) $especialidad = $_POST["field_Asignatura_especialidad"]; else $especialidad = null;
   if (isset($_POST["field_Asignatura_nivel"])) $nivel = $_POST["field_Asignatura_nivel"]; else $nivel = null;
   if (isset($_POST["field_Asignatura_creador"])) $creador = $_POST["field_Asignatura_creador"]; else $creador = null;

 // Actualizando el objeto 
   $col = new AsignaturaCollection($dbconnection);
   $obj = $col->getItem($id);
if ($descripcion !== null) $obj->setDescripcion($descripcion);
if ($especialidad !== null) $obj->setEspecialidad($especialidad);
if ($nivel !== null) $obj->setNivel($nivel);
if ($creador !== null) $obj->setCreador($creador);
?>