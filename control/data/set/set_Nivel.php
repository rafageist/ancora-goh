<?php 

/** 
  * @name: Receptor of Nivel data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.NivelModel"); 
import("model.data.Nivel"); 
import("model.data.NivelCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_Nivel_descripcion.php")) include "../control/data/fdms/fdm_Nivel_descripcion.php"; 
if (file_exists("../control/data/fdms/fdm_Nivel_creador.php")) include "../control/data/fdms/fdm_Nivel_creador.php"; 

// Getting data from POST 
   if (isset($_POST["field_Nivel_id"])) $id = $_POST["field_Nivel_id"]; else $id = null;
   if (isset($_POST["field_Nivel_descripcion"])) $descripcion = $_POST["field_Nivel_descripcion"]; else $descripcion = null;
   if (isset($_POST["field_Nivel_creador"])) $creador = $_POST["field_Nivel_creador"]; else $creador = null;

 // Actualizando el objeto 
   $col = new NivelCollection($dbconnection);
   $obj = $col->getItem($id);
if ($descripcion !== null) $obj->setDescripcion($descripcion);
if ($creador !== null) $obj->setCreador($creador);
?>