<?php 

/** 
  * @name: Receptor of Profesor data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.ProfesorModel"); 
import("model.data.Profesor"); 
import("model.data.ProfesorCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_Profesor_nombre.php")) include "../control/data/fdms/fdm_Profesor_nombre.php"; 
if (file_exists("../control/data/fdms/fdm_Profesor_creador.php")) include "../control/data/fdms/fdm_Profesor_creador.php"; 

// Getting data from POST 
   if (isset($_POST["field_Profesor_id"])) $id = $_POST["field_Profesor_id"]; else $id = null;
   if (isset($_POST["field_Profesor_nombre"])) $nombre = $_POST["field_Profesor_nombre"]; else $nombre = null;
   if (isset($_POST["field_Profesor_creador"])) $creador = $_POST["field_Profesor_creador"]; else $creador = null;

 // Actualizando el objeto 
   $col = new ProfesorCollection($dbconnection);
   $obj = $col->getItem($id);
if ($nombre !== null) $obj->setNombre($nombre);
if ($creador !== null) $obj->setCreador($creador);
?>