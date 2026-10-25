<?php 

/** 
  * @name: Receptor of Especialidad identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.EspecialidadModel"); 
import("model.data.Especialidad"); 
import("model.data.EspecialidadCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_Especialidad_descripcion.php")) include "../control/data/fdms/fdm_Especialidad_descripcion.php"; 
   if (file_exists("../control/data/fdms/fdm_Especialidad_creador.php")) include "../control/data/fdms/fdm_Especialidad_creador.php"; 
   if (isset($_POST["field_Especialidad_id"])) $id = $_POST["field_Especialidad_id"]; else $id = null;
   if (isset($_POST["field_Especialidad_descripcion"])) $descripcion = $_POST["field_Especialidad_descripcion"]; else $descripcion = null;
   if (isset($_POST["field_Especialidad_creador"])) $creador = $_POST["field_Especialidad_creador"]; else $creador = null;

 // Eliminando el objeto 
   $col = new EspecialidadCollection($dbconnection);
   $col->removeItem($id);
?>