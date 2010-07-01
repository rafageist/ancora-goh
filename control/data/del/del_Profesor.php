<?php 

/** 
  * @name: Receptor of Profesor identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.ProfesorModel"); 
import("model.data.Profesor"); 
import("model.data.ProfesorCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_Profesor_nombre.php")) include "../control/data/fdms/fdm_Profesor_nombre.php"; 
   if (file_exists("../control/data/fdms/fdm_Profesor_creador.php")) include "../control/data/fdms/fdm_Profesor_creador.php"; 
   if (isset($_POST["field_Profesor_id"])) $id = $_POST["field_Profesor_id"]; else $id = "null";
   if (isset($_POST["field_Profesor_nombre"])) $nombre = $_POST["field_Profesor_nombre"]; else $nombre = "null";
   if (isset($_POST["field_Profesor_creador"])) $creador = $_POST["field_Profesor_creador"]; else $creador = "null";

 // Eliminando el objeto 
   $col = new ProfesorCollection($dbconnection);
   $col->removeItem($id);
?>