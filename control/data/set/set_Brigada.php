<?php 

/** 
  * @name: Receptor of Brigada data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 

import("model.data.BrigadaModel"); 
import("model.data.Brigada"); 
import("model.data.BrigadaCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_Brigada_descripcion.php")) include "../control/data/fdms/fdm_Brigada_descripcion.php"; 
if (file_exists("../control/data/fdms/fdm_Brigada_especialidad.php")) include "../control/data/fdms/fdm_Brigada_especialidad.php"; 
if (file_exists("../control/data/fdms/fdm_Brigada_nivel.php")) include "../control/data/fdms/fdm_Brigada_nivel.php"; 
if (file_exists("../control/data/fdms/fdm_Brigada_matricula.php")) include "../control/data/fdms/fdm_Brigada_matricula.php"; 
if (file_exists("../control/data/fdms/fdm_Brigada_creador.php")) include "../control/data/fdms/fdm_Brigada_creador.php"; 

// Getting data from POST 
   if (isset($_POST["field_Brigada_id"])) $id = $_POST["field_Brigada_id"]; else $id = null;
   if (isset($_POST["field_Brigada_descripcion"])) $descripcion = $_POST["field_Brigada_descripcion"]; else $descripcion = null;
   if (isset($_POST["field_Brigada_especialidad"])) $especialidad = $_POST["field_Brigada_especialidad"]; else $especialidad = null;
   if (isset($_POST["field_Brigada_nivel"])) $nivel = $_POST["field_Brigada_nivel"]; else $nivel = null;
   if (isset($_POST["field_Brigada_matricula"])) $matricula = $_POST["field_Brigada_matricula"]; else $matricula = null;
   if (isset($_POST["field_Brigada_creador"])) $creador = $_POST["field_Brigada_creador"]; else $creador = null;

 // Actualizando el objeto 
   $col = new BrigadaCollection($dbconnection);
   $obj = $col->getItem($id);
if ($descripcion !== null) $obj->setDescripcion($descripcion);
if ($especialidad !== null) $obj->setEspecialidad($especialidad);
if ($nivel !== null) $obj->setNivel($nivel);
if ($matricula !== null) $obj->setMatricula($matricula);
if ($creador !== null) $obj->setCreador($creador);
?>