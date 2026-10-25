<?php 

/** 
  * @name: Receptor of OtroRecurso data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.OtroRecursoModel"); 
import("model.data.OtroRecurso"); 
import("model.data.OtroRecursoCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_OtroRecurso_nombre.php")) include "../control/data/fdms/fdm_OtroRecurso_nombre.php"; 
if (file_exists("../control/data/fdms/fdm_OtroRecurso_creador.php")) include "../control/data/fdms/fdm_OtroRecurso_creador.php"; 

// Getting data from POST 
   if (isset($_POST["field_OtroRecurso_id"])) $id = $_POST["field_OtroRecurso_id"]; else $id = null;
   if (isset($_POST["field_OtroRecurso_nombre"])) $nombre = $_POST["field_OtroRecurso_nombre"]; else $nombre = null;
   if (isset($_POST["field_OtroRecurso_creador"])) $creador = $_POST["field_OtroRecurso_creador"]; else $creador = null;

 // Actualizando el objeto 
   $col = new OtroRecursoCollection($dbconnection);
   $obj = $col->getItem($id);
if ($nombre !== null) $obj->setNombre($nombre);
if ($creador !== null) $obj->setCreador($creador);
?>